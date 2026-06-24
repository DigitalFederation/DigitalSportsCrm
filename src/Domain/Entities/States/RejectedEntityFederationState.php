<?php

namespace Domain\Entities\States;

class RejectedEntityFederationState extends EntityFederationState
{
    public function name(): string
    {
        return __('states.rejected');
    }

    public function isActive(): bool
    {
        return false;
    }

    public function isRejected(): bool
    {
        return true;
    }

    public function color(): string
    {
        return 'canceled-state';
    }
}
