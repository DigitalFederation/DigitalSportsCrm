<?php

namespace Domain\Certifications\States;

class RejectedCertificationAttributedState extends CertificationAttributedState
{
    public function name(): string
    {
        return __('certifications.details.states.rejected');
    }

    public function isActive(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'canceled-state';
    }

    public function isProvisional(): bool
    {
        return false;
    }
}
