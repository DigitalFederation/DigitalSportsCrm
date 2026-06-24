<?php

namespace Domain\EvtEvents\States;

class CanceledEventState extends EventState
{
    public function name(): string
    {
        return __('events.status.canceled');
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
