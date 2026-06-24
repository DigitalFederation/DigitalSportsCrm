<?php

namespace Domain\Memberships\States;

class ExpiredMembershipState extends MembershipState
{
    public function name(): string
    {
        return __('memberships.states.expired');
    }

    public function isActive(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'canceled-state';
    }
}
