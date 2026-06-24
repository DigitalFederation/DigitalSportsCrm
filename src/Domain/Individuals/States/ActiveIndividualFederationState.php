<?php

namespace Domain\Individuals\States;

class ActiveIndividualFederationState extends IndividualFederationState
{
    public function name(): string
    {
        return __('main.approved');
    }

    public function isActive(): bool
    {
        return true;
    }

    public function color(): string
    {
        return 'approved';
    }
}
