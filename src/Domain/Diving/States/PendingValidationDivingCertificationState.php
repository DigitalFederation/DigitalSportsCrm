<?php

namespace Domain\Diving\States;

class PendingValidationDivingCertificationState extends DivingCertificationState
{
    public function name(): string
    {
        return 'pending_validation';
    }

    public function color(): string
    {
        return '#f59e0b'; // Amber/Orange
    }

    public function isActive(): bool
    {
        return false;
    }

    public function canBeValidated(): bool
    {
        return true;
    }

    public function canBeRevoked(): bool
    {
        return false;
    }
}
