<?php

namespace Domain\Entities\States;

class ActiveEntityProfessionalRoleState extends EntityProfessionalRoleState
{
    public function name(): string
    {
        return __('states.active');
    }

    public function isActive(): bool
    {
        return true;
    }

    public function color(): string
    {
        return 'active-state';
    }
}
