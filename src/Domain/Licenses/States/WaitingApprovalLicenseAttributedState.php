<?php

namespace Domain\Licenses\States;

class WaitingApprovalLicenseAttributedState extends LicenseAttributedState
{
    public function name(): string
    {
        return __('licenses.state_waiting_approval');
    }

    public function isActive(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'pending';
    }
}
