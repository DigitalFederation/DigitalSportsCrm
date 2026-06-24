<?php

namespace Domain\EvtEvents\States;

class CandidacyEventState extends EventState
{
    public function name(): string
    {
        return __('events.status.candidacy');
    }

    public function allowsEnrollments(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'blue';
    }
}
