<?php

namespace Domain\Licenses\States;

class PendingValidationLicenseAttributedState extends LicenseAttributedState
{
    /**
     * Get the friendly name of the state
     */
    public function name(): string
    {
        return __('licenses.state_pending_validation');
    }

    /**
     * Get the color of the state
     */
    public function color(): string
    {
        return '#F59E0B'; // Yellow
    }

    /**
     * Determine if the license is active
     */
    public function isActive(): bool
    {
        return false;
    }

    /**
     * Get the valid transitions from this state
     */
    public function canTransitionTo(): array
    {
        return [
            PendingLicenseAttributedState::class,  // Approved, awaiting payment
            ActiveLicenseAttributedState::class,    // Approved and no payment needed
            CanceledLicenseAttributedState::class,  // Rejected
        ];
    }
}
