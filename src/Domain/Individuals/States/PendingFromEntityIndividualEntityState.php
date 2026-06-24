<?php

namespace Domain\Individuals\States;

class PendingFromEntityIndividualEntityState extends IndividualEntityState
{
    public function name(): string
    {
        return 'Pending Entity';
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
