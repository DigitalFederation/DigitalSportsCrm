<?php

namespace Domain\Licenses\States;

class PendingTechnicalDirectorApprovalLicenseAttributedState extends LicenseAttributedState
{
    /**
     * Get the friendly name of the state
     */
    public function name(): string
    {
        return __('licenses.state_pending_technical_director_approval');
    }

    /**
     * Get the color of the state
     */
    public function color(): string
    {
        return '#F59E0B'; // Yellow/Amber - waiting for approval
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
            PendingValidationLicenseAttributedState::class,  // All technical directors approved
            CanceledLicenseAttributedState::class,           // Any technical director rejected
        ];
    }
}
