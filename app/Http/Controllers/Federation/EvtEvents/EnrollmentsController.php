<?php

namespace App\Http\Controllers\Federation\EvtEvents;

use App\Http\Controllers\Controller;
use Domain\EvtEvents\Actions\CheckEventRegisteredAthletesAction;
use Domain\EvtEvents\Models\Event;
use Illuminate\Contracts\View\View;

class EnrollmentsController extends Controller
{
    public function index(Event $event): View
    {
        return view('web.federation.evt_event.enrollment.index');
    }

    public function create(Event $event, string $type)
    {
        $model = auth()->user()->getFederation();
        $hasRegistered = (new CheckEventRegisteredAthletesAction)->execute($event, $model->id);

        /*
        if (! $hasRegistered) {
            return redirect()
                ->route('federation.evt-events.events.enrollments.pre-register', $event)
                ->with('error', __('Please register and complete payment for athletes first'));
        }
                */

        if (! $event->canEnroll('athlete')) {
            return redirect()
                ->route('federation.evt-events.events.show', $event->id)
                ->with('error', __('New enrollments are not allowed for this event at this time.'));
        }

        return view('web.federation.evt_event.enrollment.create', compact(
            'event',
            'type',
            'model')
        );
    }
}
