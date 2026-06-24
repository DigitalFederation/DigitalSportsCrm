<?php

namespace Domain\Individuals\States;

use Domain\Individuals\Models\IndividualEntity;

abstract class IndividualEntityState
{
    protected IndividualEntity $individualEntity;

    public function __construct(IndividualEntity $individualEntity)
    {
        $this->individualEntity = $individualEntity;
    }

    abstract public function name(): string;

    abstract public function isActive(): bool;

    abstract public function color(): string;
}
