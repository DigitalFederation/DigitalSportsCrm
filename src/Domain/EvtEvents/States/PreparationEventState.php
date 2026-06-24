<?php

namespace Domain\EvtEvents\States;

class PreparationEventState extends EventState
{
    public function name(): string
    {
        return __('events.status.preparation');
    }

    public function allowsEnrollments(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'yellow';
    }
}
