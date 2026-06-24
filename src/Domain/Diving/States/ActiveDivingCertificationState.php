<?php

namespace Domain\Diving\States;

class ActiveDivingCertificationState extends DivingCertificationState
{
    public function name(): string
    {
        return 'active';
    }

    public function color(): string
    {
        return '#10b981'; // Green
    }

    public function isActive(): bool
    {
        return true;
    }

    public function canBeValidated(): bool
    {
        return false;
    }

    public function canBeRevoked(): bool
    {
        return true;
    }
}
