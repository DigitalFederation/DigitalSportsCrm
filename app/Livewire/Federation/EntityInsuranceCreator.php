<?php

namespace App\Livewire\Federation;

use App\Enums\MembershipTargetType;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
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

class EntityInsuranceCreator extends Component
{
    public ?int $selectedEntityId = null;
    public ?int $selectedPackageId = null;
    public bool $confirmingAssignment = false;
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
            ->whereIn('target_type', [MembershipTargetType::ENTITY, MembershipTargetType::BOTH])
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

    public function updatedSelectedEntityId()
    {
        $this->selectedPackageId = null;

        if ($this->selectedEntity) {
            $this->checkValidationPlanPrivileges();
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

        if (! $this->selectedEntity || ! $this->selectedPackage) {
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
            'selectedEntityId' => 'required|exists:entity,id',
            'selectedPackageId' => 'required|exists:membership_packages,id',
        ], [
            'selectedEntityId.required' => __('federation.entity_required'),
            'selectedEntityId.exists' => __('federation.invalid_entity'),
            'selectedPackageId.required' => __('federation.package_required'),
            'selectedPackageId.exists' => __('federation.invalid_package'),
        ]);

        try {
            DB::beginTransaction();

            // Get the first insurance plan from the package (insurance-only packages have only one)
            $insurancePlan = $this->selectedPackage->insurancePlans->first();

            if (! $insurancePlan) {
                throw new \Exception('No insurance plan found in the selected package.');
            }

            // Create insurance record
            Insurance::create([
                'member_type' => get_class($this->selectedEntity),
                'member_id' => $this->selectedEntity->id,
                'insurance_plan_id' => $insurancePlan->id,
                'start_date' => now(),
                'end_date' => MemberSubscription::calculateAnnualEndDate(),
                'status_class' => 'Domain\\Insurance\\States\\ActiveInsuranceState',
                'requester_type' => get_class($this->selectedEntity),
                'requester_id' => $this->selectedEntity->id,
                'request_type' => 'federation_facilitated',
            ]);

            DB::commit();

            Notification::make()
                ->title(__('federation.insurance_assigned'))
                ->body(__('federation.entity_insurance_success', [
                    'entity' => $this->selectedEntity->name,
                    'plan' => $insurancePlan->name,
                ]))
                ->success()
                ->duration(8000)
                ->send();

            $this->redirect(route('federation.entity-insurances.index'));

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
            $this->validationPlanMessage = __('federation.entity_insurance_not_authorized', ['reason' => $reason]);
        } else {
            $this->hasValidationPlanPrivileges = true;
            $this->validationPlanMessage = '';
        }
    }

    public function render()
    {
        return view('livewire.federation.entity-insurance-creator', [
            'entities' => $this->entities,
            'selectedEntity' => $this->selectedEntity,
            'packages' => $this->availablePackages,
            'hasValidationPlanPrivileges' => $this->hasValidationPlanPrivileges,
            'validationPlanMessage' => $this->validationPlanMessage,
        ]);
    }
}
