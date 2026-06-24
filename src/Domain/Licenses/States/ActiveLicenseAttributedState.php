<?php

namespace Domain\Licenses\States;

class ActiveLicenseAttributedState extends LicenseAttributedState
{
    public function name(): string
    {
        return __('licenses.state_active');
    }

    public function isActive(): bool
    {
        return true;
    }

    public function color(): string
    {
        return 'active-state';
    }
}
