<?php

namespace Domain\Insurance\Actions;

use Domain\Insurance\Models\Insurance;
use Domain\Insurance\Models\InsurancePlan;
use Domain\Insurance\States\ActiveInsuranceState;
use Domain\Insurance\States\InactiveInsuranceState;
use Domain\Insurance\States\PendingPaymentInsuranceState;
use Domain\Memberships\Models\MemberSubscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CreateInsuranceAction
{
    /**
     * Create an insurance based on subscription and plan data
     */
    public function execute(
        MemberSubscription $subscription,
        InsurancePlan $insurancePlan,
        string $memberType,
        string|int $memberId
    ): ?Insurance {
        // Check if insurance requires active affiliation
        $isInsuranceOnlyPackage = $subscription->membershipPackage->affiliationPlans->isEmpty();

        if (! $isInsuranceOnlyPackage && ($insurancePlan->requires_active_affiliation ?? true)) {
            // If it's not an insurance-only package and requires affiliation,
            // only create insurance if package has affiliation plans
            if ($subscription->membershipPackage->affiliationPlans->isEmpty()) {
                Log::info('Skipping insurance plan - requires active affiliation but package has no affiliation plans', [
                    'insurance_plan_id' => $insurancePlan->id,
                    'subscription_id' => $subscription->id,
                    'package_id' => $subscription->membershipPackage->id,
                ]);

                return null;
            }
        }

        // Determine fee based on member type and request type
        $fee = $this->calculateFee($insurancePlan, $memberType, $subscription->request_type);

        // Get insurance dates - either fixed from plan or calculated from subscription
        $subscriptionStartDate = Carbon::parse($subscription->start_date);
        $startDate = $insurancePlan->getInsuranceStartDate($subscriptionStartDate);
        $endDate = $insurancePlan->calculateEndDate($startDate);

        // Determine insurance status based on subscription status
        $insuranceStatusClass = $this->determineInsuranceStatus($subscription->status_class);

        return $subscription->insurances()->create([
            'insurance_plan_id' => $insurancePlan->id,
            'member_type' => $this->getMorphAlias($memberType),
            'member_id' => $memberId,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'individual_fee' => $this->isEntitySubscription($memberType) ? null : $fee,
            'entity_fee' => $this->isEntitySubscription($memberType) ? $fee : null,
            'is_external' => false,
            'policy_number' => $this->generatePolicyNumber($insurancePlan),
            'status_class' => $insuranceStatusClass,
            'requester_type' => $subscription->requester_type,
            'requester_id' => $subscription->requester_id,
            'request_type' => $subscription->request_type,
        ]);
    }

    /**
     * Calculate the appropriate fee based on member type and request type
     */
    private function calculateFee(InsurancePlan $insurancePlan, string $memberType, string $requestType): float
    {
        // For entity subscriptions, always use entity fee
        if ($this->isEntitySubscription($memberType)) {
            return $insurancePlan->entity_fee ?? 0;
        }

        // For individual subscriptions, check request type
        // Federation facilitated subscriptions use individual fees
        if ($requestType === 'federation_facilitated') {
            return $insurancePlan->individual_fee ?? 0;
        }

        // Entity group subscriptions use entity fees (entity pays for individuals)
        if ($requestType === 'entity_group') {
            return $insurancePlan->entity_fee ?? 0;
        }

        // Default to individual fee
        return $insurancePlan->individual_fee ?? 0;
    }

    /**
     * Generate policy number for the insurance
     */
    private function generatePolicyNumber(InsurancePlan $insurancePlan): ?string
    {
        // Group plans: return the plan's policy number
        if ($insurancePlan->isGroupPlan()) {
            return $insurancePlan->policy_number;
        }

        // Sequential policy numbers: generate if configured
        if ($insurancePlan->hasSequentialPolicyNumbers()) {
            return $insurancePlan->generateNextPolicyNumber();
        }

        // No policy number configuration: return null (to be set manually later)
        return null;
    }

    /**
     * Check if this is an entity subscription
     */
    private function isEntitySubscription(string $memberType): bool
    {
        return $this->getMorphAlias($memberType) === 'entity';
    }

    /**
     * Determine insurance status class based on subscription status
     */
    private function determineInsuranceStatus(string $subscriptionStatusClass): string
    {
        // Map subscription status to insurance status
        $statusMap = [
            \Domain\Memberships\States\ActiveMemberSubscriptionState::class => ActiveInsuranceState::class,
            \Domain\Memberships\States\PendingPaymentMemberSubscriptionState::class => PendingPaymentInsuranceState::class,
            \Domain\Memberships\States\ExpiredMemberSubscriptionState::class => \Domain\Insurance\States\ExpiredInsuranceState::class,
        ];

        return $statusMap[$subscriptionStatusClass] ?? InactiveInsuranceState::class;
    }

    /**
     * Get morph alias for member type
     */
    private function getMorphAlias(string $memberType): string
    {
        // Convert full class names to their morph aliases
        $morphMap = [
            \Domain\Entities\Models\Entity::class => 'entity',
            \Domain\Individuals\Models\Individual::class => 'individual',
        ];

        // If already a morph alias, return as is
        if (in_array($memberType, ['entity', 'individual'])) {
            return $memberType;
        }

        // Otherwise, convert to morph alias
        return $morphMap[$memberType] ?? $memberType;
    }
}
