<?php

namespace Domain\Certifications\States;

class CanceledCertificationAttributedState extends CertificationAttributedState
{
    public function name(): string
    {
        return __('certifications.details.states.canceled');
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
        return 'canceled-state';
    }
}
