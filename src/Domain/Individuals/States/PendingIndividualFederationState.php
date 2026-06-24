<?php

namespace Domain\Individuals\States;

class PendingIndividualFederationState extends IndividualFederationState
{
    public function name(): string
    {
        return __('main.pending');
    }

    public function isActive(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'pending';
    }
}
