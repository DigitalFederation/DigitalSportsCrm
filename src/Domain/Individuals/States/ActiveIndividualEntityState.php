<?php

namespace Domain\Individuals\States;

class ActiveIndividualEntityState extends IndividualEntityState
{
    public function name(): string
    {
        return 'Active';
    }

    public function isActive(): bool
    {
        return true;
    }

    public function color(): string
    {
        return 'active-state';
    }
}
