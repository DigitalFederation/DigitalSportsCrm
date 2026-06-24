<?php

namespace Domain\Licenses\States;

use Domain\Licenses\Models\LicenseAttributed;

class CanceledToActiveTransition
{
    public function __invoke(LicenseAttributed $licenseAttributed): LicenseAttributed
    {
        if (! $licenseAttributed->isActive()) {
            $licenseAttributed->status_class = ActiveLicenseAttributedState::class;
            $licenseAttributed->save();
        }

        return $licenseAttributed;
    }
}
