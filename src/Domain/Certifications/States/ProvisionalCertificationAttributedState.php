<?php

namespace Domain\Certifications\States;

class ProvisionalCertificationAttributedState extends CertificationAttributedState
{
    public function name(): string
    {
        return __('certifications.details.states.provisional');
    }

    public function isProvisional(): bool
    {
        return true;
    }

    public function isActive(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'pending-state';
    }
}
