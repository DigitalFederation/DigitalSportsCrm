<?php

namespace Domain\Diving\States;

class ExpiredDivingCertificationState extends DivingCertificationState
{
    public function name(): string
    {
        return 'expired';
    }

    public function color(): string
    {
        return '#6b7280'; // Gray
    }

    public function isActive(): bool
    {
        return false;
    }

    public function canBeValidated(): bool
    {
        return false;
    }

    public function canBeRevoked(): bool
    {
        return false;
    }
}
