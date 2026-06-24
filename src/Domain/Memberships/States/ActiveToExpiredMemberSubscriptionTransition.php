<?php

namespace Domain\Memberships\States;

use Domain\Memberships\Models\MemberSubscription;
use Exception;

class ActiveToExpiredMemberSubscriptionTransition
{
    /**
     * @throws Exception
     */
    public function __invoke(MemberSubscription $subscription): MemberSubscription
    {
        if ($subscription->status_class !== ActiveMemberSubscriptionState::class) {
            throw new Exception('Subscription must be in Active state to expire');
        }

        $subscription->status_class = ExpiredMemberSubscriptionState::class;
        $subscription->save();

        activity('MemberSubscription')
            ->performedOn($subscription)
            ->event('expired')
            ->log('Member subscription expired.');

        return $subscription;
    }
}
