<?php

namespace Domain\Insurance\States;

use Domain\Insurance\Models\Insurance;

abstract class InsuranceState
{
    protected Insurance $insurance;

    public function __construct(Insurance $insurance)
    {
        $this->insurance = $insurance;
    }

    abstract public function name(): string;

    abstract public function color(): string;

    abstract public function isActive(): bool;

    abstract public function canBeActivated(): bool;

    abstract public function canBeSuspended(): bool;

    abstract public function canBeExpired(): bool;
}
