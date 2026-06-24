<?php

namespace Domain\EventApplications\States;

class PublishedApplicationState extends ApplicationState
{
    public function name(): string
    {
        return 'published';
    }

    public function color(): string
    {
        return '#a855f7';
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
