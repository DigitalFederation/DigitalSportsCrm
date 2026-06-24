<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Event;

class CreateEventAction
{
    public function execute(array $data): Event
    {
        return Event::create($data);
    }
}
