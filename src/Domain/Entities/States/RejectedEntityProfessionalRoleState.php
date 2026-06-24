<?php

namespace Domain\Entities\States;

class RejectedEntityProfessionalRoleState extends EntityProfessionalRoleState
{
    public function name(): string
    {
        return __('states.rejected');
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
