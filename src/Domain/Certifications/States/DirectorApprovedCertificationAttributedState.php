<?php

namespace Domain\Certifications\States;

class DirectorApprovedCertificationAttributedState extends CertificationAttributedState
{
    public function name(): string
    {
        return __('certifications.details.states.director_approved');
    }

    public function isActive(): bool
    {
        return false;
    }

    public function isProvisional(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'pending-state';
    }
}
