<?php

namespace Domain\EvtEvents\States;

class PendingCoachEnrollmentState extends CoachEnrollmentState
{
    public function name(): string
    {
        return __('events.pending');
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
        return 'bg-yellow-100 text-yellow-800';
    }
}
