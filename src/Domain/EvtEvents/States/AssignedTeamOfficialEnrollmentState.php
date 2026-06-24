<?php

namespace Domain\EvtEvents\States;

class AssignedTeamOfficialEnrollmentState extends TeamOfficialEnrollmentState
{
    public function name(): string
    {
        return __('events.assigned');
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
        return 'bg-blue-100 text-blue-800';
    }
}
