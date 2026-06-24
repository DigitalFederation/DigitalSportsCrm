<?php

namespace Domain\Entities\States;

class PendingEntityFederationState extends EntityFederationState
{
    public function name(): string
    {
        return __('states.pending');
    }

    public function isActive(): bool
    {
        return false;
    }

    public function isRejected(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'pending-state';
    }
}
