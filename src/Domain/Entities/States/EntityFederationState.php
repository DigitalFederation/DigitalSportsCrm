<?php

namespace Domain\Entities\States;

use Domain\Entities\Models\EntityFederation;

abstract class EntityFederationState
{
    protected EntityFederation $entityFederation;

    public function __construct(EntityFederation $entityFederation)
    {
        $this->entityFederation = $entityFederation;
    }

    abstract public function name(): string;

    abstract public function isActive(): bool;

    abstract public function isRejected(): bool;

    abstract public function color(): string;
}
