<?php

namespace App\Livewire\EvtEvents;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Enums\EvtEventEnrollmentRoleEnum;
use App\Enums\EvtEventFeeTypeEnum;
use App\Traits\ValidatesEventAttributes;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Actions\CreateAthleteEnrollmentAction;
use Domain\EvtEvents\Actions\CreateCoachEnrollmentAction;
use Domain\EvtEvents\Actions\CreateRefereeEnrollmentAction;
use Domain\EvtEvents\Actions\CreateTeamOfficialEnrollmentAction;
use Domain\EvtEvents\Actions\GetAttributesAndRulesFromDisciplineAction;
use Domain\EvtEvents\Actions\GetAttributesAndRulesFromRolesAction;
use Domain\EvtEvents\Actions\GetDisciplineOutOfRaceAttributeAction;
use Domain\EvtEvents\Actions\GetDisciplinesFromEventAction;
use Domain\EvtEvents\Actions\GetEligibleAthletesAction;
use Domain\EvtEvents\Actions\GetEligibleEntityAthletesAction;
use Domain\EvtEvents\Actions\LoadEventPricingDataAction;
use Domain\EvtEvents\Actions\ValidateAthleteLimitAction;
use Domain\EvtEvents\Actions\ValidateAthleteMaxDisciplinesAction;
use Domain\EvtEvents\Actions\ValidateAttributeRulesAction;
use Domain\EvtEvents\Actions\ValidateOutOfRaceEnrollmentAction;
use Domain\EvtEvents\Actions\ValidateTeamCompositionAction;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Attribute;
use Domain\EvtEvents\Models\CoachEnrollment;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\Models\TeamOfficialEnrollment;
use Domain\EvtEvents\States\ActiveRefereeEnrollmentState;
use Domain\EvtEvents\States\CanceledCoachEnrollmentState;
use Domain\EvtEvents\States\CanceledRefereeEnrollmentState;
use Domain\EvtEvents\States\CanceledTeamOfficialEnrollmentState;
use Domain\EvtEvents\States\RegisteredTeamOfficialEnrollmentState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component; // Add this line

