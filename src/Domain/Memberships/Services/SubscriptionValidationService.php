<?php

namespace Domain\Memberships\Services;

use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Insurance\Models\Insurance;
use Domain\Insurance\States\ActiveInsuranceState;
use Domain\Insurance\States\PendingPaymentInsuranceState;
use Domain\Memberships\Models\Affiliation;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\States\ActiveAffiliationState;

class SubscriptionValidationService
{
    /**
     * Validate if a member can subscribe to a package according to business rules
     *
     * @param  Entity|Individual  $member
     * @return array ['valid' => bool, 'error' => string|null]
     */
    public function validateSubscription(Entity|Individual $member, MembershipPackage $package): array
    {
        if (! $this->isSupportedMember($member)) {
            return [
                'valid' => false,
                'error' => __('memberships.invalid_member_type'),
            ];
        }

        // Load package relationships
        $package->load(['affiliationPlans', 'insurancePlans']);

        // Determine if this is an affiliation package or insurance-only package
        $hasAffiliation = $package->affiliationPlans->isNotEmpty();
        $hasInsurance = $package->insurancePlans->isNotEmpty();

        // Insurance-only package validation
        if (! $hasAffiliation && $hasInsurance) {
            return $this->validateInsuranceOnlyPackage($member, $package);
        }

        // Affiliation package validation (with or without insurance)
        if ($hasAffiliation) {
            return $this->validateAffiliationPackage($member, $package);
        }

        return [
            'valid' => true,
            'error' => null,
        ];
    }

    /**
     * Validate insurance-only package subscription
     * Rules:
     * 1. For entities or entity-managed subscriptions: Must have an active validation affiliation
     * 2. For individual self-subscription: Allow as part of initial subscription
     * 3. Cannot have the same insurance plan already active or pending
     */
    private function validateInsuranceOnlyPackage(Entity|Individual $member, MembershipPackage $package): array
    {
        // Different validation rules based on who is subscribing
        // Individual self-subscription: More lenient (can get insurance with first package)
        // Entity subscribing individuals: Strict (requires validation plan)

        // Check if this is an individual self-subscription (not entity-managed)
        $isIndividualSelfSubscription = $member instanceof Individual && ! auth()->user()?->isEntity();

        if (! $isIndividualSelfSubscription) {
            // This is NOT an individual self-subscription
            // Either: Entity subscribing individual OR entity subscribing itself
            // Check for active validation affiliation (strict requirement)
            $validationPlanService = resolve(ValidationPlanPrivilegeService::class);
            if (! $validationPlanService->canRequestInsurance($member)) {
                $reason = $validationPlanService->getValidationPlanReason($member, 'insurance');

                return [
                    'valid' => false,
                    'error' => $reason ?? __('memberships.no_validation_affiliation_for_insurance'),
                ];
            }
        }
        // If we reach here and it's individual self-subscription, we skip the validation check
        // and only check for duplicates

        // Check for duplicate insurance plans (applies to everyone)
        $duplicateCheck = $this->checkDuplicateInsurancePlans($member, $package);
        if (! $duplicateCheck['valid']) {
            return $duplicateCheck;
        }

        return [
            'valid' => true,
            'error' => null,
        ];
    }

