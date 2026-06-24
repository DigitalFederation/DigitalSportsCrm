<?php

namespace Domain\EventApplications\States;

class DraftApplicationState extends ApplicationState
{
    public function name(): string
    {
        return 'draft';
    }

    public function color(): string
    {
        return '#6b7280';
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
