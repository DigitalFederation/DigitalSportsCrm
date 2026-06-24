<?php

namespace Domain\Certifications\States;

class SuspendedCertificationAttributedState extends CertificationAttributedState
{
    public function name(): string
    {
        return __('certifications.details.states.suspended');
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
