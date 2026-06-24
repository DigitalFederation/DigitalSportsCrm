<?php

namespace Domain\Certifications\States;

use Domain\Certifications\Models\CertificationAttributed;

class ActiveToCanceledCertificationAttributedTransition
{
    public function __invoke(CertificationAttributed $certificationAttributed): CertificationAttributed
    {
        if ($certificationAttributed->isActive()) {
            $certificationAttributed->status_class = CanceledCertificationAttributedState::class;
            $certificationAttributed->save();
        }

        return $certificationAttributed;
    }
}
