<?php

namespace Domain\EvtEvents\States;

class DefaultEventState extends EventState
{
    public function name(): string
    {
        return __('events.status.unknown');
    }

    public function allowsEnrollments(): bool
    {
        // Conservative default: don't allow enrollments for unknown states
        return false;
    }

    public function color(): string
    {
        return 'default-state';
    }
}
