<?php

namespace Domain\EvtEvents\States;

use Domain\EvtEvents\Models\Event;

abstract class EventState
{
    protected Event $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    abstract public function name(): string;

    abstract public function allowsEnrollments(): bool;

    abstract public function color(): string;
}
