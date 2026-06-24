<?php

namespace Domain\Entities\States;

use Domain\Entities\Models\EntityAthlete;
use Domain\Entities\Models\EntityProfessionalRole;

abstract class EntityProfessionalRoleState
{
    protected EntityProfessionalRole|EntityAthlete $entityProfessionalRole;

    public function __construct(EntityProfessionalRole|EntityAthlete $entityProfessionalRole)
    {
        $this->entityProfessionalRole = $entityProfessionalRole;
    }

    abstract public function name(): string;

    abstract public function isActive(): bool;

    abstract public function color(): string;
}
