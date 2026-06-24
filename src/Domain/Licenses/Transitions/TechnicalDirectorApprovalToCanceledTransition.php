<?php

namespace Domain\Licenses\Transitions;

use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\CanceledLicenseAttributedState;
use Domain\Licenses\States\PendingTechnicalDirectorApprovalLicenseAttributedState;

class TechnicalDirectorApprovalToCanceledTransition
{
    protected LicenseAttributed $licenseAttributed;
    protected string $rejectionReason;

    public function __construct(LicenseAttributed $licenseAttributed, string $rejectionReason)
    {
        $this->licenseAttributed = $licenseAttributed;
        $this->rejectionReason = $rejectionReason;
    }

    public function handle(): LicenseAttributed
    {
        // Validate current state
        if ($this->licenseAttributed->status_class !== PendingTechnicalDirectorApprovalLicenseAttributedState::class) {
            throw new \Exception('License must be in pending technical director approval state to be canceled by rejection');
        }

        // Transition to canceled state
        $this->licenseAttributed->status_class = CanceledLicenseAttributedState::class;
        $this->licenseAttributed->notes = 'Technical director rejection: ' . $this->rejectionReason;
        $this->licenseAttributed->save();

        // Log the transition
        activity('license_attributed')
            ->performedOn($this->licenseAttributed)
            ->causedBy(auth()->user())
            ->withProperties([
                'transition' => 'technical_director_rejection_to_canceled',
                'from' => PendingTechnicalDirectorApprovalLicenseAttributedState::class,
                'to' => CanceledLicenseAttributedState::class,
                'rejection_reason' => $this->rejectionReason,
            ])
            ->log('License canceled due to technical director rejection');

        return $this->licenseAttributed;
    }
}
