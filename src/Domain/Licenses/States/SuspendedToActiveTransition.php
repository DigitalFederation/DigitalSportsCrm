<?php

namespace Domain\Licenses\States;

use Domain\Licenses\Models\LicenseAttributed;

class SuspendedToActiveTransition
{
    public function __invoke(LicenseAttributed $licenseAttributed): LicenseAttributed
    {
        if ($licenseAttributed->status_class !== SuspendedLicenseAttributedState::class) {
            throw new \Exception('License must be in Suspended state to reactivate');
        }

        $licenseAttributed->status_class = ActiveLicenseAttributedState::class;
        $licenseAttributed->save();

        activity('License')
            ->performedOn($licenseAttributed)
            ->event('reactivated')
            ->log('License reactivated by federation admin');

        return $licenseAttributed;
    }
}
