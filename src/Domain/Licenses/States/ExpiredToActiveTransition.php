<?php

namespace Domain\Licenses\States;

use Domain\Licenses\Models\LicenseAttributed;

class ExpiredToActiveTransition
{
    public function __invoke(LicenseAttributed $licenseAttributed): LicenseAttributed
    {
        if ($licenseAttributed->status_class !== ExpiredLicenseAttributedState::class) {
            throw new \Exception('License must be in Expired state to renew');
        }

        $licenseAttributed->status_class = ActiveLicenseAttributedState::class;
        $licenseAttributed->activated_at = now();
        $licenseAttributed->save();

        activity('License')
            ->performedOn($licenseAttributed)
            ->event('renewed')
            ->log('License renewed through repurchase');

        return $licenseAttributed;
    }
}
