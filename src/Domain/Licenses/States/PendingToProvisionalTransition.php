<?php

namespace Domain\Licenses\States;

use App\Events\LicenseAttributedCreatedEvent;
use Domain\Licenses\Models\LicenseAttributed;

class PendingToProvisionalTransition
{
    public function __invoke(LicenseAttributed $licenseAttributed): LicenseAttributed
    {
        if (! $licenseAttributed->isActive()) {
            $licenseAttributed->status_class = ProvisionalLicenseAttributedState::class;
            $licenseAttributed->save();

            // Generate the document
            event(new LicenseAttributedCreatedEvent([$licenseAttributed], false));
        }

        return $licenseAttributed;
    }
}
