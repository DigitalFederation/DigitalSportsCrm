<?php

namespace App\Livewire\EvtEvents;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Enums\EvtEventEnrollmentRoleEnum;
use App\Enums\EvtEventFeeTypeEnum;
use App\Enums\EvtEventPaymentStatusEnum;
use App\Enums\OfficialDocumentTypeEnum;
use Domain\Documents\States\PendingDocumentState;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Actions\CheckExistingEventEnrollmentAction;
use Domain\EvtEvents\Actions\GetEligibleAthletesAction;
use Domain\EvtEvents\Actions\GetEligibleCoachesAction;
use Domain\EvtEvents\Actions\GetEligibleEntityAthletesAction;
use Domain\EvtEvents\Actions\GetEligibleRefereesAction;
use Domain\EvtEvents\Actions\PreRegisterParticipantsAction;
use Domain\EvtEvents\Actions\ValidateEnrollmentPricingAction;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\EvtEvents\Services\EnrollmentCreditService;
use Domain\EvtEvents\Services\EnrollmentsCostCalculationService;
use Domain\EvtEvents\States\ActiveRefereeEnrollmentState;
use Domain\EvtEvents\States\RegisteredCoachEnrollmentState;
use Domain\EvtEvents\States\RegisteredTeamOfficialEnrollmentState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Domain\OfficialDocuments\States\ActiveOfficialDocumentState;
use Exception;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Component;

