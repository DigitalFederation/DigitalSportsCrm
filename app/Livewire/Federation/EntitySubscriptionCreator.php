<?php

namespace App\Livewire\Federation;

use App\Enums\MembershipTargetType;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Memberships\Actions\CreateMemberSubscriptionAction;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Services\ValidationPlanPrivilegeService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class EntitySubscriptionCreator extends Component
{
    public ?int $selectedEntityId = null;
    public ?int $selectedPackageId = null;
    public bool $confirmingSubscription = false;
    public bool $hasValidationPlanPrivileges = true;
    public string $validationPlanMessage = '';

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
            ->where('target_type', MembershipTargetType::ENTITY)
            ->whereJsonContains('distribution_methods', 'direct')
            ->whereHas('federations', function ($query) use ($entityFederationIds) {
                $query->whereIn('federation.id', $entityFederationIds);
            })
            ->whereHas('affiliationPlans', function ($query) {
                $query->where(function ($q) {
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

    public function updatedSelectedEntityId()
    {
        $this->selectedPackageId = null;

        if ($this->selectedEntity) {
            $this->checkValidationPlanPrivileges();
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

        if (! $this->selectedEntity || ! $this->selectedPackage) {
            return;
        }

        $this->confirmingSubscription = true;
    }

    public function processSubscription(CreateMemberSubscriptionAction $action): void
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
            'selectedEntityId' => 'required|exists:entity,id',
            'selectedPackageId' => 'required|exists:membership_packages,id',
        ], [
            'selectedEntityId.required' => __('federation.entity_required'),
            'selectedEntityId.exists' => __('federation.invalid_entity'),
            'selectedPackageId.required' => __('federation.package_required'),
            'selectedPackageId.exists' => __('federation.invalid_package'),
        ]);

        try {
            $subscription = $action->execute(
                $this->selectedPackage,
                $this->selectedEntity,
                [
                    'requester_type' => get_class($this->selectedEntity),
                    'requester_id' => $this->selectedEntity->id,
                    'request_type' => 'federation_facilitated',
                ]
            );

            Notification::make()
                ->title(__('federation.subscription_created'))
                ->body(__('federation.entity_subscription_success', [
                    'entity' => $this->selectedEntity->name,
                    'package' => $this->selectedPackage->name,
                ]))
                ->success()
                ->duration(8000)
                ->send();

            $this->redirect(route('federation.entity-subscriptions.index'));

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

        if (! $validationPlanService->canSubscribeToPackages($this->selectedEntity)) {
            $this->hasValidationPlanPrivileges = false;
            $reason = $validationPlanService->getValidationPlanReason($this->selectedEntity, 'entity_subscriptions');
            $this->validationPlanMessage = __('federation.entity_subscriptions_not_authorized', ['reason' => $reason]);
        } else {
            $this->hasValidationPlanPrivileges = true;
            $this->validationPlanMessage = '';
        }
    }

    public function render()
    {
        return view('livewire.federation.entity-subscription-creator', [
            'entities' => $this->entities,
            'selectedEntity' => $this->selectedEntity,
            'packages' => $this->availablePackages,
            'hasValidationPlanPrivileges' => $this->hasValidationPlanPrivileges,
            'validationPlanMessage' => $this->validationPlanMessage,
        ]);
    }
}