    /**
     * Validate affiliation package subscription
     * Rules:
     * 1. If no active validation affiliation exists → allow subscription
     * 2. If no active insurance exists → allow subscription
     * 3. Cannot have duplicate affiliation plans
     * 4. Cannot have duplicate insurance plans
     * 5. NEW: If package contains ONLY non-validation plans AND member is Individual
     *    → Individual must have an active validation plan
     */
    private function validateAffiliationPackage(Entity|Individual $member, MembershipPackage $package): array
    {
        // Check for existing validation affiliations
        $hasValidationAffiliation = $this->hasActiveValidationAffiliation($member);

        // PACKAGE-DEPENDENT VALIDATION: For Individuals subscribing through entities
        // This validation is only enforced when an entity is subscribing individuals
        // Individual self-subscription is allowed without this restriction
        if ($member instanceof Individual && auth()->user()?->isEntity()) {
            $hasValidationPlanInPackage = $package->affiliationPlans->contains(fn ($plan) => $plan->is_validation_plan);

            // VALIDATION LOGIC BASED ON PACKAGE CONTENT:
            // 1. If package HAS validation plans: Allow anyone (they're getting a base membership)
            // 2. If package has NO validation plans (add-ons): Require active validation plan
            if (! $hasValidationPlanInPackage && ! $hasValidationAffiliation) {
                return [
                    'valid' => false,
                    'error' => __('memberships.validation_plan_required_for_non_validation_packages'),
                ];
            }
        }

        // Check for duplicate affiliation plans
        $duplicateAffiliationCheck = $this->checkDuplicateAffiliationPlans($member, $package);
        if (! $duplicateAffiliationCheck['valid']) {
            return $duplicateAffiliationCheck;
        }

        // If package has insurance plans, check for duplicates
        if ($package->insurancePlans->isNotEmpty()) {
            $hasActiveInsurance = $this->hasActiveInsurance($member);

            // According to PM rules: if no active insurance exists → allow subscription
            // But we still need to check for duplicate insurance plans
            $duplicateInsuranceCheck = $this->checkDuplicateInsurancePlans($member, $package);
            if (! $duplicateInsuranceCheck['valid']) {
                return $duplicateInsuranceCheck;
            }
        }

        return [
            'valid' => true,
            'error' => null,
        ];
    }

    /**
     * Check if member has any active validation affiliation
     */
    private function hasActiveValidationAffiliation(Entity|Individual $member): bool
    {
        return Affiliation::where('member_type', $this->getMorphAlias($member))
            ->where('member_id', $member->id)
            ->where('end_date', '>=', now())
            ->whereIn('status_class', [ActiveAffiliationState::class])
            ->whereHas('memberSubscription.membershipPackage.affiliationPlans', function ($query) {
                $query->where('is_validation_plan', true);
            })
            ->exists();
    }

    /**
     * Check if member has any active insurance
     */
    private function hasActiveInsurance(Entity|Individual $member): bool
    {
        $memberType = $member instanceof Entity ? 'entity' : 'individual';

        return Insurance::where('member_type', $memberType)
            ->where('member_id', $member->id)
            ->where('end_date', '>=', now())
            ->whereIn('status_class', [
                ActiveInsuranceState::class,
                PendingPaymentInsuranceState::class,
            ])
            ->exists();
    }

