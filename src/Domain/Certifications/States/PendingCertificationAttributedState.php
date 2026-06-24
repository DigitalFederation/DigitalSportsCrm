<?php

namespace Domain\Certifications\States;

class PendingCertificationAttributedState extends CertificationAttributedState
{
    public function name(): string
    {
        return __('certifications.details.states.pending');
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
