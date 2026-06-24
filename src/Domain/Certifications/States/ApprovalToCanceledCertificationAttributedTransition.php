<?php

namespace Domain\Certifications\States;

use Domain\Certifications\Models\CertificationAttributed;

class ApprovalToCanceledCertificationAttributedTransition
{
    public function __invoke(CertificationAttributed $certificationAttributed): CertificationAttributed
    {
        if ($certificationAttributed->status_class == DirectorApprovalCertificationAttributedState::class) {
            $certificationAttributed->status_class = CanceledCertificationAttributedState::class;
            $certificationAttributed->save();
        }

        return $certificationAttributed;
    }
}
