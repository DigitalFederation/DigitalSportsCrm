<?php

namespace Domain\EvtEvents\States;

class ActiveIndividualEnrollmentState extends IndividualEnrollmentState
{
    public function name(): string
    {
        return 'Active';
    }

    public function isActive(): bool
    {
        return true;
    }

    public function isPending(): bool
    {
        return false;
    }

    public function isCanceled(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'success-state';
    }
}
