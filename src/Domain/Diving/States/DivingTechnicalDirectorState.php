<?php

namespace Domain\Diving\States;

use Domain\Diving\Models\DivingEntityTechnicalDirector;

abstract class DivingTechnicalDirectorState
{
    protected DivingEntityTechnicalDirector $assignment;

    public function __construct(DivingEntityTechnicalDirector $assignment)
    {
        $this->assignment = $assignment;
    }

    abstract public function name(): string;

    abstract public function color(): string;

    abstract public function canBeRemoved(): bool;

    public function isAssigned(): bool
    {
        return false;
    }

    public function isRemoved(): bool
    {
        return false;
    }
}
