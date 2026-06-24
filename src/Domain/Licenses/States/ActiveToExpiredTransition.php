<?php

namespace Domain\Licenses\States;

use Domain\Licenses\Models\LicenseAttributed;

class ActiveToExpiredTransition
{
    public function __invoke(LicenseAttributed $licenseAttributed): LicenseAttributed
    {
        if ($licenseAttributed->status_class !== ActiveLicenseAttributedState::class) {
            throw new \Exception('License must be in Active state to expire');
        }

        $licenseAttributed->status_class = ExpiredLicenseAttributedState::class;
        $licenseAttributed->save();

        activity('License')
            ->performedOn($licenseAttributed)
            ->event('expired')
            ->log('License expired.');

        return $licenseAttributed;
    }
}
