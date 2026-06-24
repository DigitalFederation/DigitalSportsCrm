<?php

namespace Domain\EventApplications\States;

class RejectedApplicationState extends ApplicationState
{
    public function name(): string
    {
        return 'rejected';
    }

    public function color(): string
    {
        return '#ef4444';
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
        return [];
    }
}
