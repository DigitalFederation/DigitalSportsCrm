<?php

namespace Domain\Licenses\Actions;

use App\Events\LicenseAttributedCreatedEvent;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\PendingLicenseAttributedState;

class ApproveLicenseAttributedAction
{
    public function __invoke(LicenseAttributed $licenseAttributed)
    {
        $licenseAttributed->status_class = PendingLicenseAttributedState::class;
        $licenseAttributed->save();

        // Emit the LicenseAttributedCreated event in case we need to create a document
        if ($licenseAttributed->total_value > 0) {
            event(new LicenseAttributedCreatedEvent([$licenseAttributed], true));
        }

        $this->logLicenseActivity($licenseAttributed);

    }

    private function logLicenseActivity($license)
    {
        activity('License')
            ->performedOn($license)
            ->event('approved')
            ->withProperties((array) $license)
            ->log('License was approved by CMAS HQ.');
    }
}
