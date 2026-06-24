<?php

namespace Domain\EventApplications\States;

class ApprovedApplicationState extends ApplicationState
{
    public function name(): string
    {
        return 'approved';
    }

    public function color(): string
    {
        return '#22c55e';
    }

    public function canEdit(): bool
    {
        return false;
    }

    public function canSubmit(): bool
    {
        return false;
    }

    protected function allowedTransitions(): array
    {
        return [
            PublishedApplicationState::class,
            ReturnedForCorrectionApplicationState::class,
            RejectedApplicationState::class,
        ];
    }
}
