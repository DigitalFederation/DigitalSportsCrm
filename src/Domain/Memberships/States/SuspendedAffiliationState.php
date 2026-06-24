<?php

namespace Domain\Memberships\States;

class SuspendedAffiliationState extends AffiliationState
{
    public function name(): string
    {
        return __('affiliations.statuses.suspended');
    }

    public function color(): string
    {
        return 'orange';
    }

    public function isActive(): bool
    {
        return false;
    }
}
