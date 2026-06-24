<?php

namespace Domain\Certifications\States;

use Domain\Certifications\Models\CertificationAttributed;

class ApprovalToApprovedCertificationAttributedTransition
{
    public function __invoke(CertificationAttributed $certificationAttributed): CertificationAttributed
    {

        if ($certificationAttributed->status_class == DirectorApprovalCertificationAttributedState::class) {
            $certificationAttributed->status_class = DirectorApprovedCertificationAttributedState::class;
            $certificationAttributed->save();
        }

        return $certificationAttributed;
    }
}
