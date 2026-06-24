<?php

namespace Domain\Memberships\States;

class ExpiredAffiliationState extends AffiliationState
{
    public function name(): string
    {
        return __('affiliations.statuses.expired');
    }

    public function color(): string
    {
        return 'gray';
    }

    public function isActive(): bool
    {
        return false;
    }
}
