<?php

namespace Domain\Individuals\States;

class PendingIndividualEntityState extends IndividualEntityState
{
    public function name(): string
    {
        return 'Pending';
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