    /**
     * Check for duplicate affiliation plans
     */
    private function checkDuplicateAffiliationPlans(Entity|Individual $member, MembershipPackage $package): array
    {
        $newAffiliationPlanIds = $package->affiliationPlans->pluck('id');

        if ($newAffiliationPlanIds->isEmpty()) {
            return ['valid' => true, 'error' => null];
        }

        // Get all active affiliations for this member
        $activeAffiliations = Affiliation::where('member_type', $this->getMorphAlias($member))
            ->where('member_id', $member->id)
            ->where('end_date', '>=', now())
            ->whereIn('status_class', [ActiveAffiliationState::class])
            ->with(['memberSubscription.membershipPackage.affiliationPlans'])
            ->get();

        // Get affiliation plan IDs from the member subscription packages
        $existingAffiliationPlanIds = $activeAffiliations
            ->flatMap(function ($affiliation) {
                if ($affiliation->memberSubscription && $affiliation->memberSubscription->membershipPackage) {
                    return $affiliation->memberSubscription->membershipPackage->affiliationPlans
                        ->where('federation_id', $affiliation->federation_id)
                        ->pluck('id');
                }

                return collect();
            })
            ->unique();

        // Check for duplicates
        $duplicates = $newAffiliationPlanIds->intersect($existingAffiliationPlanIds);
        $newPlans = $newAffiliationPlanIds->diff($existingAffiliationPlanIds);

        // Only return error if ALL plans are duplicates (nothing new to subscribe to)
        // If there are some duplicates but also new plans - allow subscription
        // The CreateAffiliationAction will skip creating affiliations for duplicate plans
        if ($duplicates->isNotEmpty() && $newPlans->isEmpty()) {
            $duplicatePlans = $package->affiliationPlans->whereIn('id', $duplicates);
            $planNames = $duplicatePlans->pluck('name')->implode(', ');

            return [
                'valid' => false,
                'error' => __('memberships.all_affiliation_plans_already_active', ['plans' => $planNames]),
            ];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Check for duplicate insurance plans
     */
    private function checkDuplicateInsurancePlans(Entity|Individual $member, MembershipPackage $package): array
    {
        $newInsurancePlanIds = $package->insurancePlans->pluck('id');

        if ($newInsurancePlanIds->isEmpty()) {
            return ['valid' => true, 'error' => null];
        }

        $memberType = $member instanceof Entity ? 'entity' : 'individual';

        // Get all active or pending insurances for this member
        $activeInsurances = Insurance::where('member_type', $memberType)
            ->where('member_id', $member->id)
            ->where('end_date', '>=', now())
            ->whereIn('status_class', [
                ActiveInsuranceState::class,
                PendingPaymentInsuranceState::class,
            ])
            ->get();

        $existingInsurancePlanIds = $activeInsurances->pluck('insurance_plan_id');

        // Check for duplicates
        $duplicates = $newInsurancePlanIds->intersect($existingInsurancePlanIds);

        if ($duplicates->isNotEmpty()) {
            $duplicatePlans = $package->insurancePlans->whereIn('id', $duplicates);
            $planNames = $duplicatePlans->pluck('name')->implode(', ');

            return [
                'valid' => false,
                'error' => __('memberships.duplicate_insurance_plans', ['plans' => $planNames]),
            ];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Check if member type is supported
     */
    private function isSupportedMember(Entity|Individual $member): bool
    {
        return $member instanceof Entity || $member instanceof Individual;
    }

    /**
     * Get morph alias for member model
     */
    private function getMorphAlias(Entity|Individual $member): string
    {
        return match (true) {
            $member instanceof Entity => 'entity',
            $member instanceof Individual => 'individual',
            default => get_class($member),
        };
    }

    /**
     * Get a summary of member's current subscriptions
     */
    public function getMemberSubscriptionSummary(Entity|Individual $member): array
    {
        if (! $this->isSupportedMember($member)) {
            return [
                'has_validation_affiliation' => false,
                'has_active_insurance' => false,
                'active_affiliation_plans' => [],
                'active_insurance_plans' => [],
            ];
        }

        $memberType = $this->getMorphAlias($member);

        // Get active affiliations
        $activeAffiliations = Affiliation::where('member_type', $memberType)
            ->where('member_id', $member->id)
            ->where('end_date', '>=', now())
            ->whereIn('status_class', [ActiveAffiliationState::class])
            ->with(['memberSubscription.membershipPackage.affiliationPlans'])
            ->get();

        // Get active insurances
        $activeInsurances = Insurance::where('member_type', $memberType)
            ->where('member_id', $member->id)
            ->where('end_date', '>=', now())
            ->whereIn('status_class', [
                ActiveInsuranceState::class,
                PendingPaymentInsuranceState::class,
            ])
            ->with('insurancePlan')
            ->get();

        $hasValidationAffiliation = $activeAffiliations->filter(function ($affiliation) {
            // Use the computed attribute that gets the affiliation plan from the subscription
            $plan = $affiliation->getAffiliationPlanAttribute();

            return $plan && $plan->is_validation_plan;
        })->isNotEmpty();

        return [
            'has_validation_affiliation' => $hasValidationAffiliation,
            'has_active_insurance' => $activeInsurances->isNotEmpty(),
            'active_affiliation_plans' => $activeAffiliations->map(function ($affiliation) {
                $plan = $affiliation->getAffiliationPlanAttribute();

                return $plan ? $plan->name : null;
            })->filter()->unique()->values()->toArray(),
            'active_insurance_plans' => $activeInsurances->pluck('insurancePlan.name')->filter()->unique()->values()->toArray(),
        ];
    }
}
