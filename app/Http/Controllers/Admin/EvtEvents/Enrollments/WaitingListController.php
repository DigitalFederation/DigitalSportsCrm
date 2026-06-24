<?php

namespace App\Http\Controllers\Admin\EvtEvents\Enrollments;

use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;

class WaitingListController
{
    public function index(Event $event)
    {
        // Get all pending enrollments
        $pendingEnrollments = Enrollment::whereNull('activated_at')
            ->with([
                'event',
                'enrollable',
                'individualEnrollments.individual',
                'athleteEnrollments.discipline',
                'athleteEnrollments.individual',
                'athleteEnrollments.attributes.attribute',
                'coachEnrollments.individual',
                'refereeEnrollments.individual',
                'teamOfficialEnrollments.individual',
            ])
            ->paginate(100);

        if ($pendingEnrollments->isEmpty()) {
            return redirect()->back()->with('error', 'No pending enrollments found.');
        }

        $waitingLists = $this->aggregateWaitingLists($pendingEnrollments);

        return view('web.admin.evt_events.pending_enrollments.index', compact('waitingLists', 'event'));
    }

    private function aggregateWaitingLists($pendingEnrollments)
    {
        $waitingLists = [];

        foreach ($pendingEnrollments as $enrollment) {
            $federationId = $enrollment->enrollable->id;
            if (! isset($waitingLists[$federationId])) {
                $waitingLists[$federationId] = [
                    'federation_name' => $enrollment->enrollable->name,
                    'event_name' => $enrollment->event->name,
                    'event_date' => $enrollment->event->date, // Assuming Event model has a 'date' field
                    'event_location' => $enrollment->event->location, // Assuming Event model has a 'location' field
                    'enrollment_date' => $enrollment->created_at,
                    'event_id' => $enrollment->event->id,
                    'count' => 0,
                    'individual_count' => 0,
                    'athlete_count' => 0,
                    'coach_count' => 0,
                    'referee_count' => 0,
                    'team_official_count' => 0,
                ];
            }

            $waitingLists[$federationId]['count'] += 1;

            // Increment counts based on enrollment type
            if ($enrollment->individualEnrollments->isNotEmpty()) {
                $waitingLists[$federationId]['individual_count'] += $enrollment->individualEnrollments->count();
            }
            if ($enrollment->athleteEnrollments->isNotEmpty()) {
                $waitingLists[$federationId]['athlete_count'] += $enrollment->athleteEnrollments->count();
            }
            if ($enrollment->coachEnrollments->isNotEmpty()) {
                $waitingLists[$federationId]['coach_count'] += $enrollment->coachEnrollments->count();
            }
            if ($enrollment->refereeEnrollments->isNotEmpty()) {
                $waitingLists[$federationId]['referee_count'] += $enrollment->refereeEnrollments->count();
            }
            if ($enrollment->teamOfficialEnrollments->isNotEmpty()) {
                $waitingLists[$federationId]['team_official_count'] += $enrollment->teamOfficialEnrollments->count();
            }
        }

        return $waitingLists;
    }
}
