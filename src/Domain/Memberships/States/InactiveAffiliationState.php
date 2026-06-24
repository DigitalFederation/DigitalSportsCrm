<?php

namespace Domain\Memberships\States;

class InactiveAffiliationState extends AffiliationState
{
    public function name(): string
    {
        return __('affiliations.statuses.inactive');
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
