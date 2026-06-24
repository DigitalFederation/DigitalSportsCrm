<?php

namespace Domain\Memberships\States;

class ActiveAffiliationState extends AffiliationState
{
    public function name(): string
    {
        return __('affiliations.statuses.active');
    }

    public function color(): string
    {
        return 'green';
    }

    public function isActive(): bool
    {
        return true;
    }
}
