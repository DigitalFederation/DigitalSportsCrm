<?php

namespace App\Http\Controllers\Entity\EvtEvents;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Http\Controllers\Controller;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\Event;
use Illuminate\Contracts\View\View;

class RegistrationController extends Controller
{
    public function index(Event $event): View
    {
        $registrations = Event::with(['athleteEnrollments' => function ($query) {
            $query->where('entity_id', auth()->user()->getEntityId())
                ->whereIn('status_class', [
                    EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
                    EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value,
                ]);
        }])->paginate();

        return view('web.federation.evt_event.registration.index', compact('event', 'registrations'));
    }

    public function create(Event $event): View|\Illuminate\Http\RedirectResponse
    {
        $entityId = auth()->user()->getEntityId();
        $entity = Entity::findOrFail($entityId);

        if (! $event->allowsEnrollments()) {
            return redirect()
                ->route('entity.evt-events.events.show', $event->id)
                ->with('error', 'Registration is not allowed for this event at this time.');
        }

        return view('web.entity.evt_event.registration.create', [
            'event' => $event,
            'model' => $entity,
        ]);
    }
}
