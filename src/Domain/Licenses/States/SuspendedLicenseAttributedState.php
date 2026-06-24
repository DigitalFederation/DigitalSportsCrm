<?php

namespace Domain\Licenses\States;

class SuspendedLicenseAttributedState extends LicenseAttributedState
{
    public function name(): string
    {
        return __('licenses.state_suspended');
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
