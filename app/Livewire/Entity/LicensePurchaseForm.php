<?php

namespace App\Livewire\Entity;

use App\Models\Committee;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Actions\CalculateLicensePriceAction;
use Domain\Licenses\Actions\GetEligibleMembersForLicenseAction;
use Domain\Licenses\Models\License;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Memberships\Services\ValidationPlanPrivilegeService;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;

class LicensePurchaseForm extends Component
{
    public Entity $entity;
    public Federation $federation;

    #[Url(as: 'committee')]
    public $committee; // Committee filter

    public $licenses = [];
    public $selectedLicenseId;
    public $selectedLicense;
    public $licenseType; // 'entity' or 'members' - determined by URL parameter
    public $selectedIndividualIds = []; // For member purchase (one or more)
    public $calculatedPrice = 0;
    public $totalPrice = 0;
    public $entityMembers = [];
    public $licenseSearch = '';
    public $memberSearch = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $canSubmit = false;
    public $isInternational = null; // null = infer from committee, true/false = explicit
    public $memberEligibility = [];
    public $showIneligibleMembers = true;
    public $licenseRequirements = [];
    public $entityHasEntityLicense = false;

    public function mount(Entity $entity, ?Federation $federation = null, $committee = null, $type = null, $isInternational = false)
    {
        $this->entity = $entity;
        $this->federation = $federation ?? $entity->federations()->first();
        $this->committee = $committee;
        $this->isInternational = $isInternational;
        $this->entityHasEntityLicense = $entity->hasActiveEntityLicense();

        // Set license type from URL parameter, default to 'entity' if not provided
        // If requesting 'members' but entity doesn't have an active entity license, force 'entity'
        if ($type === 'members' && ! $this->entityHasEntityLicense) {
            $this->licenseType = 'entity';
        } else {
            $this->licenseType = in_array($type, ['entity', 'members']) ? $type : 'entity';
        }

        if ($this->federation) {
            $this->loadLicenses();
            $this->loadEntityMembers();
        }
    }

    public function getEntityHasActiveAffiliationProperty()
    {
        return $this->entity->hasActiveAffiliation();
    }

    public function render()
    {
        return view('livewire.entity.license-purchase-form');
    }

    public function updatedSelectedLicenseId($licenseId)
    {
        $this->calculatedPrice = 0;
        $this->selectedLicense = null;
        $this->memberEligibility = [];
        $this->licenseRequirements = [];
        $this->updateTotalPrice();

        if ($licenseId) {
            // Use the same query as loadLicenses to ensure we can find the license
            $query = License::query();

            // Apply the same filters as in loadLicenses
            // Priority: explicit isInternational > committee-based inference
            if ($this->isInternational !== null) {
                if ($this->isInternational) {
                    $query->withoutGlobalScope(ExcludeInternationalScope::class)
                        ->whereHas('committee', fn ($q) => $q->where('is_international', true));
                } else {
                    $query->whereHas('committee', fn ($q) => $q->where('is_international', false));
                }
            } elseif ($this->committee && ($committeeModel = Committee::where('code', strtoupper($this->committee))->first())) {
                if ($committeeModel->is_international) {
                    // International committee - only international licenses
                    $query->withoutGlobalScope(ExcludeInternationalScope::class)
                        ->whereHas('committee', fn ($q) => $q->where('is_international', true));
                } else {
                    // National committee - only national licenses
                    $query->whereHas('committee', fn ($q) => $q->where('is_international', false));
                }
            } else {
                // No committee specified - show both national and international licenses
                // This is important for member license purchases where no committee filter is applied
                $query->withoutGlobalScope(ExcludeInternationalScope::class);
            }

            $this->selectedLicense = $query->with(['requiredCertifications', 'type'])->find($licenseId);

            if ($this->selectedLicense) {
                $calculatePriceAction = new CalculateLicensePriceAction;
                // Always use Entity pricing since entity is the one purchasing
                $this->calculatedPrice = $calculatePriceAction($this->selectedLicense, Entity::class);
                $this->updateTotalPrice();

                // Get license requirements for display
                $eligibilityAction = new GetEligibleMembersForLicenseAction;
                $this->licenseRequirements = $eligibilityAction->getLicenseRequirements($this->selectedLicense);

                // Check member eligibility if this is for member licenses
                if ($this->licenseType === 'members') {
                    $this->checkMemberEligibility();
                }
            }
        }

        $this->updateCanSubmit();
    }

