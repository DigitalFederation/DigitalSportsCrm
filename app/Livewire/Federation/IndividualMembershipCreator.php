<?php

namespace App\Livewire\Federation;

use App\Enums\MembershipTargetType;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Actions\BulkMemberSubscriptionAction;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Services\ValidationPlanPrivilegeService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class IndividualMembershipCreator extends Component
{
    use WithPagination;

    public ?int $selectedEntityId = null;
    public ?int $selectedPackageId = null;
    public array $selectedIndividuals = [];
    public string $search = '';
    public bool $confirmingSubscription = false;
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
            ->whereHas('affiliationPlans', function ($query) {
                $query->where(function ($q) {
                    $q->where('affiliation_plans.type', 'individual')
                        ->orWhere(function ($subQ) {
                            $subQ->where('affiliation_plans.type', 'entity')
                                ->whereNotNull('affiliation_plans.individual_fee')
                                ->where('affiliation_plans.individual_fee', '>', 0);
                        });
                })->where(function ($q) {
                    $q->whereNull('start_date')
                        ->orWhere('start_date', '<=', now());
                })->where(function ($q) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', now());
                });
            })
            ->with(['affiliationPlans', 'insurancePlans', 'federations'])
            ->get();
    }

    #[Computed]
    public function selectedPackage(): ?MembershipPackage
    {
        return $this->selectedPackageId
            ? MembershipPackage::with(['affiliationPlans', 'insurancePlans'])->find($this->selectedPackageId)
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
                $query->whereDoesntHave('memberSubscriptions', function ($q) {
                    $q->where('membership_package_id', $this->selectedPackageId)
                        ->where('end_date', '>=', now());
                });
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
                    $query->whereHas('memberSubscriptions', function ($q) {
                        $q->where('end_date', '>=', now());
                    });
                } else {
                    $query->whereDoesntHave('memberSubscriptions', function ($q) {
                        $q->where('end_date', '>=', now());
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

    public function confirmSubscription()
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

        $this->confirmingSubscription = true;
    }

    public function processSubscription(BulkMemberSubscriptionAction $action): void
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
            $results = $action->execute(
                $this->selectedPackage,
                $this->selectedIndividuals,
                [
                    'requester_type' => get_class($this->selectedEntity),
                    'requester_id' => $this->selectedEntity->id,
                    'request_type' => 'federation_facilitated',
                ]
            );

            $successCount = count($results['success']);
            $failedCount = count($results['failed']);

            if ($successCount > 0) {
                Notification::make()
                    ->title(__('federation.subscriptions_created'))
                    ->body(__('federation.success_count_for_entity', [
                        'count' => $successCount,
                        'entity' => $this->selectedEntity->name,
                    ]))
                    ->success()
                    ->duration(8000)
                    ->send();
            }

            if ($failedCount > 0) {
                Notification::make()
                    ->title(__('federation.some_subscriptions_failed'))
                    ->body(__('federation.failed_count', ['count' => $failedCount]))
                    ->warning()
                    ->persistent()
                    ->send();
            }

            $this->redirect(route('federation.individual-memberships.index'));

        } catch (\Exception $e) {
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

        if (! $validationPlanService->canSubscribeMembersToPackages($this->selectedEntity)) {
            $this->hasValidationPlanPrivileges = false;
            $reason = $validationPlanService->getValidationPlanReason($this->selectedEntity, 'entity_member_subscriptions');
            $this->validationPlanMessage = __('federation.entity_member_subscriptions_not_authorized', ['reason' => $reason]);
        } else {
            $this->hasValidationPlanPrivileges = true;
            $this->validationPlanMessage = '';
        }
    }

    public function render()
    {
        return view('livewire.federation.individual-membership-creator', [
            'entities' => $this->entities,
            'selectedEntity' => $this->selectedEntity,
            'packages' => $this->availablePackages,
            'individuals' => $this->individuals,
            'hasValidationPlanPrivileges' => $this->hasValidationPlanPrivileges,
            'validationPlanMessage' => $this->validationPlanMessage,
        ]);
    }
}
