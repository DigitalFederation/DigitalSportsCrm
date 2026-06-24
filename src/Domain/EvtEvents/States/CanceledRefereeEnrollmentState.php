<?php

namespace Domain\EvtEvents\States;

class CanceledRefereeEnrollmentState extends RefereeEnrollmentState
{
    public function name(): string
    {
        return __('events.canceled');
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
        return 'bg-red-100 text-red-800';
    }
}