    public function updatedSelectedIndividualIds()
    {
        $this->updateTotalPrice();
        $this->updateCanSubmit();
    }

    private function updateCanSubmit()
    {
        $this->canSubmit = $this->canPurchase();
    }

    public function updatedMemberSearch()
    {
        $this->loadEntityMembers();
    }

    public function updatedCommittee()
    {
        $this->loadLicenses();
        $this->resetSelection();
    }

    public function sort($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->loadEntityMembers();
    }

    private function resetSelection()
    {
        $this->selectedIndividualIds = [];
        $this->selectedLicenseId = null;
        $this->selectedLicense = null;
        $this->calculatedPrice = 0;
        $this->totalPrice = 0;
        $this->updateCanSubmit();
    }

    private function updateTotalPrice()
    {
        if (! $this->calculatedPrice) {
            $this->totalPrice = 0;

            return;
        }

        if ($this->licenseType === 'entity') {
            // Entity purchasing for itself
            $this->totalPrice = $this->calculatedPrice;
        } elseif ($this->licenseType === 'members' && count($this->selectedIndividualIds) > 0) {
            // Member licenses - calculate total for selected members
            $this->totalPrice = $this->calculatedPrice * count($this->selectedIndividualIds);
        } else {
            $this->totalPrice = 0;
        }
    }

