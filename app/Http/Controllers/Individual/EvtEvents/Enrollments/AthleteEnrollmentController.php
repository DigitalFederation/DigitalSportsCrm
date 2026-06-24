<?php

namespace App\Http\Controllers\Individual\EvtEvents\Enrollments;

use App\Http\Controllers\Controller;
use Domain\EvtEvents\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AthleteEnrollmentController extends Controller
{
    // Store
    public function store(Request $request)
    {
        $user = Auth::user();
        $event = Event::findOrFail($request->event_id);
        $individual = $user->individual;

        if (! $event->allowsEnrollments()) {
            return redirect()->back()->with('error', 'This event is not open for enrollments.');
        }

        $athleteEnrollment = $this->createAthleteEnrollment($event, $individual);

        return redirect()->route('individual.evt-events.events.show', $event->id)
            ->with('success', 'Your registration as an athlete has been successful.');
    }
}
