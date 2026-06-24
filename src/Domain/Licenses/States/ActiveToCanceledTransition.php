<?php

namespace Domain\Licenses\States;

use Domain\Licenses\Models\LicenseAttributed;

class ActiveToCanceledTransition
{
    public function __invoke(LicenseAttributed $licenseAttributed): LicenseAttributed
    {
        if ($licenseAttributed->isActive()) {
            $licenseAttributed->status_class = CanceledLicenseAttributedState::class;
            $licenseAttributed->save();
        }

        return $licenseAttributed;
    }
}
