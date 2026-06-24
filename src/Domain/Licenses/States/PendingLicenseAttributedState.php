<?php

namespace Domain\Licenses\States;

class PendingLicenseAttributedState extends LicenseAttributedState
{
    public function name(): string
    {
        return __('licenses.state_pending');
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
