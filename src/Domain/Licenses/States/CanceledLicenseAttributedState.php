<?php

namespace Domain\Licenses\States;

class CanceledLicenseAttributedState extends LicenseAttributedState
{
    public function name(): string
    {
        return __('licenses.state_canceled');
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
