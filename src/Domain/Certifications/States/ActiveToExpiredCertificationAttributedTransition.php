<?php

namespace Domain\Certifications\States;

use Domain\Certifications\Models\CertificationAttributed;

class ActiveToExpiredCertificationAttributedTransition
{
    public function __invoke(CertificationAttributed $certificationAttributed): CertificationAttributed
    {
        if ($certificationAttributed->status_class !== ActiveCertificationAttributedState::class) {
            throw new \Exception('Certification must be in Active state to expire');
        }

        $certificationAttributed->status_class = ExpiredCertificationAttributedState::class;
        $certificationAttributed->save();

        activity('Certification')
            ->performedOn($certificationAttributed)
            ->event('expired')
            ->log('Certification expired.');

        return $certificationAttributed;
    }
}
