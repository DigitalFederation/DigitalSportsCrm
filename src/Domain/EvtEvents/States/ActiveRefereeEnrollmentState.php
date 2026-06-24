<?php

namespace Domain\EvtEvents\States;

class ActiveRefereeEnrollmentState extends RefereeEnrollmentState
{
    public function name(): string
    {
        return __('events.active');
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
        return 'bg-green-100 text-green-800';
    }
}
