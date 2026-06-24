<?php

namespace Domain\Memberships\Actions;

use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Insurance\Actions\CreateInsuranceAction;
use Domain\Memberships\DataTransferObject\MemberSubscriptionData;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\Services\SubscriptionValidationService;

class CreateMemberSubscriptionAction
{
    public function __construct(
        private readonly CreateAffiliationAction $createAffiliationAction,
        private readonly CreateInsuranceAction $createInsuranceAction,
        private readonly SubscriptionValidationService $validationService
    ) {}

    public function __invoke(MemberSubscriptionData $data): MemberSubscription
    {
        // Get the member model
        $member = $this->getMember($data);
        $package = MembershipPackage::find($data->membership_package_id);

        // Validate the subscription according to business rules
        $validation = $this->validationService->validateSubscription($member, $package);

        if (! $validation['valid']) {
            throw new \Exception($validation['error']);
        }

        // Determine requester information
        $requesterInfo = $this->determineRequester($data);

        $subscription = MemberSubscription::create([
            'membership_package_id' => $data->membership_package_id,
            'member_type' => $this->getMorphAlias($data->member_type),
            'member_id' => $data->member_id,
            'start_date' => $data->start_date,
            'end_date' => $data->end_date,
            'status_class' => $data->status_class,
            'requester_type' => $requesterInfo['requester_type'],
            'requester_id' => $requesterInfo['requester_id'],
            'request_type' => $requesterInfo['request_type'],
        ]);

        // Create associated Affiliation and Insurance
        $this->createAffiliations($subscription, $data);
        $this->createInsurances($subscription, $data);

        return $subscription;
    }

    private function createAffiliations(MemberSubscription $subscription, MemberSubscriptionData $data): void
    {
        // Ensure affiliationPlans are loaded with their federation relationship
        $subscription->membershipPackage->load('affiliationPlans.federation');

        foreach ($subscription->membershipPackage->affiliationPlans as $affiliationPlan) {
            $this->createAffiliationAction->execute(
                $subscription,
                $affiliationPlan,
                $data->member_type,
                $data->member_id
            );
        }
    }

    private function createInsurances(MemberSubscription $subscription, MemberSubscriptionData $data): void
    {
        // Ensure insurancePlans are loaded
        $subscription->membershipPackage->load('insurancePlans');

        foreach ($subscription->membershipPackage->insurancePlans as $insurancePlan) {
            $this->createInsuranceAction->execute(
                $subscription,
                $insurancePlan,
                $data->member_type,
                $data->member_id
            );
        }
    }

    private function getMorphAlias(string $memberType): string
    {
        // Convert full class names to their morph aliases
        $morphMap = [
            Entity::class => 'entity',
            Individual::class => 'individual',
        ];

        // If already a morph alias, return as is
        if (in_array($memberType, ['entity', 'individual'])) {
            return $memberType;
        }

        // Otherwise, convert to morph alias
        return $morphMap[$memberType] ?? $memberType;
    }

    /**
     * Get the member model from the subscription data
     */
    private function getMember(MemberSubscriptionData $data): Entity|Individual
    {
        $memberType = $this->getMorphAlias($data->member_type);
        $memberId = $data->member_id ?? $data->entity_id ?? $data->individual_id ?? null;

        if (! $memberId) {
            throw new \Exception('Member ID is required');
        }

        if ($memberType === 'entity' || strpos($data->member_type, 'Entity') !== false) {
            return Entity::findOrFail($memberId);
        }

        if ($memberType === 'individual' || strpos($data->member_type, 'Individual') !== false) {
            return Individual::findOrFail($memberId);
        }

        throw new \Exception('Invalid member type');
    }

    /**
     * Determine who is requesting the subscription
     */
    private function determineRequester(MemberSubscriptionData $data): array
    {
        $user = auth()->user();

        if (! $user) {
            // When no authenticated user, the member is requesting for themselves
            $memberType = $this->getMorphAlias($data->member_type);

            return [
                'requester_type' => $memberType, // Use morph alias
                'requester_id' => $data->member_id,
                'request_type' => 'direct',
            ];
        }

        // If the authenticated user is an entity user
        if ($user->isEntity()) {
            $entity = $user->getEntity();
            $memberType = $this->getMorphAlias($data->member_type);

            // If entity is subscribing itself
            if ($memberType === 'entity' && $entity && $entity->id === $data->member_id) {
                return [
                    'requester_type' => 'entity', // Use morph alias
                    'requester_id' => $entity->id,
                    'request_type' => 'direct',
                ];
            }

            // If entity is subscribing its members
            if ($memberType === 'individual' && $entity) {
                return [
                    'requester_type' => 'entity', // Use morph alias
                    'requester_id' => $entity->id,
                    'request_type' => 'entity_group',
                ];
            }
        }

        // If the authenticated user is an individual user
        if ($user->isIndividual()) {
            $individual = $user->getIndividual();

            // Individual subscribing themselves
            if ($individual && $individual->id === $data->member_id) {
                return [
                    'requester_type' => 'individual', // Use morph alias
                    'requester_id' => $individual->id,
                    'request_type' => 'direct',
                ];
            }
        }

        // Default: Assume the member being subscribed is the requester
        // This handles admin users creating subscriptions for members
        $memberType = $this->getMorphAlias($data->member_type);

        return [
            'requester_type' => $memberType, // Use morph alias directly
            'requester_id' => $data->member_id,
            'request_type' => 'direct',
        ];
    }
}
