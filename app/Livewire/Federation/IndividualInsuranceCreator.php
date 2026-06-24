<?php

namespace App\Livewire\Federation;

use App\Enums\MembershipTargetType;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Individuals\Models\Individual;
use Domain\Insurance\Models\Insurance;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\Services\ValidationPlanPrivilegeService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class IndividualInsuranceCreator extends Component
{
    use WithPagination;

    public ?int $selectedEntityId = null;
    public ?int $selectedPackageId = null;
    public array $selectedIndividuals = [];
    public string $search = '';
    public bool $confirmingAssignment = false;
    public string $statusFilter = 'all';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';
    public bool $hasValidationPlanPrivileges = true;
    public string $validationPlanMessage = '';

    protected $queryString = [
        'selectedEntityId' => ['except' => null],
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        $this->checkFederationAccess();
    }

    #[Computed]
    public function entities(): Collection
    {
        $user = auth()->user();
        $federation = $user->federations()->first();

        if (! $federation) {
            return collect([]);
        }

        return Entity::whereHas('federations', function (Builder $query) use ($federation) {
            $query->select('federation.id')
                ->where('federation.id', $federation->id)
                ->where('entity_federation.status_class', ActiveEntityFederationState::class);
        })
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function selectedEntity(): ?Entity
    {
        return $this->selectedEntityId
            ? $this->entities->firstWhere('id', $this->selectedEntityId)
            : null;
    }

    #[Computed]
    public function availablePackages(): Collection
    {
        if (! $this->selectedEntity) {
            return collect([]);
        }

        $entityFederationIds = $this->selectedEntity->federations()
            ->pluck('federation.id')
            ->toArray();

        return MembershipPackage::query()
            ->where('is_active', true)
            ->where('target_type', MembershipTargetType::INDIVIDUAL)
            ->whereJsonContains('distribution_methods', 'direct')
            ->whereHas('federations', function ($query) use ($entityFederationIds) {
                $query->whereIn('federation.id', $entityFederationIds);
            })
            // Insurance-only packages: have insurance plans but NO affiliation plans
            ->whereHas('insurancePlans', function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('start_date')
                        ->orWhere('start_date', '<=', now());
                })->where(function ($q) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', now());
                });
            })
            ->whereDoesntHave('affiliationPlans')
            ->with(['insurancePlans', 'federations'])
            ->get();
    }

    #[Computed]
    public function selectedPackage(): ?MembershipPackage
    {
        return $this->selectedPackageId
            ? MembershipPackage::with(['insurancePlans'])->find($this->selectedPackageId)
            : null;
    }

    public function getIndividualsProperty()
    {
        if (! $this->selectedEntity || ! $this->selectedPackageId) {
            return collect([]);
        }

        return Individual::query()
            ->whereHas('entities', function ($query) {
                $query->where('entity_id', $this->selectedEntity->id);
            })
            ->when($this->selectedPackageId, function ($query) {
                // Get insurance plan IDs from the selected package
                $insurancePlanIds = $this->selectedPackage?->insurancePlans->pluck('id')->toArray() ?? [];

                if (! empty($insurancePlanIds)) {
                    // Exclude individuals who already have active insurance from this package
                    $query->whereNotExists(function ($subQuery) use ($insurancePlanIds) {
                        $subQuery->select('id')
                            ->from('insurance')
                            ->whereColumn('insurance.member_id', 'individual.id')
                            ->where('insurance.member_type', Individual::class)
                            ->whereIn('insurance.insurance_plan_id', $insurancePlanIds)
                            ->where('insurance.end_date', '>=', now());
                    });
                }
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('member_code', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter !== 'all', function ($query) {
                if ($this->statusFilter === 'active') {
                    // Show individuals who have active insurance
                    $query->whereExists(function ($subQuery) {
                        $subQuery->select('id')
                            ->from('insurance')
                            ->whereColumn('insurance.member_id', 'individual.id')
                            ->where('insurance.member_type', Individual::class)
                            ->where('insurance.end_date', '>=', now());
                    });
                } else {
                    // Show individuals who don't have active insurance
                    $query->whereNotExists(function ($subQuery) {
                        $subQuery->select('id')
                            ->from('insurance')
                            ->whereColumn('insurance.member_id', 'individual.id')
                            ->where('insurance.member_type', Individual::class)
                            ->where('insurance.end_date', '>=', now());
                    });
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    public function updatedSelectedEntityId()
    {
        $this->selectedPackageId = null;
        $this->selectedIndividuals = [];
        $this->resetPage();

        if ($this->selectedEntity) {
            $this->checkValidationPlanPrivileges();
        }
    }

    public function updatedSelectedPackageId()
    {
        $this->selectedIndividuals = [];
        $this->resetPage();
    }

    public function toggleSelection($individualId)
    {
        if (in_array($individualId, $this->selectedIndividuals)) {
            $this->selectedIndividuals = array_diff($this->selectedIndividuals, [$individualId]);
        } else {
            $this->selectedIndividuals[] = $individualId;
        }
    }

    public function toggleAll()
    {
        if (count($this->selectedIndividuals) === $this->individuals->count()) {
            $this->selectedIndividuals = [];
        } else {
            $this->selectedIndividuals = $this->individuals->pluck('id')->all();
        }
    }

    public function sort($field)
    {
        if ($field === $this->sortField) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function confirmAssignment()
    {
        if (! $this->hasValidationPlanPrivileges) {
            Notification::make()
                ->title(__('federation.access_restricted'))
                ->body($this->validationPlanMessage)
                ->warning()
                ->persistent()
                ->send();

            return;
        }

        if (empty($this->selectedIndividuals) || ! $this->selectedPackage) {
            return;
        }

        $this->confirmingAssignment = true;
    }

    public function processAssignment(): void
    {
        if (! $this->hasValidationPlanPrivileges) {
            Notification::make()
                ->title(__('federation.access_restricted'))
                ->body($this->validationPlanMessage)
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        $this->validate([
            'selectedPackageId' => 'required|exists:membership_packages,id',
            'selectedIndividuals' => 'required|array|min:1',
        ], [
            'selectedPackageId.required' => __('federation.package_required'),
            'selectedPackageId.exists' => __('federation.invalid_package'),
            'selectedIndividuals.required' => __('federation.individuals_required'),
            'selectedIndividuals.min' => __('federation.min_one_individual'),
        ]);

        try {
            DB::beginTransaction();

            $successCount = 0;
            $failedCount = 0;

            foreach ($this->selectedIndividuals as $individualId) {
                try {
                    $individual = Individual::findOrFail($individualId);

                    // Verify individual belongs to the selected entity
                    if (! $individual->entities()->where('entity_id', $this->selectedEntity->id)->exists()) {
                        $failedCount++;

                        continue;
                    }

                    // Get the first insurance plan from the package (insurance-only packages have only one)
                    $insurancePlan = $this->selectedPackage->insurancePlans->first();

                    if (! $insurancePlan) {
                        $failedCount++;

                        continue;
                    }

                    // Create insurance record
                    Insurance::create([
                        'member_type' => get_class($individual),
                        'member_id' => $individual->id,
                        'insurance_plan_id' => $insurancePlan->id,
                        'start_date' => now(),
                        'end_date' => MemberSubscription::calculateAnnualEndDate(),
                        'status_class' => 'Domain\\Insurance\\States\\ActiveInsuranceState',
                        'requester_type' => get_class($this->selectedEntity),
                        'requester_id' => $this->selectedEntity->id,
                        'request_type' => 'federation_facilitated',
                    ]);

                    $successCount++;

                } catch (\Exception $e) {
                    $failedCount++;
                    report($e);
                }
            }

            DB::commit();

            if ($successCount > 0) {
                Notification::make()
                    ->title(__('federation.insurances_assigned'))
                    ->body(__('federation.insurance_success_count_for_entity', [
                        'count' => $successCount,
                        'entity' => $this->selectedEntity->name,
                    ]))
                    ->success()
                    ->duration(8000)
                    ->send();
            }

            if ($failedCount > 0) {
                Notification::make()
                    ->title(__('federation.some_assignments_failed'))
                    ->body(__('federation.failed_count', ['count' => $failedCount]))
                    ->warning()
                    ->persistent()
                    ->send();
            }

            $this->redirect(route('federation.individual-insurances.index'));

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title(__('federation.error'))
                ->body(__('federation.unexpected_error'))
                ->danger()
                ->persistent()
                ->send();

            report($e);
        }
    }

    protected function checkFederationAccess()
    {
        $user = auth()->user();
        if (! $user || ! $user->isFederation()) {
            abort(403, 'Unauthorized action.');
        }
    }

    protected function checkValidationPlanPrivileges()
    {
        if (! $this->selectedEntity) {
            $this->hasValidationPlanPrivileges = false;
            $this->validationPlanMessage = __('federation.entity_not_found');

            return;
        }

        $validationPlanService = app(ValidationPlanPrivilegeService::class);

        if (! $validationPlanService->canRequestInsurance($this->selectedEntity)) {
            $this->hasValidationPlanPrivileges = false;
            $reason = $validationPlanService->getValidationPlanReason($this->selectedEntity, 'insurance');
            $this->validationPlanMessage = __('federation.entity_member_insurance_not_authorized', ['reason' => $reason]);
        } else {
            $this->hasValidationPlanPrivileges = true;
            $this->validationPlanMessage = '';
        }
    }

    public function render()
    {
        return view('livewire.federation.individual-insurance-creator', [
            'entities' => $this->entities,
            'selectedEntity' => $this->selectedEntity,
            'packages' => $this->availablePackages,
            'individuals' => $this->individuals,
            'hasValidationPlanPrivileges' => $this->hasValidationPlanPrivileges,
            'validationPlanMessage' => $this->validationPlanMessage,
        ]);
    }
}
