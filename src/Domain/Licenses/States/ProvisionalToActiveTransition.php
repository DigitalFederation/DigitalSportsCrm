<?php

namespace Domain\Licenses\States;

use Domain\Licenses\Models\LicenseAttributed;

class ProvisionalToActiveTransition
{
    public function __invoke(LicenseAttributed $licenseAttributed): LicenseAttributed
    {
        if (! $licenseAttributed->isActive()) {
            $licenseAttributed->status_class = ActiveLicenseAttributedState::class;
            $licenseAttributed->activated_at = now();
            $licenseAttributed->save();
        }

        return $licenseAttributed;
    }
}
