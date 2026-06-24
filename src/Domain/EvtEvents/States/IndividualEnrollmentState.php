<?php

namespace Domain\EvtEvents\States;

use Domain\EvtEvents\Models\IndividualEnrollment;

abstract class IndividualEnrollmentState
{
    protected IndividualEnrollment $enrollment;

    public function __construct(IndividualEnrollment $enrollment)
    {
        $this->enrollment = $enrollment;
    }
    abstract public function isActive(): bool;
    abstract public function isPending(): bool;
    abstract public function isCanceled(): bool;

    abstract public function name(): string;
    abstract public function color(): string;
}
