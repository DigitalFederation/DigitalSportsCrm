<?php

namespace Domain\EvtEvents\States;

class CanceledIndividualEnrollmentState extends IndividualEnrollmentState
{
    public function name(): string
    {
        return 'Canceled';
    }

    public function isActive(): bool
    {
        return false;
    }

    public function isPending(): bool
    {
        return false;
    }

    public function isCanceled(): bool
    {
        return true;
    }

    public function color(): string
    {
        return 'cancel-state';
    }
}