    private function loadLicenses()
    {
        if (! $this->federation) {
            $this->licenses = [];

            return;
        }

        $query = License::query();

        // Handle international license filtering
        // Priority: explicit isInternational > committee-based inference
        if ($this->isInternational !== null) {
            // Explicit isInternational set - use it directly
            if ($this->isInternational) {
                $query->withoutGlobalScope(ExcludeInternationalScope::class)
                    ->whereHas('committee', fn ($q) => $q->where('is_international', true));
            } else {
                $query->whereHas('committee', fn ($q) => $q->where('is_international', false));
            }
        } elseif ($this->committee && ($committeeModel = Committee::where('code', strtoupper($this->committee))->first())) {
            if ($committeeModel->is_international) {
                // International committee can only see international licenses
                $query->withoutGlobalScope(ExcludeInternationalScope::class)
                    ->whereHas('committee', fn ($q) => $q->where('is_international', true));
            } else {
                // National committee can only see national licenses
                $query->whereHas('committee', fn ($q) => $q->where('is_international', false));
            }
        } else {
            // No committee specified - remove the international scope to show both national and international
            // This is crucial for member license purchases that don't have a committee filter
            $query->withoutGlobalScope(ExcludeInternationalScope::class);
        }

        if ($this->licenseType === 'entity') {
            // Show licenses where requester_model includes Entity
            // AND the license type is entity (not individual)
            $query->forRequesterType(Entity::class)
                ->whereHas('type', function ($q) {
                    $q->where(function ($subQ) {
                        $subQ->where('is_individual', false)
                            ->orWhereNull('is_individual');
                    });
                });
        } else {
            // For members section, show licenses where:
            // requester_model includes Entity AND type is Individual
            // AND the license allows entity group requests
            // This means entities can purchase these individual licenses for their members
            $query->forRequesterType(Entity::class)
                ->whereHas('type', function ($q) {
                    $q->where('is_individual', true);
                })
                ->where('allow_entity_group_request', true);

            // Filter to only show member licenses for sports where entity has active entity license
            // Note: This filter is optional - if the entity has no active licenses,
            // we'll show all available member licenses they can purchase
            $activeSportIds = $this->entity->licenses()
                ->where('license_attributed.status_class', ActiveLicenseAttributedState::class)
                ->whereHas('license', function ($q) {
                    $q->whereNotNull('sport_id')
                        ->whereHas('type', function ($typeQuery) {
                            $typeQuery->where('is_individual', '!=', true)
                                ->orWhereNull('is_individual');
                        });
                })
                ->join('license', 'license_attributed.license_id', '=', 'license.id')
                ->pluck('license.sport_id')
                ->filter()
                ->unique();

            // Strict filtering: only show member licenses for sports where entity has active entity license
            // Licenses without sport_id (null) are always allowed
            if ($activeSportIds->isNotEmpty()) {
                // Show licenses for active sports OR licenses without sport restriction
                $query->where(function ($q) use ($activeSportIds) {
                    $q->whereIn('sport_id', $activeSportIds)
                        ->orWhereNull('sport_id');
                });
            } else {
                // No active entity licenses = only show licenses without sport restriction
                $query->whereNull('sport_id');
            }
        }

        // Only show active licenses
        $query->where('active', true);

        // Apply federation filtering - only show licenses offered by entity's federations
        $federationIds = $this->entity->federations()
            ->where('entity_federation.status_class', 'Domain\\Entities\\States\\ActiveEntityFederationState')
            ->pluck('federation_id');

        $query->forFederationEntities($federationIds);

        // Apply committee filter if specified
        if ($this->committee) {
            $query->whereHas('committee', function (Builder $q) {
                $q->where('code', strtoupper($this->committee));
            });
        }

        $this->licenses = $query->with(['committee', 'professionalRole', 'sport'])
            ->orderBy('name')
            ->get();

        // Calculate price for each license
        $calculatePriceAction = new CalculateLicensePriceAction;
        // For entity tab, use entity pricing; for members tab, use entity pricing (since entity is purchasing)
        $modelClass = Entity::class;
        foreach ($this->licenses as $license) {
            $license->calculated_price = $calculatePriceAction($license, $modelClass);
        }
    }

    private function loadEntityMembers()
    {
        // Get all individuals associated with this entity
        $query = $this->entity->individuals();

        // Apply search filter
        if (! empty($this->memberSearch)) {
            $search = '%' . $this->memberSearch . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('surname', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('member_number', 'like', $search);
            });
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        // Eager load media for profile images to prevent lazy loading
        $this->entityMembers = $query->with('media')->get();

        // Check member eligibility if a license is selected and this is for member licenses
        if ($this->selectedLicense && $this->licenseType === 'members') {
            $this->checkMemberEligibility();
        }
    }

    public function getFilteredLicensesProperty()
    {
        if (empty($this->licenseSearch)) {
            return $this->licenses;
        }

        return collect($this->licenses)->filter(function ($license) {
            $search = strtolower($this->licenseSearch);

            $committeeName = $license->relationLoaded('committee') && $license->committee
                ? strtolower($license->committee->name)
                : '';
            $roleName = $license->relationLoaded('professionalRole') && $license->professionalRole
                ? strtolower($license->professionalRole->name)
                : '';

            return str_contains(strtolower($license->name), $search) ||
                str_contains(strtolower($license->license_code ?? ''), $search) ||
                str_contains($committeeName, $search) ||
                str_contains($roleName, $search);
        });
    }

    public function toggleAllMembers()
    {
        if (count($this->selectedIndividualIds) === count($this->entityMembers)) {
            $this->selectedIndividualIds = [];
        } else {
            $this->selectedIndividualIds = $this->entityMembers->pluck('id')->toArray();
        }
        $this->updateTotalPrice();
    }

