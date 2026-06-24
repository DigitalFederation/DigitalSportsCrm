<?php

namespace Domain\Certifications\States;

use Domain\Certifications\Models\CertificationAttributed;

class PendingToApprovalCertificationAttributedTransition
{
    public function __invoke(CertificationAttributed $certificationAttributed): CertificationAttributed
    {
        if ($certificationAttributed->status_class == PendingCertificationAttributedState::class) {
            $certificationAttributed->status_class = DirectorApprovalCertificationAttributedState::class;
            $certificationAttributed->save();
        }

        return $certificationAttributed;
    }
}
