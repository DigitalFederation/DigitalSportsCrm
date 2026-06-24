<?php

namespace Domain\Individuals\States;

class PendingFromIndividualEntityState extends IndividualEntityState
{
    public function name(): string
    {
        return 'Pending Individual';
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
