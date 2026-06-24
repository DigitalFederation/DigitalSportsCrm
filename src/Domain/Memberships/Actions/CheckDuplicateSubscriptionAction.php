<?php

namespace Domain\Memberships\Actions;

use Domain\Entities\Models\Entity;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\States\ActiveMemberSubscriptionState;
use Domain\Memberships\States\PendingPaymentMemberSubscriptionState;

class CheckDuplicateSubscriptionAction
{
    /**
     * Check if entity already has an active subscription to the same package
     */
    public function execute(Entity $entity, MembershipPackage $package): bool
    {
        return MemberSubscription::where('member_type', Entity::class)
            ->where('member_id', $entity->id)
            ->where('membership_package_id', $package->id)
            ->where('end_date', '>=', now())
            ->whereIn('status_class', [
                ActiveMemberSubscriptionState::class,
                PendingPaymentMemberSubscriptionState::class,
            ])
            ->exists();
    }
}
