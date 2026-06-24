<?php

namespace Domain\Certifications\States;

use Domain\Certifications\Models\CertificationAttributed;

abstract class CertificationAttributedState
{
    protected CertificationAttributed $certificationAttributed;

    public function __construct(CertificationAttributed $certificationAttributed)
    {
        $this->certificationAttributed = $certificationAttributed;
    }

    abstract public function name(): string;

    abstract public function isActive(): bool;

    abstract public function isProvisional(): bool;

    abstract public function color(): string;
}
