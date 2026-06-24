<?php

namespace App\Livewire\EvtEvents;

use App\Enums\EvtEventEnrollmentRoleEnum;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Actions\CreateStaffEnrollmentAction;
use Domain\EvtEvents\Actions\GetAttributesAndRulesFromRolesAction;
use Domain\EvtEvents\Models\Event;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StaffCreateEnrollment extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public Event $event;

    public ?Federation $federation = null;

    public array $selectedIndividuals = [];

    public array $errorMessages = [];

    public int $currentStep = 1;

    public array $staffAttributes = [];

    public array $attributeValues = [];

    protected $queryString = [
        'page' => ['except' => 1],
    ];

    public ?Entity $entity = null;

    protected function isAdminUser(): bool
    {
        return Auth::user()->group->code === 'ADMIN';
    }

    protected function isEntityUser(): bool
    {
        return Auth::user()->group->code === 'ENTITY';
    }

    public function mount(Event $event): void
    {
        $this->event = $event;

        if ($this->isAdminUser()) {
            $this->federation = Federation::where('is_default_federation', true)->first();

            if (! $this->federation) {
                abort(403, __('events.no_default_federation'));
            }
        } elseif ($this->isEntityUser()) {
            $entity = Auth::user()->entities()->first();
            $this->entity = $entity;

            $isOrganizer = DB::table('evt_organizers')
                ->where('event_id', $event->id)
                ->where('organizable_id', $entity->id)
                ->where('organizable_type', Entity::class)
                ->exists();

            if (! $isOrganizer) {
                abort(403, __('events.entity_not_organizer'));
            }

            $this->federation = $entity->federations()->where('is_default_federation', true)->first()
                ?? $entity->federations()->first();

            if (! $this->federation) {
                abort(403, __('events.entity_no_federation'));
            }
        } else {
            // Get the user's federation using the model to ensure proper type
            $federationId = DB::table('federation_user')
                ->where('user_id', Auth::id())
                ->value('federation_id');

            if ($federationId) {
                $this->federation = Federation::find($federationId);

                // For federation users, only allow access if they're organizers OR default federation
                if ($this->federation && ! $this->federation->is_default_federation) {
                    $isOrganizer = DB::table('evt_organizers')
                        ->where('event_id', $event->id)
                        ->where('organizable_id', $this->federation->id)
                        ->where('organizable_type', Federation::class)
                        ->exists();

                    if (! $isOrganizer) {
                        $this->federation = null;
                        $this->errorMessages[] = __('events.federation_not_organizer');
                    }
                }
            } else {
                $this->federation = null;
            }

            if (! $this->federation) {
                abort(403, __('events.not_authorized_staff_enrollment'));
            }
        }

        $this->loadStaffAttributes();
    }

    protected function loadStaffAttributes(): void
    {
        // Get the event's selected staff attributes
        $eventStaffAttributes = $this->event->staffAttributes()->get();

        // If the event has no staff attributes selected, fall back to the default behavior
        if ($eventStaffAttributes->isEmpty()) {
            $attributesAction = new GetAttributesAndRulesFromRolesAction;
            $attributes = $attributesAction->execute(EvtEventEnrollmentRoleEnum::STAFF->value);
        } else {
            // Use the event's selected staff attributes
            $attributes = $eventStaffAttributes->map(function ($attribute) {
                return [
                    'attribute_data' => $attribute,
                    'fillable_global' => true,
                    'default_value' => null,
                    'rules' => [],
                ];
            })->toArray();
        }

        $this->staffAttributes = collect($attributes)->map(function ($attribute) {
            $attributeModel = $attribute['attribute_data'];
            $options = is_string($attributeModel->attribute_data)
                ? json_decode($attributeModel->attribute_data, true)
                : $attributeModel->attribute_data;

            return [
                'id' => $attributeModel->id,
                'name' => $attributeModel->name,
                'type' => $attributeModel->attribute_type,
                'required' => $attributeModel->required ?? false,
                'options' => $options,
                'attribute_data' => $attributeModel,
                'fillable_global' => $attribute['fillable_global'] ?? false,
                'default_value' => $attribute['default_value'] ?? null,
                'rules' => $attribute['rules'] ?? [],
            ];
        })->toArray();

        $this->initializeAttributeValues();
    }

    private function initializeAttributeValues(): void
    {
        foreach ($this->selectedIndividuals as $individual) {
            if (! isset($this->attributeValues[$individual['id']])) {
                $this->attributeValues[$individual['id']] = [];
            }

            foreach ($this->staffAttributes as $attribute) {
                if (! isset($this->attributeValues[$individual['id']][$attribute['id']])) {
                    $this->attributeValues[$individual['id']][$attribute['id']] =
                        $attribute['attribute_data']['default_value'] ?? null;
                }
            }
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getEligibleIndividualsQuery())
            ->columns([
                TextColumn::make('full_name')->searchable(['name', 'surname'])->label(__('events.name')),
                TextColumn::make('birthdate')->date('d/m/Y')->label(__('events.birthdate')),
                TextColumn::make('gender')
                    ->label(__('events.gender'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'male' => 'M',
                        'female' => 'F',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'male' => 'info',
                        'female' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('member_number')->searchable()->label(__('events.member_number')),
            ])
            ->deferLoading(true)
            ->actions([
                Action::make('toggleSelect')
                    ->iconButton()
                    ->icon(fn ($record) => collect($this->selectedIndividuals)->contains('id', $record->id)
                        ? 'heroicon-s-check-circle'
                        : 'heroicon-o-plus-circle')
                    ->color(fn ($record) => collect($this->selectedIndividuals)->contains('id', $record->id)
                        ? 'success'
                        : 'gray')
                    ->tooltip(fn ($record) => collect($this->selectedIndividuals)->contains('id', $record->id)
                        ? __('events.remove_from_selection')
                        : __('events.add_to_selection'))
                    ->action(fn ($record) => $this->toggleIndividual($record->id)),
            ]);
    }

    protected function getEligibleIndividualsQuery()
    {
        $federationId = $this->federation->id;

        return Individual::whereHas('federations', function ($query) use ($federationId) {
            $query->where('federation_id', $federationId);
        });
    }

    public function toggleIndividual(string $individualId): void
    {
        $existingIndex = collect($this->selectedIndividuals)
            ->search(fn ($item) => $item['id'] == $individualId);

        if ($existingIndex !== false) {
            unset($this->selectedIndividuals[$existingIndex]);
            unset($this->attributeValues[$individualId]);
            $this->selectedIndividuals = array_values($this->selectedIndividuals);
        } else {
            $individual = Individual::find($individualId);
            if ($individual) {
                $this->selectedIndividuals[] = [
                    'id' => $individual->id,
                    'name' => $individual->full_name,
                    'member_number' => $individual->member_number,
                    'birthdate' => $individual->birthdate?->format('d/m/Y'),
                    'gender' => $individual->gender,
                ];
                $this->attributeValues[$individual->id] = [];
            }
        }
    }

    public function removeIndividualFromSelection(string $individualId): void
    {
        $this->selectedIndividuals = array_filter(
            $this->selectedIndividuals,
            fn ($individual) => $individual['id'] != $individualId
        );
        unset($this->attributeValues[$individualId]);
        $this->selectedIndividuals = array_values($this->selectedIndividuals);
    }

    public function submitEnrollment()
    {
        try {
            foreach ($this->selectedIndividuals as $selectedIndividual) {
                $individual = Individual::find($selectedIndividual['id']);
                $attributes = $this->attributeValues[$selectedIndividual['id']] ?? [];

                (new CreateStaffEnrollmentAction)->execute(
                    event: $this->event,
                    federation: $this->federation,
                    individual: $individual,
                    attributes: $attributes
                );
            }

            $redirectRoute = match (true) {
                $this->isAdminUser() => 'admin.evt-events.events.staff-enrollment.index',
                $this->isEntityUser() => 'entity.evt-events.events.staff-enrollment.index',
                default => 'federation.evt-events.events.staff-enrollment.index',
            };

            return redirect()->route($redirectRoute, [
                'event' => $this->event->id,
                'discipline' => null,
            ])->with('success', __('events.staff_enrolled_successfully'));
        } catch (\Exception $exception) {
            $this->errorMessages[] = $exception->getMessage();

            return;
        }
    }

    public function getBackRouteProperty(): string
    {
        $routeName = match (true) {
            $this->isAdminUser() => 'admin.evt-events.events.show',
            $this->isEntityUser() => 'entity.evt-events.events.show',
            default => 'federation.evt-events.events.show',
        };

        return route($routeName, ['event' => $this->event]);
    }

    public function render()
    {
        return view('livewire.evt-events.staff-create-enrollment', [
            'event' => $this->event,
            'enrollmentType' => 'staff',
        ]);
    }
}
