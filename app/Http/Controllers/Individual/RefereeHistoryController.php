<?php

namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use Domain\EvtEvents\States\ActiveRefereeEnrollmentState;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RefereeHistoryController extends Controller
{
    /**
     * Display the referee history for the authenticated individual.
     */
    public function index(): View
    {
        $user = Auth::user();

        if (! $user->individual) {
            abort(403, 'No individual profile found');
        }

        $refereeEnrollments = $user->individual->refereeEnrollments()
            ->with([
                'event.sport',
                'event.organizer',
                'event.competition',
                'federation',
                'refereeFunctionAssignments.refereeFunction',
            ])
            ->whereHas('event')
            ->where('evt_referees_enrollment.status_class', ActiveRefereeEnrollmentState::class)
            ->join('evt_events', 'evt_referees_enrollment.event_id', '=', 'evt_events.id')
            ->orderByDesc('evt_events.start_date')
            ->select('evt_referees_enrollment.*')
            ->get();

        return view('web.individual.referee_history.index', compact('refereeEnrollments'));
    }
}
