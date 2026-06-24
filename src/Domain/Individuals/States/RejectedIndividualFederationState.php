<?php

namespace Domain\Individuals\States;

class RejectedIndividualFederationState extends IndividualFederationState
{
    public function name(): string
    {
        return 'Rejected';
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
