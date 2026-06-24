<?php

namespace Domain\Certifications\States;

use Domain\Certifications\Models\CertificationAttributed;

class PendingToActiveCertificationAttributedTransition
{
    public function __invoke(CertificationAttributed $certificationAttributed): CertificationAttributed
    {
        if (! $certificationAttributed->isActive()) {
            $certificationAttributed->status_class = ActiveCertificationAttributedState::class;
            $certificationAttributed->save();
        }

        return $certificationAttributed;
    }
}
