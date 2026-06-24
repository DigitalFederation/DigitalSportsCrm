<?php

namespace Domain\EventApplications\States;

class ReturnedForCorrectionApplicationState extends ApplicationState
{
    public function name(): string
    {
        return 'returned_for_correction';
    }

    public function color(): string
    {
        return '#f97316';
    }

    public function canEdit(): bool
    {
        return true;
    }

    public function canSubmit(): bool
    {
        return true;
    }

    public function canDelete(): bool
    {
        return true;
    }

    protected function allowedTransitions(): array
    {
        return [
            SubmittedApplicationState::class,
        ];
    }
}
