<?php

namespace Domain\Licenses\States;

class ProvisionalLicenseAttributedState extends LicenseAttributedState
{
    public function name(): string
    {
        return __('licenses.state_provisional');
    }

    public function isActive(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'pending-state';
    }
}
