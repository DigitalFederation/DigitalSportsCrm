<?php

namespace Domain\Individuals\States;

class DeniedIndividualEntityState extends IndividualEntityState
{
    public function name(): string
    {
        return 'Denied';
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
