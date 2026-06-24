<?php

namespace Domain\Licenses\States;

class ExpiredLicenseAttributedState extends LicenseAttributedState
{
    public function name(): string
    {
        return __('licenses.state_expired');
    }

    public function isActive(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'canceled-state';
    }
}
