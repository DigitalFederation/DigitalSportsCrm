<?php

namespace Domain\Certifications\States;

class ActiveCertificationAttributedState extends CertificationAttributedState
{
    public function name(): string
    {
        return __('certifications.details.states.active');
    }

    public function isActive(): bool
    {
        return true;
    }

    public function isProvisional(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'active-state';
    }
}