class ManageEnrollment extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    use ValidatesEventAttributes;

    public $page = 1;
    public $event;
    public $model;
    public $enrollmentType;

    public $selectedIndividuals = [];
    public $selectedIndividualsByDiscipline = [];
    public $selectedIndividualIds = [];
    public $disciplines = [];
    public $disciplineModels;
    public $selectedDiscipline;
    public $disciplineAttributes = [];
    public $roleAttributes = [];
    public $disciplineAttributeValues = [];
    public $roleAttributeValues = [];

    public $globalAttributes = [];
    public $globalAttributeValues = [];
    public $individualSearchTerm;
    public $enrollmentSummary = [];
    public $inputFields = [];
    public $totalCost = 0;
    public $showConfirmation = false;
    /** @var \Illuminate\Support\Collection */
    public $pricingData = [];
    public $selectedPricingIds = [
        'perPerson' => null,
        'discipline' => null,
        'eventFee' => null,
    ];

    public $attributeDisciplinesToIndividual = [
        'disciplineIds' => [],
        'attributes' => [],
    ];
    public $displayedIndividuals = [];
    public $selectedRoles = [];
    public $errorMessages = [];
    public $successMessages = [];

    public $currentStep = 1;
    protected $queryString = [
        'page' => ['except' => 1],
    ];
    protected $cachedAttributes = [];
    public $selectedPricingTier = null;
    public $selectedDisciplinePricingId = null;

    public $availableFilters = [];
    /** @var \Illuminate\Support\Collection */
    public $filteredDisciplines;

    public bool $disciplineConfirmed = false;

    public $disciplineFilters = [
        'enrollment_type' => '',
        'gender' => '',
        'style' => '',
        'distance' => '',
    ];

    protected $listeners = [
        'refreshTable' => 'refreshTable',
    ];

    public $preRegisteredCount = 0;
    public $preRegistrationDate = null;
    public $enrolledDisciplines;
    public $individual; // Track current individual
    public $existingAttributes = [];
    public $individuallyEnrolledIds = [];

    // Enrollment context statistics for non-athlete roles
    public $enrollmentContext = [
        'totalEligible' => 0,
        'alreadyEnrolled' => 0,
        'availableToEnroll' => 0,
        'enrolledIndividuals' => [],
    ];

    public function mount(
        Event $event,
        Federation|Entity $model,
        string $enrollmentTypeSlug
    ): void {
        $this->event = $event;
        $this->model = $model;
        $this->enrollmentType = EvtEventEnrollmentRoleEnum::fromSlug($enrollmentTypeSlug)?->value;
        $this->pricingData = collect();
        $this->disciplines = collect(); // Initialize as an empty collection
        $this->filteredDisciplines = collect(); // Initialize as an empty collection

        if (! $this->enrollmentType) {
            abort(404, 'Invalid enrollment type');
        }

        $this->initialize();

        // Call loadPricingData for non-athlete roles
        if ($this->enrollmentType !== EvtEventEnrollmentRoleEnum::ATHLETE->value) {
            $this->loadPricingData();
            // Set selected pricing tier for non-athlete roles
            if ($this->pricingData === 1) {
                $this->selectedPricingTier = $this->pricingData->first()->id;
            }
        }

        // Load role-specific attributes for Coaches and Officials
        if (in_array($this->enrollmentType, [
            EvtEventEnrollmentRoleEnum::COACH->value,
            EvtEventEnrollmentRoleEnum::OFFICIAL->value,
        ], true)) {
            $this->getRoleAttributes();
        }

        $this->preRegisteredCount = Individual::query()
            ->whereHas('athleteEnrollments', function ($query) {
                $query->where('event_id', $this->event->id)
                    ->where($this->model instanceof Entity ? 'entity_id' : 'federation_id', $this->model->id)
                    ->where('status_class', EvtAthleteEnrollmentStatusEnum::PAID->value);
            })->count();

        $this->preRegistrationDate = $this->event->athleteEnrollments()
            ->where($this->model instanceof Entity ? 'entity_id' : 'federation_id', $this->model->id)
            ->latest()
            ->first()
            ?->created_at;
    }

    public function updated($property)
    {
        if (str_starts_with($property, 'disciplineFilters.')) {
            $this->filterDisciplines();
            $this->disciplineConfirmed = false;
            $this->selectedDiscipline = null;
            $this->resetTable();
        }
    }

    public function updatedCurrentStep($value)
    {
        if ($value === 2) {
            if ($this->enrollmentType === EvtEventEnrollmentRoleEnum::ATHLETE->value) {
                $this->getDisciplineAttributes();
            } else {
                $this->getRoleAttributes();
            }
            $this->initializeAttributeValues();
        }
    }

    public function getAvailableDisciplinesProperty()
    {
        return $this->filteredDisciplines->reject(function ($discipline) {
            return $this->enrolledDisciplines->contains('id', $discipline->id);
        });
    }

    private function filterDisciplines()
    {
        $this->filteredDisciplines = $this->disciplines->filter(function ($discipline) {
            // Handle gender filter
            if (! empty($this->disciplineFilters['gender'])) {
                $filterGender = $this->disciplineFilters['gender'];

                // Only show disciplines that exactly match the selected gender
                if ($discipline->gender !== $filterGender) {
                    return false;
                }
            }

            // Handle other filters
            foreach ($this->disciplineFilters as $field => $value) {
                if ($field !== 'gender' && ! empty($value) && $value !== $discipline->$field) {
                    return false;
                }
            }

            return true;
        });
    }

    protected function initialize()
    {
        if ($this->enrollmentType === EvtEventEnrollmentRoleEnum::ATHLETE->value) {

            // Get disciplines and available filters
            $disciplineData = $this->getDisciplinesFromEvent();

            $this->disciplines = collect($disciplineData['disciplines'] ?? []); // Ensure it's a collection
            // Start with showing all disciplines
            $this->filteredDisciplines = $this->disciplines;

            $this->availableFilters = [
                'has_individual' => $this->disciplines->contains('enrollment_type', 'individual'),
                'has_relay' => $this->disciplines->contains('enrollment_type', 'relay'),
                'has_male' => $this->disciplines->contains('gender', 'male'),
                'has_female' => $this->disciplines->contains('gender', 'female'),
                'has_mixed' => $this->disciplines->contains('gender', 'mixed'),
                'styles' => $this->disciplines->pluck('style')->unique()->filter()->values(),
                'distances' => $this->disciplines->pluck('distance')->unique()->filter()->values(),
            ];

            // Initialize selected individuals array for each discipline
            if ($this->disciplines instanceof Collection) {
                // Initialize selected individuals array
                $this->disciplines->each(function ($discipline) {
                    if (! isset($this->selectedIndividualsByDiscipline[$discipline->id])) {
                        $this->selectedIndividualsByDiscipline[$discipline->id] = [];
                    }
                });
            }
        } else {
            // load attributes
            $this->getRoleAttributes();
            $this->selectedRoles = ProfessionalRole::where('role', strtoupper($this->enrollmentType))->get();
            $this->loadPricingData();
            $this->loadEnrollmentContext();
        }

        // Add checks for allowing enrollments
        if ($this->enrollmentType === EvtEventEnrollmentRoleEnum::COACH->value && ! $this->event->allow_coach_enrollment) {
            abort(404, 'Coach enrollment is not allowed for this event.');
        }

        if ($this->enrollmentType === EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL->value && ! $this->event->allow_referee_enrollment) {
            abort(404, 'Referee enrollment is not allowed for this event.');
        }
    }

    public function loadPricingData()
    {
        $loadPricingDataAction = new LoadEventPricingDataAction;
        $result = $loadPricingDataAction->execute(
            $this->event,
            $this->selectedDiscipline ?? null,
            EvtEventEnrollmentRoleEnum::from($this->enrollmentType)
        );

        $this->pricingData = collect($result['pricingData']);
        $this->selectedPricingIds = $this->getSelectedPricingIds($result['selectedPricingIds']);
    }

    /**
     * Load enrollment context statistics for non-athlete roles.
     * This provides information about why the enrollment list may be empty.
     */
    public function loadEnrollmentContext(): void
    {
        if ($this->enrollmentType === EvtEventEnrollmentRoleEnum::ATHLETE->value) {
            return;
        }

        $modelId = $this->model->id;
        $eventId = $this->event->id;

        // Build base query for eligible individuals (active members of this federation/entity)
        $baseQuery = Individual::query()
            ->when($this->model instanceof Federation, function ($query) use ($modelId) {
                $query->whereHas('individualFederations', function (Builder $subQuery) use ($modelId) {
                    $subQuery->where('federation_id', $modelId)
                        ->where('status_class', ActiveIndividualFederationState::class);
                });
            })
            ->when($this->model instanceof Entity, function ($query) use ($modelId) {
                $query->whereHas('individualEntities', function (Builder $subQuery) use ($modelId) {
                    $subQuery->where('entity_id', $modelId)
                        ->where('status_class', ActiveIndividualEntityState::class);
                });
            });

        // Get enrollment model class and relationship based on type
        $enrollmentData = $this->getEnrollmentModelData();
        if (! $enrollmentData) {
            return;
        }

        // Count total eligible (with professional role for referees/technical officials)
        $eligibleQuery = clone $baseQuery;
        if ($this->enrollmentType === EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL->value) {
            $eligibleQuery->whereHas('professionalRoles', function ($subQuery) {
                $subQuery->where('role', 'TECHNICAL_OFFICIAL');
            });
        }
        $totalEligible = $eligibleQuery->count();

        // Get already enrolled individuals
        $enrolledQuery = $enrollmentData['model']::query()
            ->where('event_id', $eventId)
            ->where('status_class', '!=', $enrollmentData['canceledState'])
            ->when($this->model instanceof Federation, fn ($q) => $q->where('federation_id', $modelId))
            ->when($this->model instanceof Entity, fn ($q) => $q->where('entity_id', $modelId))
            ->with('individual:id,name,surname');

        $enrolledIndividuals = $enrolledQuery->get();
        $alreadyEnrolled = $enrolledIndividuals->count();

        // Available to enroll
        $availableToEnroll = $this->getEligibleIndividualsQuery()->count();

        $this->enrollmentContext = [
            'totalEligible' => $totalEligible,
            'alreadyEnrolled' => $alreadyEnrolled,
            'availableToEnroll' => $availableToEnroll,
            'enrolledIndividuals' => $enrolledIndividuals->map(fn ($e) => [
                'id' => $e->individual->id,
                'name' => $e->individual->full_name,
                'status' => class_basename($e->status_class),
            ])->toArray(),
        ];
    }

    /**
     * Get enrollment model data based on enrollment type.
     */
    private function getEnrollmentModelData(): ?array
    {
        return match ($this->enrollmentType) {
            EvtEventEnrollmentRoleEnum::COACH->value => [
                'model' => CoachEnrollment::class,
                'canceledState' => CanceledCoachEnrollmentState::class,
            ],
            EvtEventEnrollmentRoleEnum::OFFICIAL->value => [
                'model' => TeamOfficialEnrollment::class,
                'canceledState' => CanceledTeamOfficialEnrollmentState::class,
            ],
            EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL->value => [
                'model' => RefereeEnrollment::class,
                'canceledState' => CanceledRefereeEnrollmentState::class,
            ],
            default => null,
        };
    }

    private function getSelectedPricingIds(array $pricingIds): array
    {
        $selected = [];

        foreach ($pricingIds as $key => $ids) {
            $selected[$key] = $ids->count() === 1 ? $ids->first() : null;
        }

        return $selected;
    }

    protected function getPricingForDiscipline($disciplineId, $priceType)
    {
        return Pricing::where('event_id', $this->event->id)
            ->where('discipline_id', $disciplineId)
            ->where('price_type', $priceType)
            ->first()?->id;
    }

    public function getExistingAttributesAndValues($individual)
    {
        try {
            // Get existing enrollments for this individual in the event
            $existingEnrollments = AthleteEnrollment::query()
                ->with(['discipline', 'attributes'])
                ->where('event_id', $this->event->id)
                ->where('individual_id', $individual->id)
                ->get();

            $attributeValues = [];

            foreach ($existingEnrollments as $enrollment) {

                // Skip enrollments without a discipline
                if (! $enrollment->discipline_id) {
                    \Log::warning('Found enrollment without discipline', [
                        'enrollment_id' => $enrollment->id,
                        'individual_id' => $individual->id,
                        'event_id' => $this->event->id,
                        'status_class' => $enrollment->status_class,
                        'created_at' => $enrollment->created_at,
                    ]);

                    continue;
                }

                if (! empty($enrollment->attributes)) {
                    try {
                        // Get complete discipline attributes data
                        $disciplineAttributes = (new GetAttributesAndRulesFromDisciplineAction)
                            ->execute($enrollment->discipline_id);

                        // Initialize the structure for this discipline
                        if (! isset($attributeValues[$enrollment->discipline_id])) {
                            $attributeValues[$enrollment->discipline_id] = [
                                'individual' => [],
                                'global' => [],
                            ];
                        }

                        // Map existing attributes with their full definitions
                        foreach ($enrollment->attributes as $attribute) {
                            // Find the full attribute definition
                            $fullAttributeData = collect($disciplineAttributes['attributes'])->first(function ($item) use ($attribute) {
                                return $item['attribute_data']['id'] == $attribute->attribute_id;
                            });

                            if ($fullAttributeData) {
                                $attributeValues[$enrollment->discipline_id]['individual'][] = [
                                    'id' => $attribute->id,
                                    'athlete_enrollment_id' => $attribute->athlete_enrollment_id,
                                    'attribute_id' => $attribute->attribute_id,
                                    'value' => $attribute->value,
                                    'created_at' => $attribute->created_at,
                                    'updated_at' => $attribute->updated_at,
                                    // Add the full attribute definition
                                    'attribute_data' => $fullAttributeData['attribute_data'],
                                    'validation_rules' => $fullAttributeData['validation_rules'] ?? [],
                                    'options' => (array) ($fullAttributeData['attribute_data']['options'] ?? []),
                                ];
                            }
                        }

                        // Handle global attributes if they exist
                        if (! empty($disciplineAttributes['global_attributes'])) {
                            foreach ($enrollment->attributes as $attribute) {
                                $isGlobalAttribute = collect($disciplineAttributes['global_attributes'])
                                    ->contains('id', $attribute->attribute_id);

                                if ($isGlobalAttribute) {
                                    $attributeValues[$enrollment->discipline_id]['global'][] = [
                                        'id' => $attribute->id,
                                        'attribute_id' => $attribute->attribute_id,
                                        'value' => $attribute->value,
                                        'attribute_data' => collect($disciplineAttributes['global_attributes'])
                                            ->firstWhere('id', $attribute->attribute_id),
                                    ];
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error processing enrollment attributes', [
                            'error' => $e->getMessage(),
                            'enrollment_id' => $enrollment->id,
                            'discipline_id' => $enrollment->discipline_id,
                            'individual_id' => $individual->id,
                            'enrollment_data' => $enrollment->toArray(),
                        ]);

                        continue;
                    }
                }
            }

            return $attributeValues;
        } catch (\Exception $e) {
            \Log::error('Error in getExistingAttributesAndValues', [
                'error' => $e->getMessage(),
                'individual_id' => $individual->id,
                'trace' => $e->getTraceAsString(),
                'enrollments_count' => isset($existingEnrollments) ? $existingEnrollments->count() : 'not set',
                'event_id' => $this->event->id,
            ]);

            return [];
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(fn () => match ($this->enrollmentType) {
                EvtEventEnrollmentRoleEnum::ATHLETE->value => null,
                EvtEventEnrollmentRoleEnum::COACH->value => __('events.available_coaches'),
                EvtEventEnrollmentRoleEnum::OFFICIAL->value => __('events.available_team_officials'),
                EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL->value => __('events.available_referees'),
                default => __('events.available_individuals'),
            })
            ->description(fn () => match ($this->enrollmentType) {
                EvtEventEnrollmentRoleEnum::ATHLETE->value => null,
                EvtEventEnrollmentRoleEnum::COACH->value => __('events.select_coaches_to_enroll'),
                EvtEventEnrollmentRoleEnum::OFFICIAL->value => __('events.select_officials_to_enroll'),
                EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL->value => __('events.select_referees_to_enroll'),
                default => __('events.select_individuals_to_proceed'),
            })
            ->query($this->getEligibleIndividualsQuery()->with(['athleteEnrollments.discipline']))
            ->columns($this->tableColumns())
            ->bulkActions($this->tableBulkActions())
            ->actions([
                Action::make('remove')
                    ->label('Reset Attributes')
                    ->color('warning')
                    ->icon('heroicon-m-arrow-path')
                    ->requiresConfirmation()
                    ->modalHeading('Reset Attributes')
                    ->modalDescription(fn (Individual $record) => 'This will remove all attributes associated with ' . $record->full_name .
                        ' and reset the enrollment to its initial state. This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, Reset Attributes')
                    ->action(fn (Individual $record) => $this->resetEnrollmentAttributes($record))
                    ->visible(
                        fn (Individual $record) => $this->currentStep === 1 && (
                            ($this->enrollmentType === EvtEventEnrollmentRoleEnum::COACH->value &&
                                CoachEnrollment::where('event_id', $this->event->id)
                                    ->where('individual_id', $record->id)
                                    ->where($this->model instanceof Federation ? 'federation_id' : 'entity_id', $this->model->id)
                                    ->exists()) ||
                            ($this->enrollmentType === EvtEventEnrollmentRoleEnum::OFFICIAL->value &&
                                TeamOfficialEnrollment::where('event_id', $this->event->id)
                                    ->where('individual_id', $record->id)
                                    ->where($this->model instanceof Federation ? 'federation_id' : 'entity_id', $this->model->id)
                                    ->exists()) ||
                            ($this->enrollmentType === EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL->value &&
                                RefereeEnrollment::where('event_id', $this->event->id)
                                    ->where('individual_id', $record->id)
                                    ->where($this->model instanceof Federation ? 'federation_id' : 'entity_id', $this->model->id)
                                    ->exists())
                        )
                    ),
            ])
            ->filters([]);
    }

    // Update existing attributes from existing enrollment
    public function updateExistingAttributes($disciplineId, $individualId)
    {
        try {

            DB::beginTransaction();

            $this->selectedDiscipline = $disciplineId;
            $enrollment = AthleteEnrollment::with('attributes')
                ->where([
                    'event_id' => $this->event->id,
                    'individual_id' => $individualId,
                    'discipline_id' => $disciplineId,
                ])->firstOrFail();

            // Get the current attribute values from the form
            $currentAttributes = $this->existingAttributes[$disciplineId]['individual'] ?? [];

            // Update or create attributes
            foreach ($currentAttributes as $attributeId => $value) {
                $enrollmentAttribute = $enrollment->attributes()
                    ->where('attribute_id', $attributeId)
                    ->first();

                if ($enrollmentAttribute) {
                    $enrollmentAttribute->update([
                        'value' => $value,
                    ]);
                } else {
                    $enrollment->attributes()->create([
                        'attribute_id' => $attributeId,
                        'value' => $value,
                    ]);
                }
            }

            DB::commit();

            // Refresh the attributes
            $individual = Individual::with('athleteEnrollments')->findOrFail($individualId);
            $this->existingAttributes = $this->getExistingAttributesAndValues($individual);

            $this->dispatch('attributes-updated');
            Notification::make()
                ->title('Attributes updated successfully')
                ->success()
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessages[] = 'Error updating attributes: ' . $e->getMessage();
            \Log::error('Attribute update error', [
                'error' => $e->getMessage(),
                'discipline_id' => $disciplineId,
                'individual_id' => $individualId,
                'attributes' => $attributeValues ?? [],
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function removeDiscipline($disciplineId, $individualId)
    {
        if (! $individualId) {
            $this->errorMessages[] = 'Unable to remove discipline: Individual not found';

            return;
        }
        // Handle removal of existing discipline
        try {
            DB::beginTransaction();

            $enrollment = AthleteEnrollment::with('attributes')  // Eager load attributes
                ->where([
                    'event_id' => $this->event->id,
                    'individual_id' => $individualId,
                    'discipline_id' => $disciplineId,
                ])->first();

            if (! $enrollment) {
                throw new \Exception('Enrollment not found');
            }
            // First delete all related attributes
            $enrollment->attributes()->delete();

            // Then delete the enrollment itself
            $enrollment->delete();

            // Refresh enrolled disciplines
            $individual = Individual::findOrFail($individualId);
            $this->enrolledDisciplines = (new GetAthleteEnrolledDisciplinesFromEvent)
                ->execute($this->event, $individual);

            DB::commit();

            Notification::make()
                ->title('Discipline removed successfully')
                ->success()
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessages[] = 'Error removing discipline: ' . $e->getMessage();
            \Log::error('Discipline removal error', [
                'error' => $e->getMessage(),
                'individual_id' => $individualId,
                'discipline_id' => $disciplineId,
            ]);
        }
    }

    public function removeNewDiscipline($disciplineId)
    {
        // Remove from selected disciplines array
        $this->attributeDisciplinesToIndividual['disciplineIds'] = array_values(
            array_filter(
                $this->attributeDisciplinesToIndividual['disciplineIds'],
                fn ($id) => $id != $disciplineId
            )
        );

        // Remove associated attributes
        unset($this->attributeDisciplinesToIndividual['attributes'][$disciplineId]);
    }

    protected function tableColumns()
    {
        $columns = [
            TextColumn::make('full_name')
                ->searchable(['name', 'surname'])
                ->label(__('events.name'))
                ->html()
                ->getStateUsing(function ($record) {
                    $nameHtml = view('livewire.evt-events.components.name-with-member-code', [
                        'name' => $record?->full_name ?? 'Unknown',
                        'memberCode' => $record?->member_code,
                    ])->render();

                    if ($this->enrollmentType === EvtEventEnrollmentRoleEnum::ATHLETE->value) {
                        if ($this->isIndividuallyEnrolled($record->id)) {
                            $nameHtml .= '<div class="mt-1"><span class="px-2 py-0.5 text-xs font-medium rounded-full bg-purple-100 text-purple-800">Enrolled by: Individual</span></div>';
                        }
                    }

                    return $nameHtml;
                }),
            TextColumn::make('birthdate')
                ->label(__('events.birthdate'))
                ->date('d/m/Y'),
            TextColumn::make('member_number')
                ->label(__('events.member_number'))
                ->searchable(),
            TextColumn::make('gender')
                ->label(__('events.gender'))
                ->badge()
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'male' => __('events.male'),
                    'female' => __('events.female'),
                    default => $state,
                })
                ->color(fn (string $state): string => match ($state) {
                    'male' => 'info',
                    'female' => 'success',
                    default => 'gray',
                }),
        ];

        if ($this->enrollmentType === EvtEventEnrollmentRoleEnum::ATHLETE->value) {
            $columns[] = TextColumn::make('id')
                ->label(__('events.enrolled_disciplines'))
                ->html()
                ->formatStateUsing(function ($record) {
                    // Use Eloquent instead of raw SQL for better accuracy
                    $enrollments = AthleteEnrollment::where('event_id', $this->event->id)
                        ->where('individual_id', $record->id)
                        ->whereIn('status_class', [
                            EvtAthleteEnrollmentStatusEnum::PAID->value,
                            EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value,
                            EvtAthleteEnrollmentStatusEnum::COMPLETED->value,
                            EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value,
                        ])  // Include pending_payment status
                        ->with('discipline')
                        ->get();

                    if ($enrollments->isEmpty()) {
                        return '<span class="text-gray-500 text-xs italic">Not enrolled</span>';
                    }

                    // Filter to only include enrollments with a discipline
                    $disciplineEnrollments = $enrollments->filter(function ($enrollment) {
                        return $enrollment->discipline_id && $enrollment->discipline;
                    });

                    if ($disciplineEnrollments->isEmpty()) {
                        return '<span class="text-gray-500 text-xs italic">No disciplines assigned</span>';
                    }

                    $html = '';
                    foreach ($disciplineEnrollments as $enrollment) {
                        $html .= "<span class=\"block items-center px-2 py-0.5 my-2 rounded text-xs font-medium bg-blue-100 text-blue-800\">{$enrollment->discipline->name}</span>";
                    }

                    return $html;
                });
        } elseif (in_array($this->enrollmentType, [
            EvtEventEnrollmentRoleEnum::COACH->value,
            EvtEventEnrollmentRoleEnum::OFFICIAL->value,
            EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL->value,
        ])) {
            // Add enrollment status and attributes status column for coaches, referees, and team officials
            $columns[] = TextColumn::make('id')
                ->label('Attributes Status')
                ->html()
                ->formatStateUsing(function ($record) {
                    $enrollment = null;
                    $hasAttributes = false;

                    if ($this->enrollmentType === EvtEventEnrollmentRoleEnum::COACH->value) {
                        $enrollment = CoachEnrollment::where('event_id', $this->event->id)
                            ->where('individual_id', $record->id)
                            ->where($this->model instanceof Federation ? 'federation_id' : 'entity_id', $this->model->id)
                            ->first();
                    } elseif ($this->enrollmentType === EvtEventEnrollmentRoleEnum::OFFICIAL->value) {
                        $enrollment = TeamOfficialEnrollment::where('event_id', $this->event->id)
                            ->where('individual_id', $record->id)
                            ->where($this->model instanceof Federation ? 'federation_id' : 'entity_id', $this->model->id)
                            ->first();
                    } elseif ($this->enrollmentType === EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL->value) {
                        $enrollment = RefereeEnrollment::where('event_id', $this->event->id)
                            ->where('individual_id', $record->id)
                            ->where($this->model instanceof Federation ? 'federation_id' : 'entity_id', $this->model->id)
                            ->first();
                    }

                    if (! $enrollment) {
                        return '<span class="text-gray-500 text-xs italic">Not enrolled</span>';
                    }

                    // Check if the enrollment has attributes
                    $hasAttributes = $enrollment->attributes()->exists();

                    if ($hasAttributes) {
                        return '<span class="block items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Attributes filled</span>';
                    } else {
                        return '<span class="block items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Attributes required</span>';
                    }
                });
        }

        return $columns;
    }

    protected function tableBulkActions()
    {
        return [
            BulkAction::make('select')
                ->label($this->enrollmentType === EvtEventEnrollmentRoleEnum::ATHLETE->value
                    ? __('events.continue_to_register')
                    : __('events.enroll_selected'))
                ->color('warning')
                ->action(function (Collection $records) {
                    // Clear old errors so they don't accumulate
                    $this->errorMessages = [];

                    // Try updating selected individuals
                    $this->updateSelectedIndividuals($records);

                    // If errors occurred OR no discipline is selected,
                    // do NOT advance to Step 2
                    if (! empty($this->errorMessages)) {
                        // Stay on Step 1 so the user can see the error message
                        $this->currentStep = 1;

                        return;
                    }

                    // For non-athlete types, check if there are any attributes to fill
                    if ($this->enrollmentType !== EvtEventEnrollmentRoleEnum::ATHLETE->value) {
                        $this->getAttributesByRole();

                        // If no attributes to fill, skip step 2 and submit directly
                        if (empty($this->roleAttributes) && empty($this->globalAttributes)) {
                            $this->submitEnrollment();

                            return;
                        }
                    }

                    // Otherwise, move on to step 2
                    $this->currentStep = 2;
                })
                ->deselectRecordsAfterCompletion()
                ->requiresConfirmation(false),
        ];
    }

    public function updatedDisciplineFilters()
    {
        // Start with all disciplines
        $filtered = $this->disciplines;

        // Apply each non-empty filter
        foreach ($this->disciplineFilters as $field => $value) {
            if (! empty($value)) {
                $filtered = $filtered->filter(function ($discipline) use ($field, $value) {
                    // Convert both values to strings and compare them case-insensitively
                    $disciplineValue = (string) $discipline->{$field};
                    $filterValue = (string) $value;

                    return strcasecmp($disciplineValue, $filterValue) === 0;
                });
            }
        }

        $this->filteredDisciplines = $filtered;
    }

    public function getDisciplinesFromEvent()
    {
        return (new GetDisciplinesFromEventAction)->execute($this->event);
    }

    public function getDisciplineAttributes()
    {
        if (
            empty($this->selectedDiscipline) ||
            $this->enrollmentType !== EvtEventEnrollmentRoleEnum::ATHLETE->value
        ) {
            $this->resetAttributes();

            return;
        }

        $cacheKey = sprintf('discipline_%d_attributes', $this->selectedDiscipline);

        if (isset($this->attributeCache[$cacheKey])) {
            // Restore from cache
            $cached = $this->attributeCache[$cacheKey];
            $this->disciplineAttributes = $cached['discipline'] ?? [];
            $this->globalAttributes = $cached['global'] ?? [];

            return;
        }

        try {
            // Reset attributes before setting new ones
            $this->resetAttributes();
            $getAttributesAction = new GetAttributesAndRulesFromDisciplineAction;
            $result = $getAttributesAction->execute($this->selectedDiscipline);

            // Validate result structure
            if (! isset($result['attributes'])) {
                \Log::warning('Invalid attribute structure received', [
                    'discipline_id' => $this->selectedDiscipline,
                    'result' => $result,
                ]);

                return;
            }

            // Process attributes with validation
            foreach ($result['attributes'] as $attribute) {
                if (! isset($attribute['attribute_data'])) {
                    \Log::warning('Invalid attribute format', ['attribute' => $attribute]);

                    continue;
                }
                $this->disciplineAttributes[] = $attribute;
            }

            // Only set global attributes if properly structured
            if (! empty($result['global_attributes'])) {
                $this->globalAttributes = $result['global_attributes'];
            } else {
                // Ensure globals are empty if none exist for this discipline
                $this->globalAttributes = [];
                $this->globalAttributeValues = [];
            }

            // Initialize attribute values for existing selections
            foreach ($this->selectedIndividuals as $individual) {
                $individualId = is_array($individual) ? $individual['id'] : $individual->id;

                // Initialize discipline attributes
                if (! empty($this->disciplineAttributes)) {
                    if (! isset($this->disciplineAttributeValues[$individualId])) {
                        $this->disciplineAttributeValues[$individualId] = [];
                    }

                    foreach ($this->disciplineAttributes as $attribute) {
                        if (! isset($this->disciplineAttributeValues[$individualId][$attribute['attribute_data']['id']])) {
                            $this->disciplineAttributeValues[$individualId][$attribute['attribute_data']['id']]
                                = $attribute['attribute_data']['default_value'] ?? null;
                        }
                    }
                }
            }

            // Initialize global attributes (outside the athlete loop)
            if (! empty($this->globalAttributes)) {
                if (! isset($this->globalAttributeValues)) {
                    $this->globalAttributeValues = [];
                }

                foreach ($this->globalAttributes as $attribute) {
                    if (! isset($this->globalAttributeValues[$attribute['attribute_data']['id']])) {
                        $this->globalAttributeValues[$attribute['attribute_data']['id']]
                            = $attribute['attribute_data']['default_value'] ?? null;
                    }
                }
            }

            // Cache the result
            $this->cachedAttributes[$cacheKey] = [
                'disciplineAttributes' => $this->disciplineAttributes,
                'globalAttributes' => $this->globalAttributes,
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting discipline attributes', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getRoleAttributes(): void
    {
        if ($this->enrollmentType === EvtEventEnrollmentRoleEnum::ATHLETE->value) {
            $this->getDisciplineAttributes();
        } else {
            $this->getAttributesByRole();
        }
    }

    public function getAttributesByRole()
    {
        // Pass the event ID so it can load event-specific attributes
        $attributes = (new GetAttributesAndRulesFromRolesAction)->execute($this->enrollmentType, $this->event->id);

        $this->globalAttributes = array_filter($attributes, fn ($attribute) => $attribute['attribute_data']['fillable_global']);
        $this->roleAttributes = array_filter($attributes, fn ($attribute) => ! $attribute['attribute_data']['fillable_global']);
    }

    private function initializeAttributeValues()
    {
        foreach ($this->selectedIndividuals as $individual) {

            // Discipline specific attributes
            if (! empty($this->disciplineAttributes)) {
                foreach ($this->disciplineAttributes as $attribute) {
                    if (! isset($this->disciplineAttributeValues[$individual['id']][$attribute['attribute_data']['id']])) {
                        // Set default value for OUTOFRACE attributes
                        if (($attribute['attribute_data']['type'] ?? '') === 'OUTOFRACE') {
                            $this->disciplineAttributeValues[$individual['id']][$attribute['attribute_data']['id']] = 'no';
                        } else {
                            $this->disciplineAttributeValues[$individual['id']][$attribute['attribute_data']['id']] = $attribute['attribute_data']['default_value'] ?? null;
                        }
                    }
                }
            }
            // Every other role attributes
            if (! empty($this->roleAttributes)) {
                foreach ($this->roleAttributes as $attribute) {
                    if (! isset($this->roleAttributeValues[$individual['id']][$attribute['attribute_data']['id']])) {
                        // Set default value for OUTOFRACE attributes
                        if (($attribute['attribute_data']['type'] ?? '') === 'OUTOFRACE') {
                            $this->roleAttributeValues[$individual['id']][$attribute['attribute_data']['id']] = 'no';
                        } else {
                            $this->roleAttributeValues[$individual['id']][$attribute['attribute_data']['id']] = $attribute['attribute_data']['default_value'] ?? null;
                        }
                    }
                }
            }
        }
    }

    public function getEligibleIndividualsQuery()
    {
        if ($this->enrollmentType === EvtEventEnrollmentRoleEnum::ATHLETE->value) {
            return $this->getAllEligibleAthletes();
        }

        $modelId = $this->model->id;
        $eventId = $this->event->id;

        $query = Individual::query()
            ->when($this->model instanceof Federation, function ($query) use ($modelId) {
                $query->whereHas('individualFederations', function (Builder $subQuery) use ($modelId) {
                    $subQuery->where('federation_id', $modelId)
                        ->where('status_class', ActiveIndividualFederationState::class);
                });
            })
            ->when($this->model instanceof Entity, function ($query) use ($modelId) {
                $query->whereHas('individualEntities', function (Builder $subQuery) use ($modelId) {
                    $subQuery->where('entity_id', $modelId)
                        ->where('status_class', ActiveIndividualEntityState::class);
                });
            });

        if ($this->enrollmentType === EvtEventEnrollmentRoleEnum::OFFICIAL->value) {
            // Show active members who haven't been enrolled as officials yet
            return $query
                ->whereDoesntHave('officialsEnrollments', function (Builder $subQuery) use ($eventId, $modelId) {
                    $subQuery->where('event_id', $eventId)
                        ->where('status_class', '!=', CanceledTeamOfficialEnrollmentState::class)
                        ->when($this->model instanceof Federation, function ($q) use ($modelId) {
                            $q->where('federation_id', $modelId);
                        })
                        ->when($this->model instanceof Entity, function ($q) use ($modelId) {
                            $q->where('entity_id', $modelId);
                        });
                });
        }

        if ($this->enrollmentType === EvtEventEnrollmentRoleEnum::COACH->value) {
            // Show active members who haven't been enrolled as coaches yet
            return $query
                ->whereDoesntHave('coachEnrollments', function (Builder $subQuery) use ($eventId, $modelId) {
                    $subQuery->where('event_id', $eventId)
                        ->where('status_class', '!=', CanceledCoachEnrollmentState::class)
                        ->when($this->model instanceof Federation, function ($q) use ($modelId) {
                            $q->where('federation_id', $modelId);
                        })
                        ->when($this->model instanceof Entity, function ($q) use ($modelId) {
                            $q->where('entity_id', $modelId);
                        });
                });
        }

        if ($this->enrollmentType === EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL->value) {
            // Show individuals with TECHNICAL_OFFICIAL professional role who haven't been enrolled yet
            return $query
                ->whereHas('professionalRoles', function ($subQuery) {
                    $subQuery->where('role', 'TECHNICAL_OFFICIAL');
                })
                ->whereDoesntHave('refereeEnrollments', function (Builder $subQuery) use ($eventId, $modelId) {
                    $subQuery->where('event_id', $eventId)
                        ->where('status_class', '!=', CanceledRefereeEnrollmentState::class)
                        ->when($this->model instanceof Federation, function ($q) use ($modelId) {
                            $q->where('federation_id', $modelId);
                        })
                        ->when($this->model instanceof Entity, function ($q) use ($modelId) {
                            $q->where('entity_id', $modelId);
                        });
                });
        }

        return $query;
    }

    public function getAllEligibleAthletes()
    {
        $this->errorMessages = [];

        // Get IDs of individually enrolled athletes first
        $this->individuallyEnrolledIds = $this->getIndividuallyEnrolledAthleteIds();

        $disciplineId = $this->selectedDiscipline ? (int) $this->selectedDiscipline : null;

        // Delegate to domain actions which apply all competition eligibility filters
        // (required documents, required licenses, local federation, entity sport registration,
        // discipline gender/age/license requirements)
        if ($this->model instanceof Federation) {
            $baseQuery = app(GetEligibleAthletesAction::class)->execute(
                $this->event->id,
                $this->model->id,
                $disciplineId
            );
        } else {
            $baseQuery = app(GetEligibleEntityAthletesAction::class)->execute(
                $this->event->id,
                $this->model->id,
                $disciplineId
            );
        }

        // Include individually enrolled athletes (they bypass eligibility filters)
        if (! empty($this->individuallyEnrolledIds)) {
            $baseQuery->orWhereIn('id', $this->individuallyEnrolledIds);
        }

        // Eager load athlete enrollments for display
        $baseQuery->with(['athleteEnrollments' => function ($query) {
            $query->where('event_id', $this->event->id)
                ->whereIn('status_class', [
                    EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value,
                    EvtAthleteEnrollmentStatusEnum::PAID->value,
                    EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value,
                    EvtAthleteEnrollmentStatusEnum::COMPLETED->value,
                ])
                ->with('discipline:id,name');
        }]);

        if ($disciplineId && $baseQuery->count() === 0) {
            $this->errorMessages[] = __('events.no_preregistered_athletes_for_discipline');
        }

        return $baseQuery;
    }

    /**
     * Check if an athlete is individually enrolled
     */
    protected function isIndividuallyEnrolled($athleteId): bool
    {
        return in_array($athleteId, $this->individuallyEnrolledIds ?? []);
    }

    protected function getIndividuallyEnrolledAthleteIds()
    {
        if ($this->model instanceof Federation) {
            return Individual::query()
                ->whereHas('individualFederations', function ($query) {
                    $query->where('federation_id', $this->model->id)
                        ->where('status_class', ActiveIndividualFederationState::class);
                })
                ->whereHas('athleteEnrollments', function ($query) {
                    $query->where('event_id', $this->event->id)
                        ->whereNull('federation_id')
                        ->whereIn('status_class', [
                            EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value,
                            EvtAthleteEnrollmentStatusEnum::PAID->value,
                            EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value,
                            EvtAthleteEnrollmentStatusEnum::COMPLETED->value,
                        ]);
                })
                ->pluck('id')
                ->toArray();
        }

        // Entity case
        return Individual::query()
            ->whereHas('individualEntities', function ($query) {
                $query->where('entity_id', $this->model->id)
                    ->where('status_class', ActiveIndividualEntityState::class);
            })
            ->whereHas('athleteEnrollments', function ($query) {
                $query->where('event_id', $this->event->id)
                    ->whereNull('entity_id')
                    ->whereIn('status_class', [
                        EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value,
                        EvtAthleteEnrollmentStatusEnum::PAID->value,
                        EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value,
                        EvtAthleteEnrollmentStatusEnum::COMPLETED->value,
                    ]);
            })
            ->pluck('id')
            ->toArray();
    }

    public function removeIndividualFromSelection($individualId)
    {

        $this->selectedIndividuals = array_filter(
            $this->selectedIndividuals,
            fn ($individual) => $individual['id'] != $individualId
        );
        $this->selectedIndividuals = array_values($this->selectedIndividuals);

        $this->selectedIndividualsByDiscipline[$this->selectedDiscipline] = array_filter(
            $this->selectedIndividualsByDiscipline[$this->selectedDiscipline],
            fn ($individual) => $individual['id'] != $individualId
        );
        $this->selectedIndividualsByDiscipline[$this->selectedDiscipline] = array_values($this->selectedIndividualsByDiscipline[$this->selectedDiscipline]);
    }

    public function updateSelectedIndividuals(Collection $individuals): void
    {
        // If the enrollment type is Athlete and there's no selected discipline, do not proceed
        if ($this->enrollmentType === EvtEventEnrollmentRoleEnum::ATHLETE->value && ! $this->selectedDiscipline) {
            $this->errorMessages[] = __('You must select a discipline before adding athletes.');

            return;
        }

        $this->errorMessages = [];
        $currentSelectedIds = collect($this->selectedIndividuals)->pluck('id')->all();
        $newSelectedIds = $individuals->pluck('id')->all();

        $individualsToAdd = array_diff($newSelectedIds, $currentSelectedIds);
        $individualsToRemove = array_diff($currentSelectedIds, $newSelectedIds);

        $discipline = $this->enrollmentType === EvtEventEnrollmentRoleEnum::ATHLETE->value
            ? Discipline::find($this->selectedDiscipline)
            : null;

        $enrollable = $this->model;

        // Create a new array instead of modifying the existing one
        $tempSelectedIndividuals = collect($this->selectedIndividuals)->toArray();
        // Get current attributes
        $currentAttributes = collect($this->selectedIndividuals)
            ->pluck('attributes')
            ->flatten()
            ->toArray();

        if (
            $this->handleAddIndividuals($individualsToAdd, $individuals, $currentAttributes, $tempSelectedIndividuals) &&
            ($this->enrollmentType !== EvtEventEnrollmentRoleEnum::ATHLETE->value || $this->handleValidation($discipline, $tempSelectedIndividuals, $enrollable))
        ) {
            // If validation passes, apply changes
            $this->applySelectedIndividualsChanges($individualsToAdd, $individuals, $tempSelectedIndividuals);
            $this->handleRemovals($individualsToRemove);

            // Load attributes based on enrollment type
            if ($this->enrollmentType === EvtEventEnrollmentRoleEnum::ATHLETE->value) {
                $this->getDisciplineAttributes();
            } else {
                $this->getRoleAttributes();
                // Load existing attribute values for non-athlete roles
                foreach ($tempSelectedIndividuals as $individual) {
                    $existingEnrollment = null;
                    if ($this->enrollmentType === EvtEventEnrollmentRoleEnum::COACH->value) {
                        $existingEnrollment = CoachEnrollment::where('event_id', $this->event->id)
                            ->where('individual_id', $individual['id'])
                            ->where($this->model instanceof Federation ? 'federation_id' : 'entity_id', $this->model->id)
                            ->first();
                    } elseif ($this->enrollmentType === EvtEventEnrollmentRoleEnum::OFFICIAL->value) {
                        $existingEnrollment = TeamOfficialEnrollment::where('event_id', $this->event->id)
                            ->where('individual_id', $individual['id'])
                            ->where($this->model instanceof Federation ? 'federation_id' : 'entity_id', $this->model->id)
                            ->first();
                    } elseif ($this->enrollmentType === EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL->value) {
                        $existingEnrollment = RefereeEnrollment::where('event_id', $this->event->id)
                            ->where('individual_id', $individual['id'])
                            ->where($this->model instanceof Federation ? 'federation_id' : 'entity_id', $this->model->id)
                            ->first();
                    }

                    if ($existingEnrollment && $existingEnrollment->attributes) {
                        foreach ($existingEnrollment->attributes as $attribute) {
                            $this->roleAttributeValues[$individual['id']][$attribute->attribute_id] = $attribute->value;
                        }
                    }
                }
            }

            $this->initializeAttributeValues();
        }
    }

    protected function handleAddIndividuals($individualsToAdd, $individuals, $currentAttributes, &$tempSelectedIndividuals): bool
    {
        foreach ($individualsToAdd as $individualId) {
            $individual = $individuals->firstWhere('id', $individualId);
            if ($individual) {
                $tempSelectedIndividuals[] = [
                    'id' => $individual->id,
                    'name' => $individual->full_name,
                    'member_code' => $individual->member_code,
                    'gender' => $individual->gender,
                    'birthdate' => $individual->birthdate,
                ];

                $attributeValues = $this->disciplineAttributeValues[$individual->id] ?? [];

                if (! (new ValidateAttributeRulesAction)->validateAttributes($attributeValues, $currentAttributes)) {
                    $this->errorMessages[] = 'Validation failed for one or more attributes.';

                    return false;
                }
            }
        }

        return true;
    }

    protected function handleValidation($discipline, $tempSelectedIndividuals, $enrollable): bool
    {
        if ($this->enrollmentType === EvtEventEnrollmentRoleEnum::ATHLETE->value) {

            // Check if discipline has out-of-race attribute
            $hasOutOfRace = app(GetDisciplineOutOfRaceAttributeAction::class)
                ->hasOutOfRaceAttribute($discipline);

            if ($hasOutOfRace) {
                // Add warning message but allow to proceed
                Notification::make()
                    ->title('Important Note')
                    ->body(__('This discipline has out-of-race conditions. Final athlete limit validation will occur after attribute selection.'))
                    ->warning()
                    ->send();

                return true;
            }

            // Validate athlete limit
            if (
                ! empty($discipline) &&
                ! (new ValidateAthleteLimitAction)->execute($discipline, $this->event->id, $enrollable, $tempSelectedIndividuals) &&
                ! $hasOutOfRace
            ) {
                $this->errorMessages[] = "The number of selected individuals exceeds the limit of {$discipline->athlete_limit} for this discipline.";

                return false;
            }

            // Validate team composition requirements
            if (
                ! empty($discipline) &&
                $discipline->enrollment_type !== 'individual' &&
                ! empty($discipline->team_composition_requirements) &&
                ! (new ValidateTeamCompositionAction)->execute($discipline, $tempSelectedIndividuals, $this->errorMessages)
            ) {
                return false;
            }

            // Validate total athlete limit again
            if (
                ! empty($discipline) &&
                $discipline->athlete_limit &&
                count($tempSelectedIndividuals) > $discipline->athlete_limit &&
                ! $hasOutOfRace
            ) {
                $this->errorMessages[] = "The number of selected individuals exceeds the limit of {$discipline->athlete_limit} for this discipline.";

                return false;
            }
        }

        return true;
    }

    protected function applySelectedIndividualsChanges(
        $individualsToAdd,
        $individuals,
        $tempSelectedIndividuals
    ): void {
        $this->selectedIndividuals = $tempSelectedIndividuals;

        foreach ($individualsToAdd as $individualId) {
            $individual = $individuals->firstWhere('id', $individualId);

            if ($individual) {
                // Initialize attribute values
                if (! empty($this->disciplineAttributes)) {
                    if (! isset($this->disciplineAttributeValues[$individual->id])) {
                        $this->disciplineAttributeValues[$individual->id] = [];

                        // Set default values for each attribute
                        foreach ($this->disciplineAttributes as $attribute) {
                            $this->disciplineAttributeValues[$individual->id][$attribute['attribute_data']['id']]
                                = $attribute['attribute_data']['default_value'] ?? null;
                        }
                    }
                }
            }
        }

        // Ensure we have the latest attributes
        $this->getDisciplineAttributes();
        $this->currentStep = 2;
    }

    protected function handleRemovals($individualsToRemove): void
    {
        foreach ($individualsToRemove as $individualId) {
            $this->removeIndividualFromSelection($individualId);
        }
    }

    public function updatedSelectedDiscipline($value): void
    {
        // Clear any existing error messages
        $this->errorMessages = [];

        // Force complete state reset before changing discipline
        $this->resetFormState();
        $this->resetAttributes();

        // Set new discipline
        $this->selectedDiscipline = $value;

        // Reset confirmation - user must confirm the new discipline
        $this->disciplineConfirmed = false;

        // Don't load pricing/attributes until confirmed (handled by confirmDisciplineSelection)
        $this->resetTable();
        $this->resetPage();
    }

    public function confirmDisciplineSelection(): void
    {
        if (! $this->selectedDiscipline) {
            return;
        }

        $this->disciplineConfirmed = true;
        $this->loadPricingData();
        $this->getDisciplineAttributes();
        $this->resetTable();
    }

    protected function createCoachEnrollment(
        $selectedIndividual,
        $enrollment,
        $attributes
    ): void {
        try {
            (new CreateCoachEnrollmentAction)->execute(
                $this->event,
                $this->model,
                $selectedIndividual['id'],
                $enrollment,
                $this->selectedPricingTier,
                $attributes
            );
        } catch (ValidationException $e) {
            $this->errorMessages[] = $e->getMessage();
        }
    }

    protected function createRefereeEnrollment(
        $selectedIndividual,
        $enrollment,
        $mergedAttributes
    ): void {
        try {
            (new CreateRefereeEnrollmentAction)->execute(
                $this->event,
                $this->model,
                $selectedIndividual['id'],
                $enrollment,
                $this->selectedPricingTier,
                $mergedAttributes
            );
        } catch (ValidationException $e) {
            $this->errorMessages[] = $e->getMessage();
        }
    }

    protected function createOfficialEnrollment(
        $selectedIndividual,
        $enrollment,
        $mergedAttributes
    ): void {
        try {
            (new CreateTeamOfficialEnrollmentAction)->execute(
                $this->event,
                $this->model,
                $selectedIndividual['id'],
                $enrollment,
                $this->selectedPricingTier,
                $mergedAttributes
            );
        } catch (ValidationException $e) {
            $this->errorMessages[] = $e->getMessage();
        }
    }

    public function refreshTable(): void
    {
        $this->table->query($this->getEligibleIndividualsQuery());
    }

    public function goToStep($step): void
    {
        $this->currentStep = $step;
    }

    public function submitEnrollment(): void
    {
        // Reset error messages at start
        $this->errorMessages = [];

        $discipline = null;
        if ($this->enrollmentType === EvtEventEnrollmentRoleEnum::ATHLETE->value) {
            $discipline = $this->filteredDisciplines
                ->where('id', $this->selectedDiscipline)
                ->first();

            if (! $discipline) {
                $this->showErrorNotification('Invalid discipline selected.');

                return;
            }

            // Get discipline attributes and rules
            $disciplineAttributes = (new GetAttributesAndRulesFromDisciplineAction)->execute($this->selectedDiscipline);

            try {
                // Validate attributes for each selected individual
                foreach ($this->selectedIndividuals as $individual) {
                    $attributeValues = $this->getAttributeValues($individual['id']);
                    $processedValues = $this->processAttributeValues($attributeValues);
                    $this->validateAttributesAndRules($processedValues, $disciplineAttributes);
                }
            } catch (ValidationException $e) {
                $this->showErrorNotification($e->getMessage());

                return;
            }

            // First, check if discipline has out-of-race attribute
            $hasOutOfRace = app(GetDisciplineOutOfRaceAttributeAction::class)
                ->hasOutOfRaceAttribute($discipline);

            if ($hasOutOfRace && ! $this->validateOutOfRace($discipline)) {
                $this->showErrorNotification();

                return;
            }

            // Validate athlete limit
            if (! $this->enforceAthleteLimit($discipline)) {
                return;
            }

            // Team composition validation for relay/team
            if (! $this->enforceTeamCompositionLimit($discipline)) {
                return;
            }

            // Validate max disciplines per athlete (only for in-race athletes)
            if (! $this->enforceMaxDisciplines($discipline, $hasOutOfRace)) {
                return;
            }
        }

        try {
            DB::beginTransaction();

            $manageCreateEnrollment = $this->manageCreateEnrollment($discipline);

            DB::commit();

            $this->resetFormState();
            $this->currentStep = 1;

            $message = $this->getEnrollmentSuccessMessage($manageCreateEnrollment);

            Notification::make()
                ->title('Success')
                ->body($message)
                ->success()
                ->send();

            // Redirect back to enrollment page after successful save
            $enrollmentRoute = $this->model instanceof Federation
                ? route('federation.evt-events.events.enrollments.create', [
                    'event' => $this->event,
                    'type' => EvtEventEnrollmentRoleEnum::toSlug($this->enrollmentType),
                ])
                : route('entity.evt-events.events.enrollments.create', [
                    'event' => $this->event,
                    'type' => EvtEventEnrollmentRoleEnum::toSlug($this->enrollmentType),
                ]);

            $this->redirect($enrollmentRoute);
        } catch (\Exception $e) {
            DB::rollBack();

            $errorMessage = empty($this->errorMessages)
                ? 'Error enrolling athletes: ' . $e->getMessage()
                : implode("\n", $this->errorMessages);

            Notification::make()
                ->title('Error')
                ->body($errorMessage)
                ->danger()
                ->send();
        }
    }

    private function showErrorNotification(?string $message = null): void
    {
        // If none provided, use what's in $errorMessages
        if (! $message) {
            $message = implode("\n", $this->errorMessages);
        } else {
            // Push into the array so it's always visible in the persistent UI
            $this->errorMessages[] = $message;
        }

        Notification::make()
            ->title(__('Validation Error'))
            ->body($message)
            ->danger()
            ->send();
    }

    private function enforceAthleteLimit(Discipline $discipline): bool
    {
        // Skip validation if athlete_limit is null or 0 (unlimited)
        if ($discipline->athlete_limit === null) {
            return true;
        }

        $validationResult = (new ValidateAthleteLimitAction)->execute(
            $discipline,
            $this->event->id,
            $this->model,
            $this->selectedIndividuals
        );
        // Handle the array return value
        if (! $validationResult['valid']) {
            $this->errorMessages[] = $validationResult['message'];
            $this->showErrorNotification();

            return false;
        }

        return true;
    }
    private function enforceTeamCompositionLimit(Discipline $discipline): bool
    {
        // If it's an individual discipline or no requirements exist, skip this check
        if (
            $discipline->enrollment_type === 'individual'
            || empty($discipline->team_composition_requirements)
        ) {
            return true;
        }
        // Otherwise, run the action
        $isValid = (new ValidateTeamCompositionAction)->execute(
            $discipline,
            $this->selectedIndividuals,
            $this->errorMessages
        );

        if (! $isValid) {
            $this->showErrorNotification();

            return false;
        }

        return true;
    }

    /**
     * Enforce the max-disciplines-per-athlete limit for this discipline.
     *
     * Returns false if validation fails, true otherwise.
     */
    private function enforceMaxDisciplines(Discipline $discipline, bool $hasOutOfRace): bool
    {
        $outOfRaceAction = app(GetDisciplineOutOfRaceAttributeAction::class);
        $outOfRaceAttribute = $hasOutOfRace ? $outOfRaceAction->execute($discipline) : null;

        foreach ($this->selectedIndividuals as $individual) {
            // Use the new helper method
            $attributeValues = $this->getAttributeValues($individual['id']);

            if ($hasOutOfRace && $outOfRaceAttribute) {
                $isOutOfRace = $outOfRaceAction->isOutOfRace($attributeValues, $outOfRaceAttribute);

                if ($isOutOfRace) {
                    \Log::debug('Skipping max disciplines validation for out-of-race athlete', [
                        'individual_id' => $individual['id'],
                        'discipline_id' => $discipline->id,
                    ]);

                    continue;
                }
            }

            $isValid = (new ValidateAthleteMaxDisciplinesAction)->execute(
                $this->event->competition,
                $individual['id'],
                $discipline,
                $this->errorMessages,
                $attributeValues
            );

            if (! $isValid) {

                $this->showErrorNotification();

                return false;
            }
        }

        return true;
    }

    private function validateOutOfRace(Discipline $discipline): bool
    {
        // If OOR attribute doesn't exist, we can skip entirely:
        if (! app(GetDisciplineOutOfRaceAttributeAction::class)->hasOutOfRaceAttribute($discipline)) {
            return true;
        }

        // If it does, run any complex validations here:
        return (new ValidateOutOfRaceEnrollmentAction)->execute(
            $discipline,
            $this->event->id,
            $this->model,
            $this->disciplineAttributeValues,
            $this->errorMessages
        );
    }

    private function getFederationOrEntityPrefix(): string
    {
        if ($this->model instanceof \Domain\Federations\Models\Federation) {
            // Use "fed_" followed by the federation's ID
            return 'fed_' . $this->model->id;
        } elseif ($this->model instanceof \Domain\Entities\Models\Entity) {
            // Use "ent_" followed by the entity's ID
            return 'ent_' . $this->model->id;
        }

        // Fallback for unexpected model types - make it highly unique
        return 'unk_' . uniqid();
    }

    public function buildTeamIdentifier(): string
    {
        return DB::transaction(function () {
            $prefix = $this->getFederationOrEntityPrefix();

            // Get the highest existing counter for this entity and event
            $maxIdentifier = AthleteEnrollment::where('event_id', $this->event->id)
                ->where(function ($query) {
                    if ($this->model instanceof Federation) {
                        $query->where('federation_id', $this->model->id);
                    } else {
                        $query->where('entity_id', $this->model->id);
                    }
                })
                ->where('team_identifier', 'LIKE', $prefix . '_%')
                ->lockForUpdate()  // Add a lock to prevent race conditions
                ->get()
                ->map(function ($enrollment) use ($prefix) {
                    if (preg_match('/' . preg_quote($prefix) . '_(\d+)$/', $enrollment->team_identifier, $matches)) {
                        return (int) $matches[1];
                    }

                    return 0;
                })
                ->max() ?? 0;

            // Create new identifier with next sequential number
            $nextNumber = $maxIdentifier + 1;
            $newIdentifier = $prefix . '_' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            // Double-check that this identifier doesn't exist (safety check)
            while (AthleteEnrollment::where('event_id', $this->event->id)
                ->where(function ($query) {
                    if ($this->model instanceof Federation) {
                        $query->where('federation_id', $this->model->id);
                    } else {
                        $query->where('entity_id', $this->model->id);
                    }
                })
                ->where('team_identifier', $newIdentifier)
                ->exists()
            ) {
                $nextNumber++;
                $newIdentifier = $prefix . '_' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }

            return $newIdentifier;
        }, 5); // Add retry attempts for deadlock situations
    }

    private function collectAttributeValues($selectedIndividual): array
    {
        $attributeValues = [];

        if ($this->enrollmentType === EvtEventEnrollmentRoleEnum::ATHLETE->value) {
            $attributeValues = $this->disciplineAttributeValues[$selectedIndividual['id']] ?? [];
        } else {

            // Role-specific attributes: Change array structure to match expected format
            if (isset($this->roleAttributeValues[$selectedIndividual['id']])) {
                foreach ($this->roleAttributeValues[$selectedIndividual['id']] as $id => $value) {
                    $attributeValues[$id] = $value;
                }
            }
        }

        // Add global attributes
        if (! empty($this->globalAttributeValues)) {
            foreach ($this->globalAttributeValues as $id => $value) {
                $attributeValues[$id] = $value;
            }
        }

        return $attributeValues;
    }

    private function manageCreateEnrollment($discipline = null)
    {

        $successCount = 0; // store the athletes count
        // 1) If discipline is "relay," generate a single team_identifier
        $teamIdentifier = null;
        if (! empty($discipline) && $discipline->enrollment_type === 'relay') {
            // Example method to build something like "ita_0001"
            $teamIdentifier = $this->buildTeamIdentifier();
        }

        // Find existing enrollment for this individual or create a new one
        $baseEnrollment = Enrollment::firstOrCreate(
            [ // Attributes to find by
                'event_id' => $this->event->id,
                'enrollable_id' => $this->model->id,
                'enrollable_type' => get_class($this->model),
            ],
            [ // Attributes to use for creation if not found
                'user_id' => Auth::id(), // Add the authenticated user's ID
                // TODO: Consider adding other default fields if necessary,
                // e.g., 'status_class' => SomeInitialEnrollmentState::class,
                // 'total_amount' => 0,
                // 'currency_code' => $this->event->currency_code ?? 'USD',
            ]
        );

        // Loop selected individuals
        foreach ($this->selectedIndividuals as $selectedIndividual) {

            try {
                $attributeValues = $this->collectAttributeValues($selectedIndividual);

                $this->createEnrollment(
                    $selectedIndividual,
                    $baseEnrollment,
                    $attributeValues,
                    $teamIdentifier
                );

                $successCount++;
            } catch (ValidationException $e) {
                $this->errorMessages[] = "Validation failed for {$selectedIndividual['name']}: " . $e->getMessage();
                throw $e;
            }
        }
        // Send error messages if they exist
        if (! empty($this->errorMessages)) {
            $this->showErrorNotification();
        }

        return $successCount;
    }

    private function createEnrollment(
        $selectedIndividual,
        $baseEnrollment,
        $attributes,
        ?string $teamIdentifier = null
    ): void {

        if ($this->enrollmentType === EvtEventEnrollmentRoleEnum::ATHLETE->value) {
            // Get the discipline
            $discipline = Discipline::findOrFail($this->selectedDiscipline);

            // Validate max disciplines per athlete
            $validateMaxDisciplines = new ValidateAthleteMaxDisciplinesAction;
            if (! $validateMaxDisciplines->execute(
                $this->event->competition,
                $selectedIndividual['id'],
                $discipline,
                $this->errorMessages,
                $attributes
            )) {
                throw ValidationException::withMessages([
                    'disciplines' => implode(', ', $this->errorMessages),
                ]);
            }

            $perPersonPricingId = ! empty($this->selectedPricingIds['perPerson']) ? (int) $this->selectedPricingIds['perPerson'] : null;
            $disciplinePricingId = ! empty($this->selectedPricingIds['discipline']) ? (int) $this->selectedPricingIds['discipline'] : null;
            $eventFeePricingId = ! empty($this->selectedPricingIds['eventFee']) ? (int) $this->selectedPricingIds['eventFee'] : null;

            try {

                (new CreateAthleteEnrollmentAction)->execute(
                    $this->event,
                    $this->model instanceof Federation ? $this->model : null,
                    $selectedIndividual['id'],
                    $baseEnrollment,
                    $perPersonPricingId,
                    $disciplinePricingId,
                    $eventFeePricingId,
                    $this->selectedDiscipline,
                    $attributes,
                    $this->model instanceof Entity ? $this->model : null,
                    $teamIdentifier
                );
            } catch (ValidationException $e) {
                $this->errorMessages[] = $e->getMessage();
            }

            return;
        }

        $actions = [
            EvtEventEnrollmentRoleEnum::COACH->value => [CreateCoachEnrollmentAction::class, $this->getCoachParams()],
            EvtEventEnrollmentRoleEnum::OFFICIAL->value => [CreateTeamOfficialEnrollmentAction::class, $this->getOfficialParams()],
            EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL->value => [CreateRefereeEnrollmentAction::class, $this->getRefereeParams()],
        ];

        if (! isset($actions[$this->enrollmentType])) {
            throw new \Exception('Invalid enrollment type');
        }

        [$actionClass, $params] = $actions[$this->enrollmentType];

        app($actionClass)->execute(
            $this->event,
            $this->model,
            $selectedIndividual['id'],
            $baseEnrollment,
            $this->selectedPricingTier,
            $attributes,
            ...$params
        );
    }

    private function getAttributeValues($individualId): array
    {
        $attributeValues = [];

        // Get discipline attribute values
        if (isset($this->disciplineAttributeValues[$individualId])) {
            foreach ($this->disciplineAttributeValues[$individualId] as $attributeId => $value) {
                // Handle both simple and complex value formats
                if (is_array($value) && isset($value['value'])) {
                    $attributeValues[$attributeId] = $value['value'];
                } else {
                    $attributeValues[$attributeId] = $value;
                }
            }
        }

        \Log::debug('Attribute values for validation', [
            'individualId' => $individualId,
            'values' => $attributeValues,
        ]);

        return $attributeValues;
    }

    private function getCoachParams(): array
    {
        return [];
    }

    private function getOfficialParams(): array
    {
        return [];
    }

    private function getRefereeParams(): array
    {
        return [];
    }

    private function getEnrollmentSuccessMessage(int $successCount): string
    {
        return match ($this->enrollmentType) {
            EvtEventEnrollmentRoleEnum::ATHLETE->value => __(':success athletes assigned to discipline', ['success' => $successCount]),
            EvtEventEnrollmentRoleEnum::COACH->value => __(':success coaches saved successfully', ['success' => $successCount]),
            EvtEventEnrollmentRoleEnum::OFFICIAL->value => __(':success team officials saved successfully', ['success' => $successCount]),
            EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL->value => __(':success referees saved successfully', ['success' => $successCount]),
            default => __(':success individuals enrolled successfully', ['success' => $successCount])
        };
    }

    public function resetFilters()
    {
        $this->disciplineFilters = [
            'enrollment_type' => '',
            'gender' => '',
            'style' => '',
            'distance' => '',
        ];

        // Reset filtered disciplines to show all
        $this->filteredDisciplines = $this->disciplines;

        // Reset selected discipline since filters are cleared
        $this->selectedDiscipline = null;

        // Reset pricing data if needed
        $this->loadPricingData();
    }

    public function render()
    {

        $selectedDisciplineModel = $this->enrollmentType === EvtEventEnrollmentRoleEnum::ATHLETE->value
            ? Discipline::find($this->selectedDiscipline)
            : null;

        // Combine disciplineAttributeValues and roleAttributeValues for the view
        $attributeValues = [];

        if ($this->enrollmentType === EvtEventEnrollmentRoleEnum::ATHLETE->value) {
            $attributeValues = $this->disciplineAttributeValues ?? [];
        } else {
            $attributeValues = $this->roleAttributeValues ?? [];
        }

        return view('livewire.evt-events.manage-enrollment', [
            'selectedDisciplineModel' => $selectedDisciplineModel,
            'multiplePerPersonPricing' => $this->pricingData->where('price_type', EvtEventFeeTypeEnum::PER_PERSON->value)->count() > 1,
            'multipleDisciplinePricing' => $this->pricingData->where('price_type', EvtEventFeeTypeEnum::PER_DISCIPLINE->value)->count() > 1,
            'multipleEventFeePricing' => $this->pricingData->where('price_type', EvtEventFeeTypeEnum::EVENT_FEE->value)->count() > 1,
            'enrollmentType' => $this->enrollmentType,
            'attributeValues' => $attributeValues,
        ]);
    }

    private function resetFormState(): void
    {
        $this->attributeDisciplinesToIndividual = [
            'disciplineIds' => [],
            'attributes' => [],
        ];
        $this->disciplineAttributeValues = [];
        $this->errorMessages = [];

        // Reset any pricing selections if they exist
        $this->selectedPricingIds = [
            'perPerson' => null,
            'discipline' => null,
            'eventFee' => null,
        ];
    }

    protected function resetAttributes(): void
    {
        $this->disciplineAttributes = [];
        $this->globalAttributes = [];
        $this->disciplineAttributeValues = [];
        $this->roleAttributes = [];
        $this->roleAttributeValues = [];

        // Clear any cached attribute data
        unset($this->cachedAttributes["discipline_attributes_{$this->selectedDiscipline}"]);
    }

    public function resetEnrollmentAttributes(Individual $individual): void
    {
        try {
            DB::beginTransaction();

            $enrollment = null;

            if ($this->enrollmentType === EvtEventEnrollmentRoleEnum::COACH->value) {
                $enrollment = CoachEnrollment::where('event_id', $this->event->id)
                    ->where('individual_id', $individual->id)
                    ->where($this->model instanceof Federation ? 'federation_id' : 'entity_id', $this->model->id)
                    ->first();

                if ($enrollment) {
                    // Delete all attributes first
                    if ($enrollment->attributes()->exists()) {
                        $enrollment->attributes()->delete();
                    }

                    // Reset to initial state instead of canceling
                    $enrollment->status_class = RegisteredCoachEnrollmentState::class;
                    $enrollment->save();

                    // Log the activity
                    activity(__('events.activity_log.coach_event_registration'))
                        ->causedBy(auth()->user())
                        ->performedOn($enrollment)
                        ->withProperties([
                            'event_id' => $this->event->id,
                            'event_name' => $this->event->name,
                            'individual_id' => $individual->id,
                            'old_status' => $enrollment->getOriginal('status_class'),
                            'new_status' => RegisteredCoachEnrollmentState::class,
                            'action' => 'reset_attributes',
                        ])
                        ->log(__('events.activity_log.enrollment_attributes_reset', ['type' => __('events.coach')]));
                }
            } elseif ($this->enrollmentType === EvtEventEnrollmentRoleEnum::OFFICIAL->value) {
                $enrollment = TeamOfficialEnrollment::where('event_id', $this->event->id)
                    ->where('individual_id', $individual->id)
                    ->where($this->model instanceof Federation ? 'federation_id' : 'entity_id', $this->model->id)
                    ->first();

                if ($enrollment) {
                    // Delete all attributes first
                    if ($enrollment->attributes()->exists()) {
                        $enrollment->attributes()->delete();
                    }

                    // Reset to initial state instead of canceling
                    $enrollment->status_class = RegisteredTeamOfficialEnrollmentState::class;
                    $enrollment->save();

                    // Log the activity
                    activity(__('events.activity_log.official_event_registration'))
                        ->causedBy(auth()->user())
                        ->performedOn($enrollment)
                        ->withProperties([
                            'event_id' => $this->event->id,
                            'event_name' => $this->event->name,
                            'individual_id' => $individual->id,
                            'old_status' => $enrollment->getOriginal('status_class'),
                            'new_status' => RegisteredTeamOfficialEnrollmentState::class,
                            'action' => 'reset_attributes',
                        ])
                        ->log(__('events.activity_log.enrollment_attributes_reset', ['type' => __('events.team_official')]));
                }
            } elseif ($this->enrollmentType === EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL->value) {
                $enrollment = RefereeEnrollment::where('event_id', $this->event->id)
                    ->where('individual_id', $individual->id)
                    ->where($this->model instanceof Federation ? 'federation_id' : 'entity_id', $this->model->id)
                    ->first();

                if ($enrollment) {
                    // Delete all attributes first
                    if ($enrollment->attributes()->exists()) {
                        $enrollment->attributes()->delete();
                    }

                    // Reset to initial state (Active for referees)
                    $enrollment->status_class = ActiveRefereeEnrollmentState::class;
                    $enrollment->save();

                    // Log the activity
                    activity(__('events.activity_log.referee_event_registration'))
                        ->causedBy(auth()->user())
                        ->performedOn($enrollment)
                        ->withProperties([
                            'event_id' => $this->event->id,
                            'event_name' => $this->event->name,
                            'individual_id' => $individual->id,
                            'old_status' => $enrollment->getOriginal('status_class'),
                            'new_status' => ActiveRefereeEnrollmentState::class,
                            'action' => 'reset_attributes',
                        ])
                        ->log(__('events.activity_log.enrollment_attributes_reset', ['type' => __('events.referee')]));
                }
            }

            if (! $enrollment) {
                throw new \Exception('Enrollment not found');
            }

            DB::commit();
            $this->refreshTable();

            Notification::make()
                ->title('Success')
                ->body(__('Enrollment attributes successfully reset'))
                ->success()
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Error')
                ->body(__('Failed to reset enrollment: ') . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getStep2RouteProperty(): string
    {
        return $this->model instanceof Federation
            ? route('federation.evt-events.events.review', ['event' => $this->event])
            : route('entity.evt-events.events.review', ['event' => $this->event]);
    }

    public function getStep3RouteProperty(): string
    {
        return $this->model instanceof Federation
            ? route('federation.evt-events.events.confirmed-enrollments', ['event' => $this->event])
            : route('entity.evt-events.events.confirmed-enrollments', ['event' => $this->event]);
    }

    public function getEventShowRouteProperty(): string
    {
        return $this->model instanceof Federation
            ? route('federation.evt-events.events.show', ['event' => $this->event])
            : route('entity.evt-events.events.show', ['event' => $this->event]);
    }
}
