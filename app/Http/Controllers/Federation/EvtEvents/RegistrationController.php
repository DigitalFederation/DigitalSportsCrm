<?php

namespace App\Http\Controllers\Federation\EvtEvents;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Http\Controllers\Controller;
use Domain\EvtEvents\Models\Event;
use Domain\Federations\Models\Federation;
use Illuminate\Contracts\View\View;

class RegistrationController extends Controller
{
    public function index(Event $event): View
    {
        $registrations = Event::with(['athleteEnrollments' => function ($query) {
            $query->where('federation_id', auth()->user()->getFederationId())
                ->whereIn('status_class', [
                    EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
                    EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT->value,
                ]);
        }])->paginate();

        return view('web.federation.evt_event.registration.index', compact('event', 'registrations'));
    }

    public function create(Event $event): View|\Illuminate\Http\RedirectResponse
    {
        $federationId = auth()->user()->getFederationId();

        $model = Federation::query()->findOrFail($federationId);

        if (! $event->allowsEnrollments()) {
            return redirect()
                ->route('federation.evt-events.events.show', $event->id)
                ->with('error', 'Registration is not allowed for this event at this time.');
        }

        return view('web.federation.evt_event.registration.create', [
            'event' => $event,
            'model' => $model,
        ]);
    }
}
