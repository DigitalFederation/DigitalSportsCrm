<?php

namespace Domain\EventApplications\States;

class SubmittedApplicationState extends ApplicationState
{
    public function name(): string
    {
        return 'submitted';
    }

    public function color(): string
    {
        return '#3b82f6';
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
            InValidationApplicationState::class,
        ];
    }
}
