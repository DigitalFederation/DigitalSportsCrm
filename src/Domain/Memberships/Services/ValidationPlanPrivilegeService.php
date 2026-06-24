<?php

namespace Domain\Memberships\Services;

use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Enums\ValidationPlanPrivilegeType;
use Domain\Memberships\Models\AffiliationPlan;
use Domain\Memberships\Models\MemberSubscription;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ValidationPlanPrivilegeService
{
    public function canRequestInsurance(Model $member): bool
    {
        return $this->hasValidationPlanPrivilege($member, ValidationPlanPrivilegeType::INSURANCE);
    }

    public function canRequestLicense(Model $member): bool
    {
        return $this->hasValidationPlanPrivilege($member, ValidationPlanPrivilegeType::LICENSE);
    }

    public function canRequestLicenseForMembers(Entity $entity): bool
    {
        if (! $entity instanceof Entity) {
            return false;
        }

        return $this->hasValidationPlanPrivilege($entity, ValidationPlanPrivilegeType::ENTITY_MEMBER_LICENSES);
    }

    public function canSubscribeMembersToPackages(Entity $entity): bool
    {
        if (! $entity instanceof Entity) {
            return false;
        }

        return $this->hasValidationPlanPrivilege($entity, ValidationPlanPrivilegeType::ENTITY_MEMBER_SUBSCRIPTIONS);
    }

    public function getValidationPlanReason(Model $member, ValidationPlanPrivilegeType|string $requestType): ?string
    {
        if (! $this->isSupportedMember($member)) {
            return __('memberships.invalid_member_type');
        }

        if (! $member->hasActiveAffiliation()) {
            return __('memberships.no_active_affiliation_found');
        }

        $validationPlans = $this->getActiveValidationPlans($member);

        if ($validationPlans->isEmpty()) {
            // Handle backward compatibility with string types
            if (is_string($requestType)) {
                $requestType = ValidationPlanPrivilegeType::tryFrom($requestType);
            }

            return $requestType?->getFailureMessage() ?? __('memberships.insufficient_privileges_for_request_type');
        }

        return null;
    }

    private function hasValidationPlanPrivilege(Model $member, ValidationPlanPrivilegeType $privilegeType): bool
    {
        if (! $this->isSupportedMember($member)) {
            return false;
        }

        if (! $member->hasActiveAffiliation()) {
            return false;
        }

        $validationPlans = $this->getActiveValidationPlans($member);

        if ($validationPlans->isEmpty()) {
            return false;
        }

        return match ($privilegeType) {
            ValidationPlanPrivilegeType::INSURANCE => $validationPlans->contains(fn (AffiliationPlan $plan) => $plan->allowsInsuranceRequests()),
            ValidationPlanPrivilegeType::LICENSE => $validationPlans->contains(fn (AffiliationPlan $plan) => $plan->allowsLicenseRequests()),
            ValidationPlanPrivilegeType::ENTITY_MEMBER_LICENSES => $validationPlans->contains(fn (AffiliationPlan $plan) => $plan->allowsEntityMemberLicenseRequests()),
            ValidationPlanPrivilegeType::ENTITY_MEMBER_SUBSCRIPTIONS => $validationPlans->contains(fn (AffiliationPlan $plan) => $plan->allowsEntityMemberSubscriptions()),
        };
    }

    private function getActiveValidationPlans(Model $member): Collection
    {
        $activeSubscriptions = $this->getActiveMemberSubscriptions($member);

        $plansFromSubscriptions = $activeSubscriptions
            ->flatMap(function (MemberSubscription $subscription) {
                return $subscription->membershipPackage
                    ->affiliationPlans()
                    ->where('is_validation_plan', true)
                    ->get();
            })
            ->filter(function (AffiliationPlan $plan) {
                return $plan->isActive();
            });

        // For entities, also check their active affiliations directly
        if ($member instanceof Entity && $member->hasActiveAffiliation()) {
            $plansFromAffiliations = $member->affiliations()
                ->where('end_date', '>=', now())
                ->whereIn('status_class', [
                    \Domain\Memberships\States\ActiveAffiliationState::class,
                ])
                ->with(['memberSubscription.membershipPackage.affiliationPlans'])
                ->get()
                ->flatMap(function ($affiliation) {
                    if ($affiliation->memberSubscription && $affiliation->memberSubscription->membershipPackage) {
                        return $affiliation->memberSubscription->membershipPackage
                            ->affiliationPlans()
                            ->where('is_validation_plan', true)
                            ->where('federation_id', $affiliation->federation_id)
                            ->get();
                    }

                    return collect();
                })
                ->filter(function (AffiliationPlan $plan) {
                    return $plan->isActive();
                });

            return $plansFromSubscriptions->merge($plansFromAffiliations)->unique('id');
        }

        return $plansFromSubscriptions;
    }

    private function getActiveMemberSubscriptions(Model $member): Collection
    {
        return $member->memberSubscriptions()
            ->with(['membershipPackage.affiliationPlans'])
            ->get()
            ->filter(function (MemberSubscription $subscription) {
                return $subscription->isActive();
            });
    }

    private function isSupportedMember(Model $member): bool
    {
        return $member instanceof Entity || $member instanceof Individual;
    }

    public function getValidationPlansSummary(Model $member): array
    {
        if (! $this->isSupportedMember($member)) {
            return [
                'has_validation_plans' => false,
                'plans' => [],
                'privileges' => [],
            ];
        }

        $validationPlans = $this->getActiveValidationPlans($member);

        $privileges = [
            'can_request_insurance' => $this->canRequestInsurance($member),
            'can_request_license' => $this->canRequestLicense($member),
        ];

        if ($member instanceof Entity) {
            $privileges['can_request_member_licenses'] = $this->canRequestLicenseForMembers($member);
            $privileges['can_subscribe_members_to_packages'] = $this->canSubscribeMembersToPackages($member);
        }

        return [
            'has_validation_plans' => $validationPlans->isNotEmpty(),
            'plans' => $validationPlans->map(fn (AffiliationPlan $plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'federation' => $plan->federation->name ?? 'Unknown',
            ])->toArray(),
            'privileges' => $privileges,
        ];
    }
}
