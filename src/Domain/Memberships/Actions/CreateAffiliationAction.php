<?php

namespace Domain\Memberships\Actions;

use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Memberships\Models\Affiliation;
use Domain\Memberships\Models\AffiliationPlan;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\States\ActiveAffiliationState;
use Domain\Memberships\States\InactiveAffiliationState;
use Domain\Memberships\States\PendingPaymentAffiliationState;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CreateAffiliationAction
{
    /**
     * Create an affiliation based on subscription and plan data
     */
    public function execute(
        MemberSubscription $subscription,
        AffiliationPlan $affiliationPlan,
        string $memberType,
        string|int $memberId
    ): ?Affiliation {
        // Skip if federation_id is not set or invalid
        if (! $affiliationPlan->federation_id) {
            Log::warning('Skipping affiliation plan without federation_id', [
                'affiliation_plan_id' => $affiliationPlan->id,
                'subscription_id' => $subscription->id,
            ]);

            return null;
        }

        // Skip if member already has an active affiliation for this plan
        // This prevents duplicate affiliations when subscribing to packages with partial overlap
        $existingAffiliation = Affiliation::where('member_type', $this->getMorphAlias($memberType))
            ->where('member_id', $memberId)
            ->where('federation_id', $affiliationPlan->federation_id)
            ->where('end_date', '>=', now())
            ->where('status_class', ActiveAffiliationState::class)
            ->whereHas('memberSubscription.membershipPackage.affiliationPlans', function ($q) use ($affiliationPlan) {
                $q->where('affiliation_plans.id', $affiliationPlan->id);
            })
            ->exists();

        if ($existingAffiliation) {
            Log::info('Skipping affiliation - member already has active affiliation for this plan', [
                'member_type' => $memberType,
                'member_id' => $memberId,
                'federation_id' => $affiliationPlan->federation_id,
                'affiliation_plan_id' => $affiliationPlan->id,
                'subscription_id' => $subscription->id,
            ]);

            return null;
        }

        // For entity subscriptions, only create affiliations for federations the entity is associated with
        if ($this->isEntitySubscription($memberType)) {
            $entity = Entity::find($memberId);
            if (! $entity || ! $this->canEntityCreateAffiliationForFederation($entity, $affiliationPlan->federation_id)) {
                Log::info('Skipping affiliation plan - entity not associated with federation', [
                    'entity_id' => $memberId,
                    'federation_id' => $affiliationPlan->federation_id,
                    'affiliation_plan_id' => $affiliationPlan->id,
                    'subscription_id' => $subscription->id,
                ]);

                return null;
            }
        }

        // Determine fee based on member type and request type
        $fee = $this->calculateFee($affiliationPlan, $memberType, $subscription->request_type);

        // Calculate dates based on the affiliation plan's own duration and date range
        $startDate = $this->calculateAffiliationStartDate($affiliationPlan, $subscription->start_date);
        $endDate = $this->calculateAffiliationEndDate($affiliationPlan, $startDate);

        // Determine affiliation status based on subscription status
        $affiliationStatusClass = $this->determineAffiliationStatus($subscription->status_class);

        return $subscription->affiliations()->create([
            'federation_id' => $affiliationPlan->federation_id,
            'member_type' => $this->getMorphAlias($memberType),
            'member_id' => $memberId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'individual_fee' => $this->isEntitySubscription($memberType) ? null : $fee,
            'entity_fee' => $this->isEntitySubscription($memberType) ? $fee : null,
            'status_class' => $affiliationStatusClass,
            'requester_type' => $subscription->requester_type,
            'requester_id' => $subscription->requester_id,
            'request_type' => $subscription->request_type,
        ]);
    }

    /**
     * Calculate the appropriate fee based on member type and request type
     */
    private function calculateFee(AffiliationPlan $affiliationPlan, string $memberType, string $requestType): float
    {
        // For entity subscriptions, always use entity fee
        if ($this->isEntitySubscription($memberType)) {
            return $affiliationPlan->entity_fee ?? 0;
        }

        // For individual subscriptions, check request type
        // Federation facilitated subscriptions use individual fees
        if ($requestType === 'federation_facilitated') {
            return $affiliationPlan->individual_fee ?? 0;
        }

        // Entity group subscriptions use entity fees (entity pays for individuals)
        if ($requestType === 'entity_group') {
            return $affiliationPlan->entity_fee ?? 0;
        }

        // Default to individual fee
        return $affiliationPlan->individual_fee ?? 0;
    }

    /**
     * Check if this is an entity subscription
     */
    private function isEntitySubscription(string $memberType): bool
    {
        return $this->getMorphAlias($memberType) === 'entity';
    }

    /**
     * Check if entity can create affiliation for a specific federation
     */
    private function canEntityCreateAffiliationForFederation(Entity $entity, int $federationId): bool
    {
        // Allow entities to create affiliations for federations they're associated with (active or pending)
        return $entity->federations()
            ->whereIn('entity_federation.status_class', [
                ActiveEntityFederationState::class,
                'Domain\\Entities\\States\\PendingEntityFederationState',
            ])
            ->where('federation.id', $federationId)
            ->exists();
    }

    /**
     * Calculate the start date for an affiliation based on the plan's constraints
     */
    private function calculateAffiliationStartDate(AffiliationPlan $affiliationPlan, string $subscriptionStartDate): string
    {
        $subscriptionStart = Carbon::parse($subscriptionStartDate);

        // If the affiliation plan has a specific start date, use the later of the two
        if ($affiliationPlan->start_date) {
            $planStart = Carbon::parse($affiliationPlan->start_date);

            return $subscriptionStart->gte($planStart) ? $subscriptionStartDate : $affiliationPlan->start_date->format('Y-m-d');
        }

        // Otherwise, use the subscription start date
        return $subscriptionStartDate;
    }

    /**
     * Calculate the end date for an affiliation based on the plan's duration and constraints
     */
    private function calculateAffiliationEndDate(AffiliationPlan $affiliationPlan, string $startDate): string
    {
        $start = Carbon::parse($startDate);

        // If the affiliation plan has a specific end date, use it
        if ($affiliationPlan->end_date) {
            $planEnd = Carbon::parse($affiliationPlan->end_date);
            $durationEnd = $start->copy()->addMonths($affiliationPlan->duration_months);

            // Use the earlier of plan end date or duration-based end date
            return $planEnd->lt($durationEnd) ? $affiliationPlan->end_date->format('Y-m-d') : $durationEnd->format('Y-m-d');
        }

        // Otherwise, calculate based on duration
        return $start->addMonths($affiliationPlan->duration_months)->format('Y-m-d');
    }

    /**
     * Determine affiliation status class based on subscription status
     */
    private function determineAffiliationStatus(string $subscriptionStatusClass): string
    {
        // Map subscription status to affiliation status
        $statusMap = [
            \Domain\Memberships\States\ActiveMemberSubscriptionState::class => ActiveAffiliationState::class,
            \Domain\Memberships\States\PendingPaymentMemberSubscriptionState::class => PendingPaymentAffiliationState::class,
            \Domain\Memberships\States\ExpiredMemberSubscriptionState::class => \Domain\Memberships\States\ExpiredAffiliationState::class,
        ];

        return $statusMap[$subscriptionStatusClass] ?? InactiveAffiliationState::class;
    }

    /**
     * Get morph alias for member type
     */
    private function getMorphAlias(string $memberType): string
    {
        // Convert full class names to their morph aliases
        $morphMap = [
            Entity::class => 'entity',
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
