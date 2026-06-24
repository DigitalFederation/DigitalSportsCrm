<?php

namespace Domain\EvtEvents\States;

use Domain\EvtEvents\Models\CoachEnrollment;

abstract class CoachEnrollmentState
{
    protected CoachEnrollment $enrollment;

    public function __construct(CoachEnrollment $enrollment)
    {
        $this->enrollment = $enrollment;
    }

    abstract public function isActive(): bool;
    abstract public function isPending(): bool;
    abstract public function isCanceled(): bool;

    abstract public function name(): string;
    abstract public function color(): string;
}
