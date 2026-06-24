<?php

namespace Domain\Licenses\Actions;

use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Domain\Licenses\States\SuspendedLicenseAttributedState;

class DeleteLicenseAttributedAction
{
    public function __invoke(string $license_attributed_id): void
    {
        $responseToReturn = '';
        $license_attributed = LicenseAttributed::find($license_attributed_id);

        // If the license is pending, we can delete it, otherwise we can only suspend it
        if ($license_attributed->status_class == PendingLicenseAttributedState::class) {
            $license_attributed->delete();
            $responseToReturn = 'License removed successfully';
        } else {
            $license_attributed->status_class = SuspendedLicenseAttributedState::class;
            $license_attributed->save();
            $responseToReturn = 'License canceled successfully';
        }
    }
}
