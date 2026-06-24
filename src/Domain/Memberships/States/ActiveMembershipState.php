<?php

namespace Domain\Memberships\States;

class ActiveMembershipState extends MembershipState
{
    public function name(): string
    {
        return __('memberships.states.active');
    }

    public function isActive(): bool
    {
        return true;
    }

    public function color(): string
    {
        return 'active-state';
    }
}
