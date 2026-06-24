<?php

namespace Domain\Memberships\States;

use Domain\Memberships\Models\Membership;

abstract class MembershipState
{
    protected Membership $membership;

    public function __construct(Membership $membership)
    {
        $this->membership = $membership;
    }

    abstract public function name(): string;

    abstract public function isActive(): bool;

    abstract public function color(): string;
}
