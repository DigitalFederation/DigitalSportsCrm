<?php

namespace Domain\Entities\States;

class PendingEntityProfessionalRoleState extends EntityProfessionalRoleState
{
    public function name(): string
    {
        return __('states.pending');
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
