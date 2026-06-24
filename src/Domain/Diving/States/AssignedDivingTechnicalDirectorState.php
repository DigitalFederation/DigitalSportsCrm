<?php

namespace Domain\Diving\States;

class AssignedDivingTechnicalDirectorState extends DivingTechnicalDirectorState
{
    public function name(): string
    {
        return __('diving.assigned');
    }

    public function color(): string
    {
        return 'success';
    }

    public function canBeRemoved(): bool
    {
        return true;
    }

    public function isAssigned(): bool
    {
        return true;
    }
}
