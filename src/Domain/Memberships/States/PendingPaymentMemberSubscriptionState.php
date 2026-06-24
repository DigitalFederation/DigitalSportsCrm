<?php

namespace Domain\Memberships\States;

class PendingPaymentMemberSubscriptionState extends MemberSubscriptionState
{
    public function name(): string
    {
        return __('memberships.subscription_states.pending_payment');
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
