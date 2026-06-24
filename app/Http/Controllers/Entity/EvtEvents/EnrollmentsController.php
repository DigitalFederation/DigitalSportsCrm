<?php

namespace App\Http\Controllers\Entity\EvtEvents;

use App\Http\Controllers\Controller;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\Event;
use Illuminate\Contracts\View\View;

class EnrollmentsController extends Controller
{
    public function index(Event $event): View
    {
        return view('web.entity.evt_event.enrollment.index');
    }

    public function create(Event $event, string $type)
    {
        $entityId = auth()->user()->getEntityId();
        $entity = Entity::findOrFail($entityId);

        return view('web.entity.evt_event.enrollment.create', [
            'event' => $event,
            'type' => $type,
            'model' => $entity,
        ]);
    }
}
