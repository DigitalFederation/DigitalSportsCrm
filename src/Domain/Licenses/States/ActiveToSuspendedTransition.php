<?php

namespace Domain\Licenses\States;

use Domain\Licenses\Models\LicenseAttributed;

class ActiveToSuspendedTransition
{
    public function __invoke(LicenseAttributed $licenseAttributed, string $reason = ''): LicenseAttributed
    {
        if ($licenseAttributed->status_class !== ActiveLicenseAttributedState::class) {
            throw new \Exception('License must be in Active state to suspend');
        }

        $licenseAttributed->status_class = SuspendedLicenseAttributedState::class;
        $licenseAttributed->save();

        activity('License')
            ->performedOn($licenseAttributed)
            ->event('suspended')
            ->withProperties(['reason' => $reason])
            ->log('License suspended by federation admin');

        return $licenseAttributed;
    }
}
