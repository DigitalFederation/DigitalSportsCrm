<?php

namespace Domain\Entities\States;

class CanceledEntityProfessionalRoleState extends EntityProfessionalRoleState
{
    public function name(): string
    {
        return __('states.canceled');
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
