<?php

namespace Domain\EvtEvents\States;

class PendingIndividualEnrollmentState extends IndividualEnrollmentState
{
    public function name(): string
    {
        return 'Pending';
    }

    public function isActive(): bool
    {
        return false;
    }

    public function isPending(): bool
    {
        return true;
    }

    public function isCanceled(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'pending-state';
    }
}
