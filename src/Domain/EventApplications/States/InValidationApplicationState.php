<?php

namespace Domain\EventApplications\States;

class InValidationApplicationState extends ApplicationState
{
    public function name(): string
    {
        return 'in_validation';
    }

    public function color(): string
    {
        return '#eab308';
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
            ApprovedApplicationState::class,
            RejectedApplicationState::class,
            ReturnedForCorrectionApplicationState::class,
        ];
    }
}
