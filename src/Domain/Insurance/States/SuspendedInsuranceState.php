<?php

namespace Domain\Insurance\States;

class SuspendedInsuranceState extends InsuranceState
{
    public function name(): string
    {
        return __('insurances.suspended');
    }

    public function color(): string
    {
        return 'warning';
    }

    public function isActive(): bool
    {
        return false;
    }

    public function canBeActivated(): bool
    {
        return true; // Can be reactivated
    }

    public function canBeSuspended(): bool
    {
        return false; // Already suspended
    }

    public function canBeExpired(): bool
    {
        return true;
    }
}
