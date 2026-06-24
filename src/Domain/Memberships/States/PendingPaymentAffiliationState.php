<?php

namespace Domain\Memberships\States;

class PendingPaymentAffiliationState extends AffiliationState
{
    public function name(): string
    {
        return __('affiliations.statuses.pending_payment');
    }

    public function color(): string
    {
        return 'yellow';
    }

    public function isActive(): bool
    {
        return false;
    }
}
