<?php

namespace Domain\Licenses\Transitions;

use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\PendingTechnicalDirectorApprovalLicenseAttributedState;
use Domain\Licenses\States\PendingValidationLicenseAttributedState;

class TechnicalDirectorApprovalToPendingValidationTransition
{
    protected LicenseAttributed $licenseAttributed;

    public function __construct(LicenseAttributed $licenseAttributed)
    {
        $this->licenseAttributed = $licenseAttributed;
    }

    public function handle(): LicenseAttributed
    {
        // Validate current state
        if ($this->licenseAttributed->status_class !== PendingTechnicalDirectorApprovalLicenseAttributedState::class) {
            throw new \Exception('License must be in pending technical director approval state to transition to pending validation');
        }

        // Ensure all technical directors have approved
        $allDirectors = $this->licenseAttributed->divingTechnicalDirectors()
            ->where('status_class', \Domain\Diving\States\AssignedDivingTechnicalDirectorState::class)
            ->get();

        foreach ($allDirectors as $director) {
            if (! $director->hasApproved()) {
                throw new \Exception('All technical directors must approve before transitioning to pending validation');
            }
        }

        // Transition to pending validation state
        $this->licenseAttributed->status_class = PendingValidationLicenseAttributedState::class;
        $this->licenseAttributed->save();

        // Log the transition
        activity('license_attributed')
            ->performedOn($this->licenseAttributed)
            ->causedBy(auth()->user())
            ->withProperties([
                'transition' => 'technical_director_approval_to_pending_validation',
                'from' => PendingTechnicalDirectorApprovalLicenseAttributedState::class,
                'to' => PendingValidationLicenseAttributedState::class,
            ])
            ->log('All technical directors approved - license pending admin validation');

        return $this->licenseAttributed;
    }
}
