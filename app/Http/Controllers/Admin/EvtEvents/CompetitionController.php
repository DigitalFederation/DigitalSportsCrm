<?php

namespace App\Http\Controllers\Admin\EvtEvents;

use App\Http\Controllers\Controller;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Event;

class CompetitionController extends Controller
{
    public function edit(Event $event, Competition $competition)
    {
        return view('web.admin.evt_events.competitions.edit', compact('competition', 'event'));
    }
}
