<?php

namespace Domain\Insurance\States;

class InactiveInsuranceState extends InsuranceState
{
    public function name(): string
    {
        return __('insurances.inactive');
    }

    public function color(): string
    {
        return 'secondary';
    }

    public function isActive(): bool
    {
        return false;
    }

    public function canBeActivated(): bool
    {
        return true;
    }

    public function canBeSuspended(): bool
    {
        return false;
    }

    public function canBeExpired(): bool
    {
        return true;
    }
}
