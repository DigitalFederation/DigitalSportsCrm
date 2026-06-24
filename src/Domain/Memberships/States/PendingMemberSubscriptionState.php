<?php

namespace Domain\Memberships\States;

class PendingMemberSubscriptionState extends MemberSubscriptionState
{
    public function name(): string
    {
        return __('memberships.subscription_states.pending');
    }

    public function isActive(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'yellow';
    }
}
