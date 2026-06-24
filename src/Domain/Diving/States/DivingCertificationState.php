<?php

namespace Domain\Diving\States;

use Domain\Diving\Models\DivingProfessionalCertification;

abstract class DivingCertificationState
{
    protected DivingProfessionalCertification $certification;

    public function __construct(DivingProfessionalCertification $certification)
    {
        $this->certification = $certification;
    }

    abstract public function name(): string;

    abstract public function color(): string;

    abstract public function isActive(): bool;

    abstract public function canBeValidated(): bool;

    abstract public function canBeRevoked(): bool;
}