    public function canPurchase()
    {
        // Check if entity has active affiliation
        if (! $this->entityHasActiveAffiliation) {
            return false;
        }

        // Check validation plan privileges
        $validationPlanService = resolve(ValidationPlanPrivilegeService::class);

        if ($this->licenseType === 'entity') {
            // Entity purchasing for itself
            if (! $validationPlanService->canRequestLicense($this->entity)) {
                return false;
            }
        } elseif ($this->licenseType === 'members') {
            // Entity purchasing for members
            if (! $validationPlanService->canRequestLicenseForMembers($this->entity)) {
                return false;
            }
        }

        // Check if license is selected
        if (! $this->selectedLicenseId) {
            return false;
        }

        // For price check, allow 0 price (free licenses) but ensure calculatedPrice is set (not null)
        if ($this->calculatedPrice === null) {
            return false;
        }

        if ($this->licenseType === 'entity') {
            // Entity purchasing for itself - just need license selected and price calculated
            return true;
        } elseif ($this->licenseType === 'members') {
            // Member licenses - need at least one member selected
            return ! empty($this->selectedIndividualIds);
        }

        return false;
    }

    public function getValidationPlanMessageProperty()
    {
        $validationPlanService = resolve(ValidationPlanPrivilegeService::class);

        if ($this->licenseType === 'entity') {
            return $validationPlanService->getValidationPlanReason($this->entity, 'license');
        } elseif ($this->licenseType === 'members') {
            return $validationPlanService->getValidationPlanReason($this->entity, 'entity_member_licenses');
        }

        return null;
    }

    public function getSelectedMembersProperty()
    {
        if (! empty($this->selectedIndividualIds)) {
            return $this->entityMembers->whereIn('id', $this->selectedIndividualIds);
        }

        return collect();
    }

    public function checkMemberEligibility()
    {
        if (! $this->selectedLicense || $this->entityMembers->isEmpty()) {
            $this->memberEligibility = [];

            return;
        }

        $eligibilityAction = new GetEligibleMembersForLicenseAction;
        $eligibilityData = $eligibilityAction($this->selectedLicense, $this->entity, $this->entityMembers);

        $this->memberEligibility = [];
        foreach ($eligibilityData as $data) {
            $this->memberEligibility[$data['individual']->id] = [
                'is_eligible' => $data['is_eligible'],
                'missing_certifications' => $data['missing_certifications'],
                'missing_documents' => $data['missing_documents'],
                'has_active_affiliation' => $data['has_active_affiliation'],
                'eligibility_message' => $data['eligibility_message'],
            ];
        }

        // Automatically deselect ineligible members
        $eligibleIds = collect($this->memberEligibility)
            ->filter(function ($eligibility) {
                return $eligibility['is_eligible'];
            })
            ->keys()
            ->toArray();

        $this->selectedIndividualIds = array_intersect($this->selectedIndividualIds, $eligibleIds);
        $this->updateTotalPrice();
    }

    public function toggleShowIneligibleMembers()
    {
        $this->showIneligibleMembers = ! $this->showIneligibleMembers;
    }

    public function getFilteredMembersProperty()
    {
        $members = $this->entityMembers;

        if (! $this->showIneligibleMembers && ! empty($this->memberEligibility)) {
            $members = $members->filter(function ($member) {
                return $this->memberEligibility[$member->id]['is_eligible'] ?? true;
            });
        }

        return $members;
    }

    public function getEligibleMemberCountProperty()
    {
        if (empty($this->memberEligibility)) {
            return $this->entityMembers->count();
        }

        return collect($this->memberEligibility)
            ->filter(function ($eligibility) {
                return $eligibility['is_eligible'];
            })
            ->count();
    }

    public function getIneligibleMemberCountProperty()
    {
        if (empty($this->memberEligibility)) {
            return 0;
        }

        return collect($this->memberEligibility)
            ->filter(function ($eligibility) {
                return ! $eligibility['is_eligible'];
            })
            ->count();
    }
}