class EventRegistration extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public Event $event;
    public Model $model;
    protected $tableRecords = [];
    protected $selectedRecords = [];

    // Add property to store stats
    public array $registrationStats = [];

    // Role Management
    #[Url]
    public string $activeRole = 'athlete';
    public array $selectedParticipants = [
        'athlete' => [],
        'coach' => [],
        'referee' => [],
        'official' => [],
    ];
    public array $rolePricing = [];
    public array $roleCosts = [];
    public float $totalCost = 0;
    protected $costCalculationService;
    protected $creditService;

    public $totalSelectedCost = 0;
    public $perPersonPrice = 0;
    public $selectedCount = 0;

    public string $activeTab = 'register';
    public bool $roleChanged = false;
    public bool $isEntity = false;

    protected $listeners = ['reload-table' => '$refresh'];

    public bool $showSuccessModal = false;
    public array $successModalData = [];

    public bool $showRemoveConfirmation = false;
    public ?string $participantToRemoveId = null;
    public ?string $participantToRemoveRole = null;

    // Add property for available credits
    public array $availableCredits = [];

    public function boot(
        EnrollmentsCostCalculationService $costCalculationService,
        EnrollmentCreditService $creditService
    ) {
        $this->costCalculationService = $costCalculationService;
        $this->creditService = $creditService;
    }

    public function booted(): void
    {
        $this->dispatch('filament-tables::reorder');
    }

    public function mount(
        Event $event,
        Model $model
    ): void {

        // Explicitly check for valid model types
        if (! ($model instanceof Entity || $model instanceof Federation)) {
            throw new \InvalidArgumentException('Invalid model type provided');
        }

        $this->event = $event;
        $this->model = $model;

        try {
            // Validate if pricing is set on event
            (new ValidateEnrollmentPricingAction)->execute($event, $this->activeRole);

            $this->perPersonPrice = $this->getPerPersonPrice();
            $this->initializePricingForRoles();
            $this->calculateTotalCosts();
            $this->registrationStats = $this->getRegistrationStats();

            // Get available credits
            $this->availableCredits = app(EnrollmentCreditService::class)
                ->getAvailableCredits($event, $model);

            if ($model instanceof Entity) {
                $this->isEntity = true;
            }
        } catch (ValidationException $e) {

            // Flash a session message using Livewire's session helper
            session()->flash('error', __('Please check the event pricing configuration before proceeding.'));
            $this->redirect(route($this->getRedirectRoute(), $event));
        }
    }

    protected function getRedirectRoute(): string
    {
        if ($this->model instanceof Federation) {
            return 'federation.evt-events.events.show';
        }
        if ($this->model instanceof Entity) {

            return 'entity.evt-events.events.show';
        }

        throw ValidationException::withMessages([
            'model' => 'Invalid organization type',
        ]);
    }

    public function getRegistrationStats(): array
    {
        return [
            'registered' => $this->getRegisteredCount(),
            'withDiscipline' => $this->getWithDisciplineCount(),
            'pendingAssignment' => $this->getPendingAssignmentCount(),
        ];
    }

    protected function getRegisteredCount(): int
    {
        return $this->event->athleteEnrollments()
            ->when(
                $this->model instanceof Federation,
                fn ($q) => $q->where('federation_id', $this->model->id)
            )
            ->when(
                $this->model instanceof Entity,
                fn ($q) => $q->where('entity_id', $this->model->id)
            )
            ->whereIn('status_class', [
                EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
                EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value,
                EvtAthleteEnrollmentStatusEnum::PAID->value,
            ])
            ->count();
    }

    protected function getWithDisciplineCount(): int
    {
        return $this->event->athleteEnrollments()
            ->when(
                $this->model instanceof Federation,
                fn ($q) => $q->where('federation_id', $this->model->id)
            )
            ->when(
                $this->model instanceof Entity,
                fn ($q) => $q->where('entity_id', $this->model->id)
            )
            ->whereNotNull('discipline_id')
            ->whereIn('status_class', [
                EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
                EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value,
                EvtAthleteEnrollmentStatusEnum::PAID->value,
            ])
            ->count();
    }

    protected function getPendingAssignmentCount(): int
    {
        return $this->event->athleteEnrollments()
            ->when(
                $this->model instanceof Federation,
                fn ($q) => $q->where('federation_id', $this->model->id)
            )
            ->when(
                $this->model instanceof Entity,
                fn ($q) => $q->where('entity_id', $this->model->id)
            )
            ->whereNull('discipline_id')
            ->whereIn('status_class', [
                EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
                EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value,
            ])
            ->count();
    }

    private function initializePricingForRoles(): void
    {
        $this->rolePricing = [
            'athlete' => $this->getRolePricing(EvtEventEnrollmentRoleEnum::ATHLETE),
            'coach' => $this->getRolePricing(EvtEventEnrollmentRoleEnum::COACH),
            'referee' => $this->getRolePricing(EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL),
            'official' => $this->getRolePricing(EvtEventEnrollmentRoleEnum::OFFICIAL),
        ];
    }

    private function getRolePricing(EvtEventEnrollmentRoleEnum $role): ?float
    {
        return Pricing::query()
            ->where('event_id', $this->event->id)
            ->where('enrollment_role', $role)
            ->where('is_active', true)
            ->value('price') ?? 0;
    }

    private function calculateTotalCosts(): void
    {
        $this->totalCost = array_sum($this->roleCosts);
    }

    private function calculateRoleCost(string $role): void
    {
        $participantCount = count($this->selectedParticipants[$role]);
        $this->roleCosts[$role] = $participantCount * ($this->rolePricing[$role] ?? 0);
    }

    public function updateTotalCost(): void
    {
        $selectedRecords = $this->getSelectedTableRecords();
        $this->selectedCount = $selectedRecords->count();
        $this->totalSelectedCost = $this->selectedCount * $this->perPersonPrice;
    }

    protected function getPerPersonPrice(): float
    {
        return Pricing::query()
            ->where('event_id', $this->event->id)
            ->where('price_type', EvtEventFeeTypeEnum::PER_PERSON->value)
            ->where('is_active', true)
            ->value('price') ?? 0;
    }

    protected function getEligibleOfficialsQuery(): Builder
    {
        return Individual::query()
            ->when($this->model instanceof Federation, function (Builder $query) {
                $query->whereHas('individualFederations', function (Builder $q) {
                    $q->where('federation_id', $this->model->id)
                        ->where('status_class', ActiveIndividualFederationState::class);
                });
            })
            ->when($this->model instanceof Entity, function (Builder $query) {
                $query->whereHas('individualEntities', function (Builder $q) {
                    $q->where('entity_id', $this->model->id)
                        ->where('status_class', ActiveIndividualEntityState::class);
                });
            })
            ->whereDoesntHave('officialsEnrollments', function (Builder $query) {
                $query->where('event_id', $this->event->id)
                    ->whereIn('status_class', [
                        EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
                        EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value,
                        EvtAthleteEnrollmentStatusEnum::PAID->value,
                    ]);
            });
    }

    public function getPendingEnrollments()
    {
        return $this->event->enrollments()
            ->with([
                'athleteEnrollments.individual',
                'coachEnrollments.individual',
                'refereeEnrollments.individual',
                'teamOfficialEnrollments.individual',
                'document',
            ])
            ->where('enrollable_id', $this->model->id)
            ->where('enrollable_type', $this->model instanceof Federation ? Federation::class : Entity::class)
            ->where(function ($query) {
                $query->whereNull('payment_status')
                    ->orWhere('payment_status', EvtEventPaymentStatusEnum::PENDING->value);
            })
            ->whereHas('document', function ($query) {
                $query->where('status_class', PendingDocumentState::class);
            })
            ->get()
            ->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'total_price' => $enrollment->total_price,
                    'document_id' => $enrollment->document?->id,
                    'document_number' => $enrollment->document?->number_extended,
                    'participants' => collect([
                        ...$enrollment->athleteEnrollments->map(fn ($e) => [
                            'name' => $e->individual?->full_name,
                            'role' => 'Athlete',
                            'price' => $e->total_price,
                        ]),
                        ...$enrollment->coachEnrollments->map(fn ($e) => [
                            'name' => $e->individual->full_name,
                            'role' => 'Coach',
                            'price' => $e->total_price,
                        ]),
                        ...$enrollment->refereeEnrollments->map(fn ($e) => [
                            'name' => $e->individual->full_name,
                            'role' => 'Referee',
                            'price' => $e->total_price,
                        ]),
                        ...$enrollment->teamOfficialEnrollments->map(fn ($e) => [
                            'name' => $e->individual->full_name,
                            'role' => 'Official',
                            'price' => $e->total_price,
                        ]),
                    ])->toArray(),
                ];
            });
    }

    /**
     * Table - Query
     */
    private function getQueryForRole(): Builder
    {
        try {
            $organizationType = $this->model instanceof Entity ? 'entity' : 'federation';

            return match ($this->activeRole) {
                'coach' => app(GetEligibleCoachesAction::class)->execute(
                    $this->event,
                    $this->model->id,
                    $organizationType
                ),
                'referee' => app(GetEligibleRefereesAction::class)->execute(
                    $this->event,
                    $this->model->id,
                    $organizationType
                ),
                'official' => $this->getEligibleOfficialsQuery(),
                default => $this->model instanceof Entity // Athletes
                    ? app(GetEligibleEntityAthletesAction::class)->execute(
                        $this->event->id,
                        $this->model->id,
                        null
                    )
                    : app(GetEligibleAthletesAction::class)->execute(
                        $this->event->id,
                        $this->model->id,
                        null
                    )
            };
        } catch (\Exception $e) {
            Log::error('Error in getEligibleEntityAthletesQuery', [
                'error' => $e->getMessage(),
                'model_id' => $this->model->id,
                'event_id' => $this->event->id,
            ]);
            throw $e;
        }
    }

    private function getEnrollmentRelationName(): string
    {
        return match ($this->activeRole) {
            'athlete' => 'athleteEnrollments',
            'coach' => 'coachEnrollments',
            'referee' => 'refereeEnrollments',
            'official' => 'teamOfficialEnrollments',
            default => 'athleteEnrollments',
        };
    }

    public function addParticipants(Collection $records): void
    {
        \Log::info('EventRegistration::addParticipants - Start', [
            'records' => $records->toArray(),
            'active_role' => $this->activeRole,
            'existing_participants' => $this->selectedParticipants,
        ]);

        // Get existing participant IDs for the active role
        $existingIds = array_column($this->selectedParticipants[$this->activeRole], 'id');

        // Filter out duplicates
        $newParticipants = $records->reject(fn ($record) => in_array($record->id, $existingIds));

        \Log::info('EventRegistration::addParticipants - After duplicate check', [
            'new_participants' => $newParticipants->toArray(),
        ]);

        // Check if any individuals are already enrolled in the event
        foreach ($newParticipants as $key => $record) {
            // Only check for athlete role since other roles are already filtered by the query
            if ($this->activeRole === 'athlete') {
                // For the first step, we use a placeholder disciplineId of empty string
                // This will allow athletes to be registered by both federation and entity
                // Different disciplines will be assigned at a later stage
                $checkExistingEnrollment = app(CheckExistingEventEnrollmentAction::class)
                    ->execute(
                        $this->event,
                        $record,
                        '', // Empty discipline ID allows cross-organization registration
                        $this->model
                    );

                if (! $checkExistingEnrollment['can_register']) {
                    // Remove the record from the collection
                    $newParticipants->forget($key);

                    // Show a notification about the issue
                    Notification::make()
                        ->title('Cannot Add Participant')
                        ->body($checkExistingEnrollment['message'])
                        ->warning()
                        ->send();
                }
            }
        }

        // Map to the format needed for this component
        $formattedParticipants = $newParticipants->map(fn ($record) => [
            'id' => $record->id,
            'name' => $record->full_name,
            'price' => $this->rolePricing[$this->activeRole] ?? 0,
        ])->toArray();

        // Check if any documents are required for the current role
        $requiredDocuments = $this->event->competition?->getRequiredDocumentsFor($this->activeRole) ?? [];

        if (! empty($requiredDocuments)) {
            foreach ($formattedParticipants as $participant) {
                foreach ($requiredDocuments as $documentType) {
                    $hasDocument = OfficialDocument::where('individual_id', $participant['id'])
                        ->where('type', $documentType)
                        ->where('status_class', ActiveOfficialDocumentState::class)
                        ->where(function ($q) {
                            $q->whereNull('expiry_date')
                                ->orWhereDate('expiry_date', '>=', $this->event->end_date);
                        })
                        ->exists();

                    if (! $hasDocument) {
                        $docLabel = OfficialDocumentTypeEnum::toString($documentType);

                        Notification::make()
                            ->title(__('events.missing_required_document'))
                            ->body(__('events.participant_missing_document', [
                                'name' => $participant['name'],
                                'document' => $docLabel,
                            ]))
                            ->warning()
                            ->send();

                        return;
                    }
                }
            }
        }

        // Add new participants
        $this->selectedParticipants[$this->activeRole] = array_merge(
            $this->selectedParticipants[$this->activeRole],
            $formattedParticipants
        );

        // Show notification if duplicates were found
        if ($records->count() !== count($formattedParticipants)) {
            Notification::make()
                ->title(__('Duplicate participants detected'))
                ->body(__('Some participants were already selected and were not added again.'))
                ->warning()
                ->send();
        }

        $this->calculateRoleCost($this->activeRole);
        $this->calculateTotalCosts();
    }

    /**
     * FilamentPHP Table
     *
     * @throws \Exception
     */
    public function table(Table $table): Table
    {

        return $table
            ->query($this->getQueryForRole())
            ->columns([
                TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(['name', 'surname']),
                TextColumn::make('member_code')
                    ->label('International Code')
                    ->searchable(),
                TextColumn::make('birthdate')
                    ->date()
                    ->sortable(),
                TextColumn::make('gender')
                    ->label('Gender'),
            ])
            ->filters([
                SelectFilter::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                    ])
                    ->searchable(),
            ], layout: FiltersLayout::AboveContent)
            ->defaultPaginationPageOption(100)
            ->filtersFormColumns(3)
            ->searchDebounce(500)
            ->searchPlaceholder('Search by name, surname or CMAS code...')
            ->selectable()
            ->deferLoading(false)
            ->queryStringIdentifier('role_' . $this->activeRole)
            ->bulkActions([
                BulkAction::make('select')
                    ->label('Add to Registration')
                    ->action(fn ($records) => $this->addParticipants($records))
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    /**
     * Handle the registration process for all selected participants.
     * After successful pre-registration, redirects to Step 2 (ManageEnrollment)
     * for discipline assignment and attribute collection.
     */
    public function submitRegistration(): void
    {
        try {
            DB::beginTransaction();

            // Filter out empty participant arrays
            $participants = array_filter($this->selectedParticipants, fn ($roleParticipants) => ! empty($roleParticipants));

            if (empty($participants)) {
                Notification::make()
                    ->title(__('No participants selected for registration.'))
                    ->body(__('Please use the table to select and click on the "Add to Registration" button to add participants.'))
                    ->danger()
                    ->send();

                return;
            }

            // Final validation - check if any athletes are already enrolled elsewhere
            if (isset($participants['athlete'])) {
                $invalidParticipants = [];

                foreach ($participants['athlete'] as $participant) {
                    $individual = Individual::find($participant['id']);

                    if (! $individual) {
                        continue;
                    }

                    // For registration we use empty string as discipline ID to allow
                    // the same athlete to be registered by both federation and entity
                    // Discipline-specific validation will happen at assignment stage
                    $checkExistingEnrollment = app(CheckExistingEventEnrollmentAction::class)
                        ->execute(
                            $this->event,
                            $individual,
                            '', // Empty string enables cross-organization registration
                            $this->model
                        );

                    if (! $checkExistingEnrollment['can_register']) {
                        $invalidParticipants[] = [
                            'name' => $individual->full_name,
                            'message' => $checkExistingEnrollment['message'],
                        ];
                    }
                }

                if (! empty($invalidParticipants)) {
                    DB::rollBack();

                    $errorMessage = __('Cannot register the following athletes:') . '<ul>';
                    foreach ($invalidParticipants as $participant) {
                        $errorMessage .= '<li>' . $participant['name'] . ': ' . $participant['message'] . '</li>';
                    }
                    $errorMessage .= '</ul>';

                    $this->dispatch('show-error-modal', [
                        'title' => __('Registration Failed'),
                        'message' => $errorMessage,
                        'errorDetails' => __('Please remove these athletes from your selection and try again.'),
                    ]);

                    return;
                }
            }

            // Format participants data for PreRegisterParticipantsAction
            $formattedParticipants = $this->formatParticipantsForRegistration($participants);

            // Check for and apply available credits
            $creditService = app(EnrollmentCreditService::class);
            $creditsUsed = $creditService->useCredits($this->event, $this->model, $participants);

            // Execute pre-registration with credit information
            $enrollment = app(PreRegisterParticipantsAction::class)->execute(
                $this->event,
                $this->model,
                $formattedParticipants,
                $creditsUsed
            );

            DB::commit();

            // Reset selections after successful registration
            $this->resetSelections();

            // Refresh registration stats and available credits
            $this->registrationStats = $this->getRegistrationStats();
            $this->availableCredits = $creditService->getAvailableCredits($this->event, $this->model);

            $this->dispatch('registration-completed', id: $enrollment->id);

            // Show success modal with redirect to Step 2 (ManageEnrollment)
            $this->showSuccessModal = true;

            // Build the Step 2 route for discipline assignment
            $manageEnrollmentRoute = $this->model instanceof Federation
                ? route('federation.evt-events.events.enrollments.create', ['event' => $this->event, 'type' => 'athlete'])
                : route('entity.evt-events.events.enrollments.create', ['event' => $this->event, 'type' => 'athlete']);

            $this->successModalData = [
                'title' => __('events.step1_complete_title'),
                'message' => __('events.step1_complete_message'),
                'nextSteps' => [
                    [
                        'label' => __('events.proceed_to_step2'),
                        'url' => $manageEnrollmentRoute,
                        'primary' => true,
                    ],
                ],
            ];

            // Add credit usage information to success message
            if (! empty($creditsUsed)) {
                $creditInfo = [];
                foreach ($creditsUsed as $roleType => $used) {
                    if ($used['slots_used'] > 0) {
                        $roleLabel = match ($roleType) {
                            'athlete' => 'athlete',
                            'coach' => 'coach',
                            'referee' => 'referee',
                            'official' => 'team official',
                            default => 'participant'
                        };
                        $creditInfo[] = "{$used['slots_used']} {$roleLabel} credit(s)";
                    }
                }

                if (! empty($creditInfo)) {
                    $this->successModalData['message'] .= ' ' . __('events.credits_used') . ': ' . implode(', ', $creditInfo) . '.';
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
            $this->dispatch('show-error-modal', [
                'title' => __('Registration Failed'),
                'message' => __('Failed to register participants. Please try again.'),
                'errorDetails' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Format selected participants for the registration action.
     * Disciplines are assigned in Step 2 (ManageEnrollment), not here.
     */
    protected function formatParticipantsForRegistration(array $participants): array
    {
        $formatted = [];

        foreach ($participants as $roleType => $roleParticipants) {
            if (empty($roleParticipants)) {
                continue;
            }

            $formatted[$roleType] = array_map(function ($participant) {
                return [
                    'id' => $participant['id'],
                    'discipline_id' => null, // Discipline assigned in Step 2
                ];
            }, $roleParticipants);
        }

        return $formatted;
    }

    /**
     * Reset all selections after successful registration
     */
    protected function resetSelections(): void
    {
        $this->selectedParticipants = [
            'athlete' => [],
            'coach' => [],
            'referee' => [],
            'official' => [],
        ];
        $this->roleCosts = [];
        $this->totalCost = 0;
    }

    public function removeParticipant(string $role, string $participantId): void
    {
        $this->selectedParticipants[$role] = array_filter(
            $this->selectedParticipants[$role],
            fn ($p) => $p['id'] !== $participantId
        );

        $this->calculateRoleCost($role);
        $this->calculateTotalCosts();
    }

    /**
     * Event properties that allow to register or not determined rle of user.
     */
    private function getAvailableRoles(): array
    {
        return [
            'athlete' => true,
            'coach' => $this->event->allow_coach_enrollment,
            'referee' => $this->event->allow_referee_enrollment,
            'official' => true, // Always available
        ];
    }

    // Modify the render method to handle role changes
    public function render()
    {
        if ($this->roleChanged) {
            $this->roleChanged = false;
            $this->dispatch('filament-tables::reset');
        }

        // Initialize error variable
        $error = null;
        $registeredParticipants = null;

        // Try to get registered participants, catching any errors
        try {
            $registeredParticipants = $this->getRegisteredParticipants();
        } catch (\Exception $e) {
            Log::error('Error loading registered participants', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'model_type' => get_class($this->model),
                'model_id' => $this->model->id,
                'event_id' => $this->event->id,
            ]);

            $error = $e->getMessage();
        }

        return view('livewire.evt-events.event-registration', [
            'selectedParticipants' => $this->selectedParticipants,
            'roleCosts' => $this->roleCosts,
            'totalCost' => $this->totalCost,
            'pendingEnrollments' => $this->getPendingEnrollments(),
            'availableRoles' => $this->getAvailableRoles(),
            'isEntity' => $this->isEntity,
            'registrationStats' => $this->registrationStats,
            'registeredParticipants' => $registeredParticipants,
            'availableCredits' => $this->availableCredits,
            'error' => $error,
        ]);
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [25, 50, 100];
    }

    // Add this method to handle table resets
    public function resetTable(): void
    {

        $this->tableFilters = null;
        $this->tableSortColumn = null;
        $this->tableSortDirection = null;
    }

    public function updatingActiveRole($value): void
    {
        $this->resetTable();
    }

    public function updatedActiveRole($value): void
    {
        $this->roleChanged = true;

        // Clear all states
        $this->resetTable();
        $this->tableRecords = null;
        $this->dispatch('filament-tables::reset');

        // Force new query execution
        $this->tableSortColumn = null;
        $this->tableSortDirection = null;
        $this->tableFilters = [];
        $this->resetPage();

        // Force immediate reload
        $this->dispatch('reload-table');
    }

    private function getRegisteredParticipants(): array
    {
        $results = [
            'participants' => [],
            'count' => 0,
        ];

        // Define role relationships with their correct names
        $roles = [
            'athlete' => 'athleteEnrollments',
            'coach' => 'coachEnrollments',
            'official' => 'officialsEnrollments',
        ];

        foreach ($roles as $role => $relationship) {
            // Only load discipline relationship for athlete enrollments
            $with = ['individual', 'enrollment'];
            if ($role === 'athlete') {
                $with[] = 'discipline';
            }

            $enrollments = $this->event->{$relationship}()
                ->whereHas('enrollment', function ($query) {
                    $query->where('enrollable_id', $this->model->id)
                        ->where('enrollable_type', $this->model instanceof Federation ? Federation::class : Entity::class);
                })
                ->groupBy('individual_id')
                ->with($with)
                ->get();

            foreach ($enrollments as $enrollment) {
                // Get the appropriate status based on role type
                $status = '';
                if ($role === 'athlete') {
                    // For athletes, use the status_class directly
                    $status = is_object($enrollment->status_class)
                        ? $enrollment->status_class->value
                        : $enrollment->status_class;
                } else {
                    // For coaches and officials, use the state to get a readable status
                    try {
                        // Get short name from state pattern class
                        if (is_string($enrollment->status_class) && class_exists($enrollment->status_class)) {
                            $statusParts = explode('\\', $enrollment->status_class);
                            $statusClass = end($statusParts);

                            // Remove "EnrollmentState" suffix and convert to snake case
                            $status = str_replace(['Coach', 'TeamOfficial', 'EnrollmentState'], '', $statusClass);
                            // Convert camel case to snake case
                            $status = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $status));
                        } else {
                            // Fallback to state name if available
                            $status = method_exists($enrollment, 'stateName') ?
                                strtolower($enrollment->stateName()) : 'unknown';
                        }
                    } catch (\Exception $e) {
                        Log::error('Error getting enrollment status', [
                            'error' => $e->getMessage(),
                            'enrollment_id' => $enrollment->id,
                            'status_class' => $enrollment->status_class,
                        ]);
                        $status = 'unknown';
                    }
                }

                $participantData = [
                    'id' => $enrollment->id,
                    'individual_id' => $enrollment->individual_id,
                    'name' => $enrollment->individual->full_name,
                    'role' => $role,
                    'status' => $status,
                    'price' => $enrollment->total_price,
                ];

                // Only set discipline data for athletes
                if ($role === 'athlete') {
                    $participantData['discipline_id'] = $enrollment->discipline_id;
                    $participantData['discipline_name'] = $enrollment->discipline->name ?? null;
                    $participantData['can_delete'] = $enrollment->discipline_id === null;
                } else {
                    // Non-athletes can always be deleted
                    $participantData['discipline_id'] = null;
                    $participantData['discipline_name'] = null;
                    $participantData['can_delete'] = true;
                }

                $results['participants'][] = $participantData;
            }
        }

        // Set total count
        $results['count'] = count($results['participants']);

        return $results;
    }

    // Update the removeRegisteredParticipant method
    public function removeRegisteredParticipant(): void
    {
        try {
            DB::beginTransaction();

            // Get stored values
            $role = $this->participantToRemoveRole;
            $participantId = $this->participantToRemoveId;

            // Determine which relation to use based on role
            $relation = match ($role) {
                'athlete' => 'athleteEnrollments',
                'coach' => 'coachEnrollments',
                'referee' => 'refereeEnrollments',
                'official' => 'officialsEnrollments',
                default => throw new \Exception('Invalid role')
            };

            // Get the enrollment record
            $enrollment = $this->event->$relation()
                ->where('id', $participantId)
                ->whereHas('enrollment', function ($query) {
                    $query->where('enrollable_id', $this->model->id)
                        ->where('enrollable_type', get_class($this->model));
                })
                ->first();

            if (! $enrollment) {
                throw new \Exception('Enrollment not found');
            }

            // For athletes, validate removal is allowed
            if ($role === 'athlete' && $enrollment->discipline_id !== null) {
                throw new \Exception('Cannot remove an athlete with assigned discipline');
            }

            // Check if the main enrollment record exists and was paid for
            $mainEnrollment = $enrollment->enrollment;
            $creditMessage = null;

            // Check if this enrollment should generate a credit (was it paid for?)
            $shouldAddCredit = false;

            // Method 1: Check main enrollment payment status
            if ($mainEnrollment && ($mainEnrollment->payment_status === EvtEventPaymentStatusEnum::PAID ||
                $mainEnrollment->payment_status === 'PAID')) {
                $shouldAddCredit = true;
            }

            // Method 2: Check if there's an associated document that was paid
            /*
            if (!$shouldAddCredit && $mainEnrollment && $mainEnrollment->document_id) {
                // Fetch the document directly to avoid the relationship query error
                $document = \Domain\Documents\Models\Document::find($mainEnrollment->document_id);
                if ($document) {
                    $shouldAddCredit = $document->isPaid();
                }
            }
            */
            $shouldAddCredit = false;

            // Method 3: Check the enrollment's specific status (as a fallback)
            if (! $shouldAddCredit) {
                $shouldAddCredit = match ($role) {
                    'athlete' => $enrollment->status_class == EvtAthleteEnrollmentStatusEnum::PAID->value,
                    'coach' => $enrollment->status_class == RegisteredCoachEnrollmentState::class,
                    'referee' => $enrollment->status_class == ActiveRefereeEnrollmentState::class,
                    'official' => $enrollment->status_class == RegisteredTeamOfficialEnrollmentState::class,
                    default => false
                };
            }

            // If any payment verification succeeded, add a credit
            if ($shouldAddCredit) {
                // Get credit service and add credit
                $creditService = app(EnrollmentCreditService::class);
                $creditInfo = $creditService->addCredit($enrollment);

                // Create message about the added credit
                $roleLabel = match ($role) {
                    'athlete' => 'athlete',
                    'coach' => 'coach',
                    'referee' => 'referee',
                    'official' => 'team official',
                    default => 'participant'
                };

                $creditMessage = " A replacement credit has been added for this {$roleLabel}.";

                Log::info('Added enrollment credit for removed participant', [
                    'event_id' => $this->event->id,
                    'enrollable_type' => get_class($this->model),
                    'enrollable_id' => $this->model->id,
                    'role' => $role,
                    'individual_id' => $enrollment->individual_id,
                    'credit_added' => $creditInfo,
                ]);
            } else {
                Log::info('No credit added for removed participant (not paid)', [
                    'event_id' => $this->event->id,
                    'enrollable_type' => get_class($this->model),
                    'enrollable_id' => $this->model->id,
                    'role' => $role,
                    'individual_id' => $enrollment->individual_id,
                    'enrollment_status' => $enrollment->status_class,
                    'main_enrollment_payment_status' => $mainEnrollment ? $mainEnrollment->payment_status : null,
                ]);
            }

            // Delete enrollment (existing code)
            if (method_exists($enrollment, 'attributes') && $enrollment->attributes) {
                $enrollment->attributes()->delete();
            }
            $enrollment->delete();

            DB::commit();

            // Refresh data and show notification
            $this->registrationStats = $this->getRegistrationStats();
            $this->availableCredits = app(EnrollmentCreditService::class)
                ->getAvailableCredits($this->event, $this->model);
            $this->cancelRemoval();

            Notification::make()
                ->title(__('Participant removed'))
                ->body(__('The participant has been successfully removed from the event.') .
                    ($creditMessage ?? ''))
                ->success()
                ->send();

            $this->dispatch('reload-table');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->cancelRemoval();
            Notification::make()
                ->title(__('Removal failed'))
                ->body(__('Failed to remove participant: ') . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function confirmRemoval(string $role, string $participantId): void
    {
        $this->participantToRemoveId = $participantId;
        $this->participantToRemoveRole = $role;
        $this->showRemoveConfirmation = true;
    }

    public function cancelRemoval(): void
    {
        $this->reset(['showRemoveConfirmation', 'participantToRemoveId', 'participantToRemoveRole']);
    }
}
