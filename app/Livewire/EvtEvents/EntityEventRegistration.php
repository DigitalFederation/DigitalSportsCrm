<?php

namespace App\Livewire\EvtEvents;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Enums\EvtEventEnrollmentRoleEnum;
use App\Enums\EvtEventFeeTypeEnum;
use Domain\Documents\States\PendingDocumentState;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Actions\GetDisciplinesFromEventAction;
use Domain\EvtEvents\Actions\GetEligibleEntityAthletesAction;
use Domain\EvtEvents\Actions\PreRegisterAthletesAction;
use Domain\EvtEvents\Actions\PreRegisterParticipantsAction;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\EvtEvents\Services\EnrollmentsCostCalculationService;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualEntityState;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;
use Livewire\Component;

class EntityEventRegistration extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public Event $event;
    public Entity $entity;
    protected $tableRecords = [];
    protected $selectedRecords = [];
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

    public $totalSelectedCost = 0;
    public $perPersonPrice = 0;
    public $selectedCount = 0;

    public string $activeTab = 'register';
    public bool $roleChanged = false;

    // Per-discipline pricing properties
    public bool $requiresDisciplineSelection = false;
    public array $disciplinesByAthlete = [];
    public $availableDisciplines;
    public array $disciplinePricing = [];

    protected $listeners = ['reload-table' => '$refresh'];

    public function boot(EnrollmentsCostCalculationService $costCalculationService)
    {
        $this->costCalculationService = $costCalculationService;
    }

    public function booted(): void
    {
        $this->dispatch('filament-tables::reorder');
    }

    public function mount(
        Event $event,
        Entity $entity
    ): void {
        $this->event = $event;
        $this->entity = $entity;
        $this->perPersonPrice = $this->getPerPersonPrice();
        // Force initial table load
        // $this->dispatch('filament-tables::reload');
        $this->initializePricingForRoles();
        $this->calculateTotalCosts();

        // Check if per-discipline pricing is required
        $this->checkPerDisciplineRequirement();
        if ($this->requiresDisciplineSelection) {
            $this->loadAvailableDisciplines();
            $this->loadDisciplinePricing();
        }
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

    protected function checkPerDisciplineRequirement(): void
    {
        $this->requiresDisciplineSelection = $this->event->hasPerDisciplinePricing();
    }

    protected function loadAvailableDisciplines(): void
    {
        $disciplineData = app(GetDisciplinesFromEventAction::class)->execute($this->event);
        $this->availableDisciplines = $disciplineData['disciplines'] ?? collect();
    }

    protected function loadDisciplinePricing(): void
    {
        $pricingRecords = $this->event->getPerDisciplinePricing();
        foreach ($pricingRecords as $pricing) {
            if ($pricing->discipline_id) {
                $this->disciplinePricing[$pricing->discipline_id] = [
                    'price' => $pricing->price,
                    'pricing_id' => $pricing->id,
                ];
            }
        }
    }

    public function toggleDisciplineForAthlete(string $athleteId, int $disciplineId): void
    {
        if (! isset($this->disciplinesByAthlete[$athleteId])) {
            $this->disciplinesByAthlete[$athleteId] = [];
        }

        $key = array_search($disciplineId, $this->disciplinesByAthlete[$athleteId]);
        if ($key !== false) {
            unset($this->disciplinesByAthlete[$athleteId][$key]);
            $this->disciplinesByAthlete[$athleteId] = array_values($this->disciplinesByAthlete[$athleteId]);
        } else {
            $this->disciplinesByAthlete[$athleteId][] = $disciplineId;
        }

        $this->recalculateCostsWithDisciplines();
    }

    protected function recalculateCostsWithDisciplines(): void
    {
        $baseCost = 0;
        $disciplineCost = 0;

        foreach ($this->selectedParticipants['athlete'] as $participant) {
            $baseCost += $this->rolePricing['athlete'] ?? 0;

            $athleteId = $participant['id'];
            if (isset($this->disciplinesByAthlete[$athleteId])) {
                foreach ($this->disciplinesByAthlete[$athleteId] as $disciplineId) {
                    $disciplineCost += $this->disciplinePricing[$disciplineId]['price'] ?? 0;
                }
            }
        }

        $this->roleCosts['athlete'] = $baseCost + $disciplineCost;
        $this->calculateTotalCosts();
    }

    protected function getEligibleIndividualsQuery(): Builder
    {
        return Individual::query()
            ->whereHas('individualEntities', function (Builder $query) {
                $query->where('entity_id', $this->entity->id)
                    ->where('active', true);
            })
            ->whereDoesntHave('athleteEnrollments', function (Builder $query) {
                $query->where('event_id', $this->event->id)
                    ->whereIn('status_class', [
                        EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
                        EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value,
                        EvtAthleteEnrollmentStatusEnum::PAID->value,
                    ]);
            });
    }
    protected function getEligibleAthletesQuery(): Builder
    {
        $query = app(GetEligibleEntityAthletesAction::class)->execute(
            $this->event->id,
            $this->entity->id,
            null
        );

        // Filter out athletes already enrolled in this event
        $query->whereDoesntHave('athleteEnrollments', function (Builder $q) {
            $q->where('event_id', $this->event->id);
        });

        return $query;
    }
    protected function getEligibleOfficialsQuery(): Builder
    {
        return Individual::query()
            ->whereHas('individualEntities', function (Builder $query) {
                $query->where('entity_id', $this->entity->id)
                    ->where('status_class', ActiveIndividualEntityState::class);
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

    /**
     *  TODO: find the proper filter for Referees
     * For the time being this query will not filter any specific condition for Referees
     */
    protected function getEligibleRefereesQuery(): Builder
    {
        return Individual::query()
            ->whereHas('individualEntities', function (Builder $query) {
                $query->where('entity_id', $this->entity->id)
                    ->where('status_class', ActiveIndividualEntityState::class);
            })
            ->whereDoesntHave('refereedCompetitions', function (Builder $query) {
                $query->where('event_id', $this->event->id)
                    ->whereIn('status_class', [
                        EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
                        EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value,
                        EvtAthleteEnrollmentStatusEnum::PAID->value,
                    ]);
            });
    }

    /**
     * TODO: find the proper filter for Coaches
     * For the time being this query will not filter any specific condition for Coaches
     */
    protected function getEligibleCoachesQuery(): Builder
    {
        return Individual::query()
            ->whereHas('individualEntities', function (Builder $query) {
                $query->where('entity_id', $this->entity->id)
                    ->where('status_class', ActiveIndividualEntityState::class);
            })
            ->whereDoesntHave('coachEnrollments', function (Builder $query) {
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
            ->where('enrollable_id', $this->entity->id)
            ->where('enrollable_type', Entity::class)
            ->where(function ($query) {
                $query->whereNull('payment_status')
                    ->orWhere('payment_status', 'pending');
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

    protected function getRegisteredCount(): int
    {
        return $this->event->athleteEnrollments()
            ->where('entity_id', $this->entity->id)
            ->whereIn('status_class', [
                EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
                EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value,
            ])
            ->count();
    }

    protected function registerAthletes(array $selectedAthletes): void
    {
        try {
            $registration = app(PreRegisterAthletesAction::class)->execute(
                $this->event,
                $this->entity,
                $selectedAthletes
            );

            $this->dispatch('registration-completed', id: $registration->id);

            // Filament Notification
            Notification::make()
                ->title('Success')
                ->body(__('Athletes registered successfully. Please proceed to payment.'))
                ->success()
                ->send();
        } catch (\Exception $e) {

            \Log::error('registerAthletes error: ' . $e->getMessage());
            Notification::make()
                ->title('Error')
                ->body(__('Failed to register athletes. Please try again.'))
                ->danger()
                ->send();
        }
    }

    /**
     * Table - Query
     */
    private function getQueryForRole(): Builder
    {

        $query = match ($this->activeRole) {
            'athlete' => $this->getEligibleAthletesQuery()->clone(),
            'coach' => $this->getEligibleCoachesQuery()->clone(),
            'referee' => $this->getEligibleRefereesQuery()->clone(),
            'official' => $this->getEligibleOfficialsQuery()->clone(),
            default => $this->getEligibleAthletesQuery()->clone()
        };

        return $query->with(['country']);
    }

    private function addParticipants(Collection $records): void
    {
        $this->selectedParticipants[$this->activeRole] = array_merge(
            $this->selectedParticipants[$this->activeRole],
            $records->toArray()
        );
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
     * Handle the registration process for all selected participants
     */
    public function submitRegistration(): void
    {
        try {
            DB::beginTransaction();

            // Filter out empty participant arrays
            $participants = array_filter($this->selectedParticipants, fn ($roleParticipants) => ! empty($roleParticipants));

            if (empty($participants)) {
                throw new Exception(__('No participants selected for registration.'));
            }

            // Validate discipline assignments for per-discipline pricing
            if ($this->requiresDisciplineSelection && isset($participants['athlete'])) {
                $missingDisciplines = [];
                $processedAthletes = [];

                foreach ($participants['athlete'] as $participant) {
                    $athleteId = $participant['id'];
                    if (in_array($athleteId, $processedAthletes)) {
                        continue;
                    }
                    $processedAthletes[] = $athleteId;

                    if (empty($this->disciplinesByAthlete[$athleteId])) {
                        $missingDisciplines[] = $participant['full_name'] ?? $athleteId;
                    }
                }

                if (! empty($missingDisciplines)) {
                    DB::rollBack();

                    Notification::make()
                        ->title(__('events.discipline_selection_required'))
                        ->body(__('events.please_assign_disciplines') . ': ' . implode(', ', $missingDisciplines))
                        ->danger()
                        ->send();

                    return;
                }
            }

            // Format participants data for PreRegisterParticipantsAction
            $formattedParticipants = $this->formatParticipantsForRegistration($participants);
            // Execute pre-registration
            $enrollment = app(PreRegisterParticipantsAction::class)->execute(
                $this->event,
                $this->entity,
                $formattedParticipants
            );
            Log::info('submitRegistration', [
                'formattedParticipants' => $formattedParticipants,
                'enrollment' => $enrollment,
            ]);
            DB::commit();

            // Reset selections after successful registration
            $this->resetSelections();

            $this->dispatch('registration-completed', id: $enrollment->id);

            Notification::make()
                ->title(__('Success'))
                ->body(__('Participants registered successfully. Please proceed to payment.'))
                ->success()
                ->send();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'event' => $this->event->id,
            ]);

            Notification::make()
                ->title(__('Error'))
                ->body(__('Failed to register participants. Please try again.'))
                ->danger()
                ->send();
        }
    }

    /**
     * Format selected participants for the registration action
     */
    protected function formatParticipantsForRegistration(array $participants): array
    {
        $formatted = [];

        foreach ($participants as $roleType => $roleParticipants) {
            if (empty($roleParticipants)) {
                continue;
            }

            // Handle per-discipline pricing for athletes
            if ($roleType === 'athlete' && $this->requiresDisciplineSelection) {
                $formatted[$roleType] = [];

                foreach ($roleParticipants as $participant) {
                    $athleteId = $participant['id'];
                    $disciplines = $this->disciplinesByAthlete[$athleteId] ?? [];

                    foreach ($disciplines as $disciplineId) {
                        $formatted[$roleType][] = [
                            'id' => $athleteId,
                            'discipline_id' => $disciplineId,
                            'discipline_pricing_id' => $this->disciplinePricing[$disciplineId]['pricing_id'] ?? null,
                            'discipline_price' => $this->disciplinePricing[$disciplineId]['price'] ?? 0,
                        ];
                    }
                }

                continue;
            }

            // Default handling for other roles
            $formatted[$roleType] = array_map(function ($participant) {
                return [
                    'id' => $participant['id'],
                    'discipline_id' => $participant['discipline_id'] ?? null,
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
        $this->disciplinesByAthlete = [];
    }

    public function removeParticipant(string $role, string $participantId): void
    {
        $this->selectedParticipants[$role] = array_filter(
            $this->selectedParticipants[$role],
            fn ($p) => $p['id'] !== $participantId
        );

        // Also remove discipline selections for this athlete
        if ($role === 'athlete' && isset($this->disciplinesByAthlete[$participantId])) {
            unset($this->disciplinesByAthlete[$participantId]);
        }

        // Recalculate costs
        if ($this->requiresDisciplineSelection && $role === 'athlete') {
            $this->recalculateCostsWithDisciplines();
        } else {
            $this->calculateRoleCost($role);
            $this->calculateTotalCosts();
        }
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

        return view('livewire.evt-events.entity-event-registration', [
            'selectedParticipants' => $this->selectedParticipants,
            'roleCosts' => $this->roleCosts,
            'totalCost' => $this->totalCost,
            'pendingEnrollments' => $this->getPendingEnrollments(),
            'availableRoles' => $this->getAvailableRoles(),
            // Per-discipline pricing data
            'requiresDisciplineSelection' => $this->requiresDisciplineSelection,
            'disciplinesByAthlete' => $this->disciplinesByAthlete,
            'availableDisciplines' => $this->availableDisciplines,
            'disciplinePricing' => $this->disciplinePricing,
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
        $this->tableSearchQuery = null;
        $this->tableColumnSearchQueries = [];
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
}
