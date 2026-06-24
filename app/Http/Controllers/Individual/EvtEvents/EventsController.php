<?php

namespace App\Http\Controllers\Individual\EvtEvents;

use App\Http\Controllers\Controller;
use Domain\EvtEvents\Actions\GetDisciplinesFromEventForIndividualAction;
use Domain\EvtEvents\Actions\GetIndividualEventsAction;
use Domain\EvtEvents\Actions\GetIneligibleDisciplinesForIndividualAction;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\Individuals\Models\Individual;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class EventsController extends Controller
{
    /**
     * Display a listing of organization events.
     */
    public function index(Request $request, GetIndividualEventsAction $getIndividualEventsAction): View|RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $individual = $user->individuals()->first();
        if (! $individual) {
            Log::warning('User missing individual association', ['user_id' => $user->id]);

            return redirect()->route('home')->with('error', 'Individual profile not found.');
        }

        $events = $getIndividualEventsAction->execute(null, 'organization', $request);
        $individualId = $individual->id;

        foreach ($events as $event) {
            $event->isEnrolled = $event->enrollments()
                ->where('enrollable_id', $individualId)
                ->where('enrollable_type', Individual::class)
                ->exists();
        }

        return view('web.individual.evt_events.events.index', compact('events'));
    }

    /**
     * Display a listing of competition events.
     */
    public function competitionsIndex(Request $request, GetIndividualEventsAction $getIndividualEventsAction): View|RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $individual = $user->individuals()->first();
        if (! $individual) {
            Log::warning('User missing individual association', ['user_id' => $user->id]);

            return redirect()->route('home')->with('error', 'Individual profile not found.');
        }

        $events = $getIndividualEventsAction->execute(null, null, $request);
        $individualId = $individual->id;

        foreach ($events as $event) {
            $event->isEnrolled = $event->enrollments()
                ->where('enrollable_id', $individualId)
                ->where('enrollable_type', Individual::class)
                ->exists();
        }

        return view('web.individual.evt_events.competitions.index', compact('events'));
    }

    public function show(
        Event $event,
        GetIndividualEventsAction $getIndividualEventsAction,
        GetDisciplinesFromEventForIndividualAction $getEligibleDisciplinesAction,
        GetIneligibleDisciplinesForIndividualAction $getIneligibleDisciplinesAction
    ) {
        if ($event->end_date < now()) {
            return redirect()->route('individual.evt-events.events.index')
                ->with('error', 'This event has already ended.');
        }

        $canAccessEvent = $getIndividualEventsAction->execute($event->id);
        if (! $canAccessEvent) {
            abort(403, 'You don\'t have the necessary roles to access this event');
        }

        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }
        $individual = $user->individuals()->first();
        if (! $individual) {
            Log::warning('User missing individual association during event show', ['user_id' => $user->id, 'event_id' => $event->id]);

            return redirect()->route('home')->with('error', 'Individual profile not found.');
        }

        // Check for eligible disciplines
        $eligibleDisciplines = $getEligibleDisciplinesAction->execute($event, $individual);
        $hasEligibleDisciplines = $eligibleDisciplines->isNotEmpty();

        // Always get ineligible disciplines
        $ineligibleDisciplines = $getIneligibleDisciplinesAction->execute($event, $individual);

        $event->load([
            'competition',
            'competitions.sport',
            'competitions.technicalDelegates',
            'competitions.venueCountry',
            'organizer.organizable.country',
            'pricing',
            'technicalDelegate.individual',
            'chiefJudge.individual',
            'competitionDirector.individual',
        ]);

        $attachments = Media::where('model_id', $event->id)
            ->where('collection_name', 'event-general-attachments')
            ->orderBy('name', 'ASC')
            ->get();

        $enrollment = Enrollment::where('event_id', $event->id)
            ->where('enrollable_id', $individual->id)
            ->where('enrollable_type', Individual::class)
            ->first();

        // Fetch activities based on properties, not just subject relationship
        $activities = collect();
        if ($user) { // Ensure user is logged in
            $activities = Activity::where('causer_type', $user->getMorphClass())
                ->where('causer_id', $user->id)
                // Filter based on properties stored consistently during logging
                ->whereJsonContains('properties->event_id', $event->id)
                ->whereJsonContains('properties->individual_id', $individual->id)
                // Optional: Filter by log name if you only want enrollment process logs
                // ->where('log_name', 'enrollment_process')
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        }

        return view('web.individual.evt_events.events.show', compact(
            'event',
            'attachments',
            'individual',
            'activities',
            'hasEligibleDisciplines',
            'ineligibleDisciplines'
        ));
    }
}
