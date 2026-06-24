<?php

namespace Domain\Memberships\Actions;

use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\States\ActiveToExpiredMemberSubscriptionTransition;

class ExpireMemberSubscriptionAction
{
    public function __invoke(MemberSubscription $subscription): void
    {
        $transition = new ActiveToExpiredMemberSubscriptionTransition;
        $transition($subscription);
    }
}
