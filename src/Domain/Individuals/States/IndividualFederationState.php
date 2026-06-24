<?php

namespace Domain\Individuals\States;

use Domain\Individuals\Models\IndividualFederation;

abstract class IndividualFederationState
{
    protected IndividualFederation $individualFederation;

    public function __construct(IndividualFederation $individualFederation)
    {
        $this->individualFederation = $individualFederation;
    }

    abstract public function isActive(): bool;

    abstract public function name(): string;

    abstract public function color(): string;
}
