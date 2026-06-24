<?php

namespace App\Http\Controllers\Entity\EvtEvents;

use App\Http\Controllers\Controller;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\Event;
use Illuminate\Contracts\View\View;

class ConfirmedEnrollmentsController extends Controller
{
    public function show(Event $event): View
    {
        $entityId = auth()->user()->getEntityId();
        $entity = Entity::findOrFail($entityId);

        return view('web.entity.evt_event.registration.confirmed', [
            'event' => $event,
            'model' => $entity,
        ]);
    }
}
