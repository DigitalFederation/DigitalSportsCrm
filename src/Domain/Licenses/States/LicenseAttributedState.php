<?php

namespace Domain\Licenses\States;

use Domain\Licenses\Models\LicenseAttributed;

abstract class LicenseAttributedState
{
    protected LicenseAttributed $licenseAttributed;

    public function __construct(LicenseAttributed $licenseAttributed)
    {
        $this->licenseAttributed = $licenseAttributed;
    }

    abstract public function name(): string;

    abstract public function isActive(): bool;

    abstract public function color(): string;
}
