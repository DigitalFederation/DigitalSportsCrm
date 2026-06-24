<?php

namespace Domain\Memberships\States;

class ExpiredMemberSubscriptionState extends MemberSubscriptionState
{
    public function name(): string
    {
        return __('memberships.subscription_states.expired');
    }

    public function isActive(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'red';
    }
}
