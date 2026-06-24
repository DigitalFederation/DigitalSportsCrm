<?php

namespace Domain\Diving\States;

class RemovedDivingTechnicalDirectorState extends DivingTechnicalDirectorState
{
    public function name(): string
    {
        return __('diving.removed');
    }

    public function color(): string
    {
        return 'danger';
    }

    public function canBeRemoved(): bool
    {
        return false;
    }

    public function isRemoved(): bool
    {
        return true;
    }
}
