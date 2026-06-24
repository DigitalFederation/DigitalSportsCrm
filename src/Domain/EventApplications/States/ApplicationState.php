<?php

namespace Domain\EventApplications\States;

use Domain\EventApplications\Models\EventApplication;

abstract class ApplicationState
{
    protected EventApplication $application;

    public function __construct(EventApplication $application)
    {
        $this->application = $application;
    }

    abstract public function name(): string;

    abstract public function color(): string;

    public function canEdit(): bool
    {
        return false;
    }

    public function canSubmit(): bool
    {
        return false;
    }

    public function canDelete(): bool
    {
        return false;
    }

    public function canTransitionTo(string $state): bool
    {
        return in_array($state, $this->allowedTransitions());
    }

    abstract protected function allowedTransitions(): array;
}
