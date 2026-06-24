<?php

namespace App\Http\Controllers\Federation\EvtEvents;

use App\Http\Controllers\Controller;
use Domain\EvtEvents\Models\Event;
use Domain\Federations\Models\Federation;
use Illuminate\Contracts\View\View;

class ReviewController extends Controller
{
    public function show(Event $event): View
    {
        $federationId = auth()->user()->getFederationId();
        $federation = Federation::findOrFail($federationId);

        return view('web.federation.evt_event.registration.review', [
            'event' => $event,
            'model' => $federation,
        ]);
    }
}
