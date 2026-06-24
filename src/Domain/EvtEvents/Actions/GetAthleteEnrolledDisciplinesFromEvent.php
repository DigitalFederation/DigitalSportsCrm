<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Event;
use Domain\Individuals\Models\Individual;
use Illuminate\Support\Collection;

class GetAthleteEnrolledDisciplinesFromEvent
{
    /**
     * Get disciplines that an athlete is enrolled in for a specific event
     */
    public function execute(Event $event, Individual $individual): Collection
    {
        return $event->athleteEnrollments()
            ->where('individual_id', $individual->id)
            ->with('discipline')
            ->get()
            ->pluck('discipline')
            ->filter();
    }
}
