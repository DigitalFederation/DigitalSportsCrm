<?php

namespace Domain\EvtEvents\States;

use Domain\EvtEvents\Models\TeamOfficialEnrollment;

abstract class TeamOfficialEnrollmentState
{
    protected TeamOfficialEnrollment $enrollment;

    public function __construct(TeamOfficialEnrollment $enrollment)
    {
        $this->enrollment = $enrollment;
    }

    abstract public function isActive(): bool;
    abstract public function isPending(): bool;
    abstract public function isCanceled(): bool;

    abstract public function name(): string;
    abstract public function color(): string;
}
