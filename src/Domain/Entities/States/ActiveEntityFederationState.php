<?php

namespace Domain\Entities\States;

class ActiveEntityFederationState extends EntityFederationState
{
    public function name(): string
    {
        return __('states.active');
    }

    public function isActive(): bool
    {
        return true;
    }

    public function isRejected(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'active-state';
    }
}
