<?php

namespace Domain\Memberships\States;

class CanceledMembershipState extends MembershipState
{
    public function name(): string
    {
        return __('memberships.states.canceled');
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
