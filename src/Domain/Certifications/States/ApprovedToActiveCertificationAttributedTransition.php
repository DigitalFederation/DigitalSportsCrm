<?php

namespace Domain\Certifications\States;

use Domain\Certifications\Models\CertificationAttributed;

class ApprovedToActiveCertificationAttributedTransition
{
    public function __invoke(CertificationAttributed $certificationAttributed): CertificationAttributed
    {

        if ($certificationAttributed->status_class == DirectorApprovedCertificationAttributedState::class) {
            $certificationAttributed->status_class = ActiveCertificationAttributedState::class;
            $certificationAttributed->save();
        }

        return $certificationAttributed;
    }
}
