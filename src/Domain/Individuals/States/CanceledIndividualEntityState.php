<?php

namespace Domain\Individuals\States;

class CanceledIndividualEntityState extends IndividualEntityState
{
    public function name(): string
    {
        return 'Canceled';
    }

    public function isActive(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'canceled-state';
    }
}
