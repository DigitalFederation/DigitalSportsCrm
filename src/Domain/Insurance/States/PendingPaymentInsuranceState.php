<?php

namespace Domain\Insurance\States;

class PendingPaymentInsuranceState extends InsuranceState
{
    public function name(): string
    {
        return __('insurances.pending_payment');
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
