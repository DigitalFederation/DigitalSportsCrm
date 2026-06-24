<?php

namespace Domain\EvtEvents\States;

use Carbon\Carbon;

class ActiveEventState extends EventState
{
    public function name(): string
    {
        return __('events.status.active');
    }

    public function allowsEnrollments(): bool
    {
        $now = Carbon::now();
        $start = $this->event->start_registration;
        $end = $this->event->end_registration_end_of_day;

        return ($start === null && $end === null) ||
            ($start !== null && $now->greaterThanOrEqualTo($start) && ($end === null || $now->lessThanOrEqualTo($end)));
    }

    public function color(): string
    {
        return 'green';
    }
}
