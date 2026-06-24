<?php

namespace Domain\EvtEvents\States;

class ArchiveEventState extends EventState
{
    public function name(): string
    {
        return __('events.status.archived');
    }

    public function allowsEnrollments(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'red';
    }
}
