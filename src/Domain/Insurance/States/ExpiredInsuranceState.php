<?php

namespace Domain\Insurance\States;

class ExpiredInsuranceState extends InsuranceState
{
    public function name(): string
    {
        return __('insurances.expired');
    }

    public function color(): string
    {
        return 'danger';
    }

    public function isActive(): bool
    {
        return false;
    }

    public function canBeActivated(): bool
    {
        return false; // Cannot reactivate expired insurance
    }

    public function canBeSuspended(): bool
    {
        return false;
    }

    public function canBeExpired(): bool
    {
        return false; // Already expired
    }
}
