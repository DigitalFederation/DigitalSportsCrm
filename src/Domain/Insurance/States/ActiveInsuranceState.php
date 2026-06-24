<?php

namespace Domain\Insurance\States;

class ActiveInsuranceState extends InsuranceState
{
    public function name(): string
    {
        return __('insurances.active');
    }

    public function color(): string
    {
        return 'success';
    }

    public function isActive(): bool
    {
        // Check if within date range
        $now = now();

        return $this->insurance->start_date <= $now && $this->insurance->end_date >= $now;
    }

    public function canBeActivated(): bool
    {
        return false; // Already active
    }

    public function canBeSuspended(): bool
    {
        return true;
    }

    public function canBeExpired(): bool
    {
        return true;
    }
}
