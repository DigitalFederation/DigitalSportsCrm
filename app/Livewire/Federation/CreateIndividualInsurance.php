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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class CreateIndividualInsurance extends Component
{
    public $federation;
    public $availablePackages;
    public $entities;

    // Form fields
    public $selectedPackageId = '';
    public $selectedEntityId = '';
    public $selectedIndividuals = [];

    // Dynamic data
    public $selectedPackage = null;
    public $availableIndividuals = [];
    public $loadingIndividuals = false;

    // UI state
    public $isSubmitting = false;

    protected $rules = [
        'selectedPackageId' => 'required|exists:membership_packages,id',
        'selectedEntityId' => 'nullable|exists:entity,id',
        'selectedIndividuals' => 'required|array|min:1',
        'selectedIndividuals.*' => 'required|exists:individual,id',
    ];

    protected function messages()
    {
        return [
            'selectedPackageId.required' => __('federation.individual_insurances.validation.package_required'),
            'selectedPackageId.exists' => __('federation.individual_insurances.validation.package_invalid'),
            'selectedEntityId.required' => __('federation.individual_insurances.validation.entity_required'),
            'selectedEntityId.nullable' => __('federation.individual_insurances.validation.entity_optional'),
            'selectedEntityId.exists' => __('federation.individual_insurances.validation.entity_invalid'),
            'selectedIndividuals.required' => __('federation.individual_insurances.validation.individuals_required'),
            'selectedIndividuals.min' => __('federation.individual_insurances.validation.individuals_min'),
        ];
    }

    public function mount()
    {
        $user = Auth::user();
        if (! $user || ! $user->isFederation()) {
            abort(403, __('federation.common.unauthorized_action'));
        }

        $this->federation = $user->federations()->first();
        if (! $this->federation) {
            abort(403, __('federation.common.federation_not_found'));
        }

        // Get available insurance-only packages including federation-managed ones
        $this->availablePackages = $this->getAvailableInsurancePackagesForIndividuals();

        // Get entities under this federation
        $this->entities = Entity::whereHas('federations', function ($query) {
            $query->select('federation.id')
                ->where('federation.id', $this->federation->id)
                ->where('entity_federation.status_class', ActiveEntityFederationState::class);
        })
            ->orderBy('name')
            ->get();
    }

    public function updatedSelectedPackageId($value)
    {
        if ($value) {
            $this->selectedPackage = MembershipPackage::with(['insurancePlans', 'affiliationPlans'])
                ->find($value);
        } else {
            $this->selectedPackage = null;
        }

        // Reset individuals when package changes
        $this->selectedIndividuals = [];

        // Load individuals based on entity selection
        if ($this->selectedEntityId) {
            $this->loadEntityIndividuals();
        } elseif ($value) {
            // If no entity selected but package is selected, load federation individuals
            $this->loadFederationIndividuals();
        }
    }

    public function updatedSelectedEntityId($value)
    {
        if ($value && $this->selectedPackageId) {
            $this->loadEntityIndividuals();
        } else {
            $this->availableIndividuals = [];
            $this->selectedIndividuals = [];
        }
    }

    public function loadEntityIndividuals()
    {
        if (! $this->selectedPackageId) {
            $this->availableIndividuals = [];

            return;
        }

        // If no entity selected, load all individuals from federation
        if (! $this->selectedEntityId) {
            $this->loadFederationIndividuals();

            return;
        }

        $this->loadingIndividuals = true;
        $this->selectedIndividuals = [];

        $entity = Entity::find($this->selectedEntityId);
        $package = MembershipPackage::find($this->selectedPackageId);

        if (! $entity || ! $package) {
            $this->availableIndividuals = [];
            $this->loadingIndividuals = false;

            return;
        }

        // Get eligible individuals (those without active insurance for this package's plans)
        $insurancePlanIds = $package->insurancePlans->pluck('id')->toArray();

        $this->availableIndividuals = Individual::query()
            ->whereHas('entities', function ($query) use ($entity) {
                $query->where('entity_id', $entity->id);
            })
            ->when(! empty($insurancePlanIds), function ($query) use ($insurancePlanIds) {
                // Exclude individuals who already have active insurance from this package
                $query->whereNotExists(function ($subQuery) use ($insurancePlanIds) {
                    $subQuery->select('id')
                        ->from('insurances')
                        ->whereColumn('insurances.member_id', 'individual.id')
                        ->where('insurances.member_type', 'individual')
                        ->whereIn('insurances.insurance_plan_id', $insurancePlanIds)
                        ->where('insurances.end_date', '>=', now());
                });
            })
            ->select(['id', 'name', 'member_code', 'email'])
            ->orderBy('name')
            ->get();

        $this->loadingIndividuals = false;
    }

    public function loadFederationIndividuals()
    {
        $this->loadingIndividuals = true;
        $this->selectedIndividuals = [];

        $package = MembershipPackage::find($this->selectedPackageId);
        if (! $package) {
            $this->availableIndividuals = [];
            $this->loadingIndividuals = false;

            return;
        }

        // Get eligible individuals from the entire federation
        $insurancePlanIds = $package->insurancePlans->pluck('id')->toArray();

        $this->availableIndividuals = Individual::query()
            ->whereHas('entities', function ($query) {
                $query->whereHas('federations', function ($fedQuery) {
                    $fedQuery->where('federation.id', $this->federation->id);
                });
            })
            ->when(! empty($insurancePlanIds), function ($query) use ($insurancePlanIds) {
                // Exclude individuals who already have active insurance from this package
                $query->whereNotExists(function ($subQuery) use ($insurancePlanIds) {
                    $subQuery->select('id')
                        ->from('insurances')
                        ->whereColumn('insurances.member_id', 'individual.id')
                        ->where('insurances.member_type', 'individual')
                        ->whereIn('insurances.insurance_plan_id', $insurancePlanIds)
                        ->where('insurances.end_date', '>=', now());
                });
            })
            ->select(['id', 'name', 'member_code', 'email'])
            ->orderBy('name')
            ->get();

        $this->loadingIndividuals = false;
    }

    public function toggleAllIndividuals()
    {
        if (count($this->selectedIndividuals) === count($this->availableIndividuals)) {
            $this->selectedIndividuals = [];
        } else {
            $this->selectedIndividuals = $this->availableIndividuals->pluck('id')->toArray();
        }
    }

    public function submit()
    {
        $this->validate();
        $this->isSubmitting = true;

        try {
            DB::beginTransaction();

            $package = MembershipPackage::with(['affiliationPlans', 'insurancePlans'])
                ->findOrFail($this->selectedPackageId);

            $entity = null;
            if ($this->selectedEntityId) {
                $entity = Entity::findOrFail($this->selectedEntityId);

                // Verify that this entity belongs to this federation
                if (! $entity->federations()->where('federation.id', $this->federation->id)
                    ->where('entity_federation.status_class', ActiveEntityFederationState::class)->exists()) {
                    DB::rollBack();
                    $this->isSubmitting = false;
                    $this->addError('selectedEntityId', __('federation.individual_insurances.validation.entity_not_in_federation'));

                    return;
                }
            }

            // Verify the package is insurance-only (has insurance plans but NO affiliation plans)
            if ($package->insurancePlans->isEmpty() || $package->affiliationPlans->isNotEmpty()) {
                DB::rollBack();
                $this->isSubmitting = false;
                $this->addError('selectedPackageId', __('federation.individual_insurances.validation.package_must_be_insurance_only'));

                return;
            }

            // Check validation plan privileges for entity member insurance requests
            /*
            $validationPlanService = resolve(ValidationPlanPrivilegeService::class);

            if (! $validationPlanService->canRequestInsurance($entity)) {
                $reason = $validationPlanService->getValidationPlanReason($entity, 'insurance');
                DB::rollBack();
                $this->isSubmitting = false;
                $this->addError('general', __('memberships.entity_member_insurance_not_authorized', ['reason' => $reason]));
                return;
            }
            */

            $successCount = 0;
            $failedCount = 0;

            foreach ($this->selectedIndividuals as $individualId) {
                try {
                    $individual = Individual::findOrFail($individualId);

                    // If entity is selected, verify individual belongs to it
                    if ($entity && ! $individual->entities()->where('entity_id', $entity->id)->exists()) {
                        $failedCount++;

                        continue;
                    }

                    // If no entity selected, verify individual belongs to federation
                    if (! $entity) {
                        $belongsToFederation = $individual->entities()
                            ->whereHas('federations', function ($query) {
                                $query->where('federation.id', $this->federation->id);
                            })
                            ->exists();

                        if (! $belongsToFederation) {
                            $failedCount++;

                            continue;
                        }
                    }

                    // Get the first insurance plan from the package (insurance-only packages typically have one)
                    $insurancePlan = $package->insurancePlans->first();

                    if (! $insurancePlan) {
                        $failedCount++;

                        continue;
                    }

                    // Check for existing active insurance for this individual and plan
                    $existingInsurance = Insurance::where('member_type', 'individual')
                        ->where('member_id', $individual->id)
                        ->where('insurance_plan_id', $insurancePlan->id)
                        ->where('end_date', '>=', now())
                        ->exists();

                    if ($existingInsurance) {
                        $failedCount++;

                        continue;
                    }

                    // Create insurance record
                    $requesterType = $entity ? 'entity' : 'federation';
                    $requesterId = $entity ? $entity->id : $this->federation->id;
                    $requestType = $entity ? 'entity_group' : 'federation_facilitated';

                    // Get the appropriate fee based on who's paying
                    $individualFee = $entity ? 0 : ($insurancePlan->individual_fee ?? 0);
                    $entityFee = $entity ? ($insurancePlan->entity_fee ?? 0) : 0;

                    Insurance::create([
                        'member_type' => 'individual', // Use morph map key
                        'member_id' => $individual->id,
                        'insurance_plan_id' => $insurancePlan->id,
                        'start_date' => now(),
                        'end_date' => MemberSubscription::calculateAnnualEndDate(),
                        'status_class' => 'Domain\\Insurance\\States\\ActiveInsuranceState',
                        'requester_type' => $requesterType, // Use morph map key
                        'requester_id' => $requesterId, // Federation is requester, but Individual gets documents if no entity
                        'request_type' => $requestType, // entity_group or federation_facilitated
                        'member_subscription_id' => null, // Not part of a subscription package
                        'individual_fee' => $individualFee,
                        'entity_fee' => $entityFee,
                        'is_external' => false,
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    Log::error('Failed to create individual insurance', [
                        'individual_id' => $individualId,
                        'entity_id' => $entity->id,
                        'package_id' => $package->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            if ($successCount > 0 && $failedCount === 0) {
                // All successful
                $entityName = $entity ? $entity->name : $this->federation->name;
                $this->dispatch('insurance-created', [
                    'message' => __('federation.individual_insurances.success_all', [
                        'count' => $successCount,
                        'entity' => $entityName,
                    ]),
                ]);

                return redirect()->route('federation.individual-insurances.index')
                    ->with('success', __('federation.individual_insurances.success_all', [
                        'count' => $successCount,
                        'entity' => $entityName,
                    ]));
            } elseif ($successCount > 0 && $failedCount > 0) {
                // Partial success
                $entityName = $entity ? $entity->name : $this->federation->name;

                return redirect()->route('federation.individual-insurances.index')
                    ->with('warning', __('federation.individual_insurances.partial_success', [
                        'success' => $successCount,
                        'failed' => $failedCount,
                        'entity' => $entityName,
                    ]));
            } else {
                // All failed
                $this->isSubmitting = false;
                $this->addError('general', __('federation.individual_insurances.all_failed', [
                    'count' => $failedCount,
                ]));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process individual insurance subscriptions', [
                'error' => $e->getMessage(),
                'entity_id' => $this->selectedEntityId,
                'federation_id' => $this->federation->id,
                'package_id' => $this->selectedPackageId,
            ]);

            $this->isSubmitting = false;
            $this->addError('general', __('federation.individual_insurances.error_processing'));
        }
    }

    private function getAvailableInsurancePackagesForIndividuals(): Collection
    {
        return MembershipPackage::where('is_active', true)
            ->whereIn('target_type', [MembershipTargetType::INDIVIDUAL, MembershipTargetType::BOTH])
            ->whereHas('federations', function ($query) {
                $query->where('federation.id', $this->federation->id);
            })
            ->where(function ($query) {
                // Include packages that are entity_managed OR federation_managed
                $query->whereJsonContains('distribution_methods', 'entity_managed')
                    ->orWhereJsonContains('distribution_methods', 'federation_managed');
            })
            ->with(['insurancePlans', 'affiliationPlans'])
            ->get()
            ->filter(function ($package) {
                // Only packages that have insurance plans and NO affiliation plans
                return $package->insurancePlans->isNotEmpty() && $package->affiliationPlans->isEmpty();
            });
    }

    public function render()
    {
        return view('livewire.federation.create-individual-insurance');
    }
}
