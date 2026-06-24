<?php

namespace Domain\Memberships\States;

class PendingMembershipState extends MembershipState
{
    public function name(): string
    {
        return __('memberships.states.pending');
    }

    public function isActive(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'pending-state';
    }
}
