<?php

namespace Domain\Diving\States;

class RevokedDivingCertificationState extends DivingCertificationState
{
    public function name(): string
    {
        return 'revoked';
    }

    public function color(): string
    {
        return '#ef4444'; // Red
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
