<?php

namespace Domain\Licenses\Actions;

use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ExpiredLicenseAttributedState;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Domain\Licenses\States\SuspendedLicenseAttributedState;

class SuspendLicenseAttributedAction
{
    public function __invoke(LicenseAttributed $license): LicenseAttributed
    {
        // Check if the license is already suspended or pending
        if (
            $license->status_class == SuspendedLicenseAttributedState::class ||
            $license->status_class == PendingLicenseAttributedState::class
        ) {
            throw new \Exception('License is already in a suspended or pending state.');
        }

        // Check if the license is expired
        if ($license->status_class == ExpiredLicenseAttributedState::class) {
            throw new \Exception('Cannot suspend an expired license.');
        }

        $license->status_class = SuspendedLicenseAttributedState::class;
        $license->save();

        $oldStatus = $license->getOriginal('status_class');

        activity('License')
            ->performedOn($license)
            ->causedBy(auth()->user())
            ->event('suspended')
            ->withProperties([
                'old_status' => $oldStatus,
                'new_status' => SuspendedLicenseAttributedState::class,
            ])
            ->log('License status changed to Suspended.');

        return $license;
    }
}
