<?php

namespace App\View\Components\EvtEvent;

use Domain\EvtEvents\Models\Event;
use Illuminate\View\Component;

class BlockEventDetailsEntity extends Component
{
    public $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function render()
    {
        return view('components.evt_event.block-event-details-entity');
    }
}
