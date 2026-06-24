<?php

namespace Domain\EvtEvents\States;

use Domain\EvtEvents\Models\RefereeEnrollment;

abstract class RefereeEnrollmentState
{
    public function __construct(
        protected RefereeEnrollment $refereeEnrollment
    ) {}

    abstract public function name(): string;
    abstract public function isActive(): bool;
    abstract public function isPending(): bool;
    abstract public function isCanceled(): bool;
    abstract public function color(): string;
}
