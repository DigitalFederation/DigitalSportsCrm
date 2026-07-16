<?php

namespace App\Livewire\EvtEvents;

use App\Enums\EvtEventEnrollmentRoleEnum;
use App\Enums\EvtEventFeeTypeEnum;
use App\Enums\EvtIndividualEnrollmentStatusEnum;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Actions\CreateEnrollmentAction;
use Domain\EvtEvents\Actions\CreateEnrollmentPaymentDocumentAction;
use Domain\EvtEvents\Actions\CreateIndividualEnrollmentAction;
use Domain\EvtEvents\Actions\GetAttributesAndRulesFromRolesAction;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\IndividualEnrollment;
use Domain\EvtEvents\Models\Pricing;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class IndividualEventRegistration extends Component implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    public string $activeTab = 'participants';
    public Event $event;
    public Model $model;
    public array $selectedIndividuals = [];
    public array $pricingOptions = [];
    public string $selectedPricing = '';
    public float $totalCost = 0;
    public int $selectedCount = 0;
    public float $perPersonPrice = 0;
    public array $selectedParticipants = [];
    public array $registrationStats = [];
    public array $roleAttributes = [];
    public array $roleAttributeValues = [];
    public array $globalAttributes = [];
    public array $globalAttributeValues = [];
    public array $selectedRecords = [];
    public bool $showSuccessMessage = false;
    public string $successMessage = '';
    public ?string $documentId = null;
    public int $participantCount = 0;

    public function mount(Event $event, Model $model): void
    {
        $this->event = $event;
        $this->model = $model;
        $this->selectedParticipants = [];
        $this->selectedCount = 0;
        $this->totalCost = 0;

        try {
            // Load pricing options
            $this->loadPricingOptions();

            // Set default pricing if available
            if (! empty($this->pricingOptions)) {
                $this->selectedPricing = array_key_first($this->pricingOptions);
            }

            // Calculate costs based on initial state
            $this->calculateTotalCost();
            $this->registrationStats = $this->getRegistrationStats();

            // Initialize attributes
            $this->initializeAttributes();
        } catch (ValidationException $e) {
            session()->flash('error', __('events.pricing_config_error'));
            $this->redirect(route($this->getRedirectRoute(), $event));
        }
    }

    protected function loadPricingOptions(): void
    {
        $this->pricingOptions = Pricing::query()
            ->where('event_id', $this->event->id)
            ->where('is_active', true)
            ->get()
            ->mapWithKeys(fn (Pricing $pricing) => [
                $pricing->id => $pricing->description . ' - ' . money($pricing->price),
            ])
            ->toArray();
    }

    public function table(Table $table): Table
    {
        $query = Individual::query()
            ->with('individualFederations', 'entities')
            ->whereDoesntHave('individualEnrollments', function ($query) {
                $query->where('event_id', $this->event->id);
            });

        // Filter individuals based on enrollable type
        if ($this->model instanceof Entity) {
            $query->whereHas('entities', function ($q) {
                $q->where('entity.id', $this->model->id);
            });
        } elseif ($this->model instanceof Federation) {
            $query->whereHas('individualFederations', function ($q) {
                $q->where('federation_id', $this->model->id)
                    ->where('status_class', ActiveIndividualFederationState::class);
            });
        }

        return $table
            ->query($query)
            ->defaultPaginationPageOption(100)
            ->searchDebounce(500)
            ->searchPlaceholder(__('events.search_by_name_surname'))
            ->selectable()
            ->deferLoading(false)
            ->columns([
                TextColumn::make('full_name')
                    ->label(__('events.name'))
                    ->searchable(['name', 'surname'])
                    ->sortable(),
                TextColumn::make('birthdate')
                    ->label(__('events.birth_date'))
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('member_number')
                    ->label(__('events.member_number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('events.email'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('events.phone')),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkAction::make('selectForRegistration')
                    ->label(__('events.select_for_registration'))
                    ->action(function (Collection $records) {
                        // Get existing selections if any
                        $existingSelections = collect($this->selectedParticipants)->keyBy('id');

                        // Merge new selections with existing ones
                        $newSelections = $records->map(fn ($record) => [
                            'id' => $record->id,
                            'name' => $record->full_name,
                            'price' => $this->getPerPersonPrice(),
                        ])->keyBy('id');

                        // Combine existing and new selections
                        $this->selectedParticipants = $existingSelections->merge($newSelections)->values()->toArray();

                        $this->selectedCount = count($this->selectedParticipants);
                        $this->calculateTotalCost();

                        // Initialize attribute values for the newly selected participants
                        $this->initializeAttributeValues();

                        // Automatically switch to attributes tab after selection
                        if (! empty($this->selectedParticipants) && ! empty($this->roleAttributes)) {
                            $this->activeTab = 'attributes';
                        }
                    })
                    ->deselectRecordsAfterCompletion()
                    ->after(fn () => $this->dispatch('selected-individuals-updated')),
            ]);
    }

    protected function getEnrollable(): Model
    {
        // Return the model (either Federation or Entity) that is doing the enrollment
        return $this->model;
    }

    public function getEnrollableType(): string
    {
        // Return the class name for the enrollable type
        return get_class($this->model);
    }

    protected function getEnrollableForeignKey(): string
    {
        // Create a new instance of the enrollable type to get its foreign key
        return (new ($this->getEnrollableType()))->getForeignKey();
    }

    protected function getPerPersonPrice(): float
    {
        return Pricing::query()
            ->where('event_id', $this->event->id)
            ->where('price_type', EvtEventFeeTypeEnum::PER_PERSON->value)
            ->where('is_active', true)
            ->value('price') ?? 0;
    }

    protected function calculateTotalCost(): void
    {
        $this->totalCost = $this->selectedCount * $this->getPerPersonPrice();
    }

    public function register(): void
    {
        try {
            // Validate inputs
            $this->validate([
                'selectedParticipants' => ['required', 'array', 'min:1'],
                'selectedPricing' => ['required', 'string', 'exists:evt_pricing,id'],
            ], [
                'selectedParticipants.required' => __('events.select_at_least_one_participant'),
                'selectedParticipants.min' => __('events.select_at_least_one_participant'),
                'selectedPricing.required' => __('events.select_pricing_option'),
                'selectedPricing.exists' => __('events.invalid_pricing_option'),
            ]);

            $pricing = Pricing::findOrFail($this->selectedPricing);
            $enrollable = $this->getEnrollable();
            $individualIds = array_column($this->selectedParticipants, 'id');
            $individuals = Individual::findMany($individualIds);

            // Create main enrollment record
            $enrollment = (new CreateEnrollmentAction)->execute(
                enrollable: $enrollable,
                event: $this->event,
                pricingId: $pricing->id
            );

            // Prepare selected individuals data
            $selectedIndividualsData = $individuals->map(fn ($individual) => [
                'individual_id' => $individual->id,
                'role' => EvtEventEnrollmentRoleEnum::INDIVIDUAL->value,
                'pricing_id' => $pricing->id,
                'price_type' => $pricing->price_type,
                'federation_id' => $enrollable instanceof Federation ? $enrollable->id : null,
                'entity_id' => $enrollable instanceof Entity ? $enrollable->id : null,
                'name' => $individual->name,
                'surname' => $individual->surname,
            ])->toArray();

            // Create payment document if needed
            $document = $this->totalCost > 0
                ? (new CreateEnrollmentPaymentDocumentAction)->execute(
                    event: $this->event,
                    enrollment: $enrollment,
                    enrollable_id: $enrollable->id,
                    enrollable_type: $this->getEnrollableType(),
                    selectedIndividuals: $selectedIndividualsData,
                    totalCost: $this->totalCost,
                    pricingId: $pricing->id
                )
                : null;

            if ($document) {
                $enrollable->documents()->save($document);
            }

            // Determine the appropriate status based on whether payment is required
            $initialStatus = $this->totalCost > 0
                ? EvtIndividualEnrollmentStatusEnum::PENDING->value
                : EvtIndividualEnrollmentStatusEnum::PAID->value;

            // Create individual enrollments
            foreach ($selectedIndividualsData as $individualData) {
                $attributeValues = $this->collectAttributeValues($individualData);

                $individualEnrollment = (new CreateIndividualEnrollmentAction)->execute(
                    $this->event,
                    $this->model,
                    Individual::find($individualData['individual_id']),
                    $enrollment,
                    $initialStatus,
                    $attributeValues,
                    $individualData['pricing_id'],
                    $individualData['entity_id'],
                    $individualData['price_type']
                );
            }

            // Show success message
            $this->showSuccessMessage = true;
            $this->successMessage = $this->totalCost > 0
                ? __('events.participants_registered_proceed_payment')
                : __('events.participants_registered_confirmed');
            $this->documentId = $document?->id;
            $this->participantCount = $this->selectedCount;

            $this->reset(['selectedParticipants', 'selectedPricing', 'totalCost']);
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('events.registration_failed'))
                ->body($e->getMessage())
                ->danger()
                ->send();

            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'event_id' => $this->event->id,
                'user_id' => Auth::id() ?? null,
            ]);
        }
    }

    protected function getRegistrationStats(): array
    {
        return [
            'registered' => $this->getRegisteredCount(),
            'pendingAssignment' => $this->getPendingAssignmentCount(),
        ];
    }

    public function addParticipants(Collection $records): void
    {
        $this->selectedParticipants = $records->map(fn ($record) => [
            'id' => $record->id,
            'name' => $record->full_name,
            'price' => $this->perPersonPrice,
        ])->toArray();

        $this->selectedCount = count($this->selectedParticipants);
        $this->calculateTotalCosts();
    }

    protected function calculateTotalCosts(): void
    {
        $this->totalCost = $this->selectedCount * $this->perPersonPrice;
    }

    protected function getRegisteredCount(): int
    {
        return IndividualEnrollment::query()
            ->where('event_id', $this->event->id)
            ->where($this->getEnrollableForeignKey(), $this->model->id)
            ->count();
    }

    protected function getPendingAssignmentCount(): int
    {
        return IndividualEnrollment::query()
            ->where('event_id', $this->event->id)
            ->where($this->getEnrollableForeignKey(), $this->model->id)
            ->whereNull('status_class')
            ->count();
    }

    protected function initializeAttributes(): void
    {
        try {
            // Use the action to get role-specific attributes
            $attributes = (new GetAttributesAndRulesFromRolesAction)->execute(
                EvtEventEnrollmentRoleEnum::INDIVIDUAL->value,
                $this->event->id  // Pass the event ID to filter attributes
            );

            // Separate global and role-specific attributes
            $this->globalAttributes = array_filter($attributes, fn ($attribute) => $attribute['attribute_data']['fillable_global']);
            $this->roleAttributes = array_filter($attributes, fn ($attribute) => ! $attribute['attribute_data']['fillable_global']);

            // Initialize default values for attributes
            $this->initializeAttributeValues();
        } catch (\InvalidArgumentException $e) {
            Log::error('Error initializing attributes', [
                'event_id' => $this->event->id,
                'error' => $e->getMessage(),
            ]);

            Notification::make()
                ->title(__('events.configuration_error'))
                ->body(__('events.error_initializing_attributes'))
                ->danger()
                ->send();

            $this->redirect(route($this->getRedirectRoute(), $this->event));
        }
    }

    protected function initializeAttributeValues(): void
    {
        foreach ($this->selectedParticipants as $participant) {
            // Initialize role attribute values
            foreach ($this->roleAttributes as $attribute) {
                if (! isset($this->roleAttributeValues[$participant['id']][$attribute['attribute_data']['id']])) {
                    $this->roleAttributeValues[$participant['id']][$attribute['attribute_data']['id']] =
                        $attribute['attribute_data']['default_value'] ?? null;
                }
            }

            // Initialize global attribute values
            foreach ($this->globalAttributes as $attribute) {
                if (! isset($this->globalAttributeValues[$attribute['attribute_data']['id']])) {
                    $this->globalAttributeValues[$attribute['attribute_data']['id']] =
                        $attribute['attribute_data']['default_value'] ?? null;
                }
            }
        }
    }

    protected function collectAttributeValues(): array
    {
        $attributeValues = [];

        // Role-specific attributes
        foreach ($this->roleAttributeValues as $id => $value) {
            $attributeValues[$id] = $value;
        }

        // Global attributes
        foreach ($this->globalAttributeValues as $id => $value) {
            $attributeValues[$id] = $value;
        }

        return $attributeValues;
    }

    public function updatedActiveTab($value): void
    {
        if ($value === 'attributes') {
            // No need to recalculate attributes here
        }
    }

    public function updatedSelectedRecords($value): void
    {
        $this->selectedParticipants = $value;
        $this->selectedCount = count($value);
        $this->calculateTotalCost();

        // Initialize attribute values for the newly selected participants
        $this->initializeAttributeValues();

        // Automatically switch to attributes tab if there are participants
        if (! empty($this->selectedParticipants) && ! empty($this->roleAttributes)) {
            $this->activeTab = 'attributes';
        }
    }

    public function submit(): void
    {
        try {
            DB::beginTransaction();

            // Save participants
            $enrollment = (new CreateEventEnrollmentAction)->execute([
                'event_id' => $this->event->id,
                'participants' => $this->selectedParticipants,
                'attributes' => $this->getAttributeValues(),
                'pricing_option' => $this->selectedPricing,
            ]);

            DB::commit();

            $this->redirectRoute('event-registration-success', $enrollment->id);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->notification()->error('Error saving registration: ' . $e->getMessage());
        }
    }

    protected function getAttributeValues(): array
    {
        $attributeValues = [];

        // Role-specific attributes
        foreach ($this->roleAttributeValues as $participantId => $values) {
            foreach ($values as $attributeId => $value) {
                $attributeValues[] = [
                    'participant_id' => $participantId,
                    'attribute_id' => $attributeId,
                    'value' => $value,
                ];
            }
        }

        // Global attributes
        foreach ($this->globalAttributeValues as $attributeId => $value) {
            $attributeValues[] = [
                'attribute_id' => $attributeId,
                'value' => $value,
            ];
        }

        return $attributeValues;
    }

    /**
     * Remove a participant from the selected participants list
     */
    public function removeParticipant(string $participantId): void
    {
        // Find and remove the participant from the selectedParticipants array
        $this->selectedParticipants = array_filter(
            $this->selectedParticipants,
            fn ($participant) => $participant['id'] != $participantId
        );

        // Clean up any attribute values for this participant
        if (isset($this->roleAttributeValues[$participantId])) {
            unset($this->roleAttributeValues[$participantId]);
        }

        // Update the count and recalculate costs
        $this->selectedCount = count($this->selectedParticipants);
        $this->calculateTotalCost();

        // If no participants left, go back to participants tab
        if ($this->selectedCount === 0) {
            $this->activeTab = 'participants';
        }
    }

    public function render()
    {
        return view('livewire.evt-events.individual-event-registration');
    }
}
