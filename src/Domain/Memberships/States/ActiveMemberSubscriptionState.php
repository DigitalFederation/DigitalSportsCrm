<?php

namespace Domain\Memberships\States;

class ActiveMemberSubscriptionState extends MemberSubscriptionState
{
    public function name(): string
    {
        return __('memberships.subscription_states.active');
    }

    public function isActive(): bool
    {
        return true;
    }

    public function color(): string
    {
        return 'green';
    }
}
