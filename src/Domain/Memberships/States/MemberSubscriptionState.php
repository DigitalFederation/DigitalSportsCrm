<?php

namespace Domain\Memberships\States;

use Domain\Memberships\Models\MemberSubscription;

abstract class MemberSubscriptionState
{
    protected MemberSubscription $subscription;

    public function __construct(MemberSubscription $subscription)
    {
        $this->subscription = $subscription;
    }

    abstract public function name(): string;

    abstract public function isActive(): bool;

    abstract public function color(): string;
}
