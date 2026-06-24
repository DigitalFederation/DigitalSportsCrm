<?php

namespace Domain\Licenses\Transitions;

use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\CanceledLicenseAttributedState;
use Domain\Licenses\States\PendingValidationLicenseAttributedState;

class PendingValidationToCanceledTransition
{
    protected LicenseAttributed $licenseAttributed;
    protected ?string $reason;

    public function __construct(LicenseAttributed $licenseAttributed, ?string $reason = null)
    {
        $this->licenseAttributed = $licenseAttributed;
        $this->reason = $reason;
    }

    public function handle(): LicenseAttributed
    {
        $this->licenseAttributed->status_class = CanceledLicenseAttributedState::class;
        $this->licenseAttributed->cancelled_at = now();
        $this->licenseAttributed->validation_notes = $this->reason;
        $this->licenseAttributed->save();

        activity('license_attributed')
            ->performedOn($this->licenseAttributed)
            ->causedBy(auth()->user())
            ->withProperties([
                'transition' => 'pending_validation_to_canceled',
                'from' => PendingValidationLicenseAttributedState::class,
                'to' => CanceledLicenseAttributedState::class,
                'reason' => $this->reason,
            ])
            ->log('License validation rejected');

        return $this->licenseAttributed;
    }
}
