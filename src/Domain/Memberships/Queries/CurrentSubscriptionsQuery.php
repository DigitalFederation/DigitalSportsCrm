<?php

namespace Domain\Memberships\Queries;

use Domain\Individuals\Models\Individual;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\States\ActiveMemberSubscriptionState;

class CurrentSubscriptionsQuery
{
    public function execute(Individual $individual)
    {
        return MemberSubscription::query()
            ->where('member_type', Individual::class)
            ->where('member_id', $individual->id)
            ->where('status_class', ActiveMemberSubscriptionState::class)
            ->where('end_date', '>=', now())
            ->with(['membershipPackage.affiliationPlans', 'membershipPackage.insurancePlans'])
            ->get();
    }
}
