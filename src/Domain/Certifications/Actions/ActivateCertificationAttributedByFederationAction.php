<?php

namespace Domain\Certifications\Actions;

use Domain\Certifications\Models\CertificationAttributed;
use Exception;

class ActivateCertificationAttributedByFederationAction
{
    private ActivateCertificationAttributedAction $activateCertification;

    public function __construct(
        ActivateCertificationAttributedAction $activateCertification
    ) {
        $this->activateCertification = $activateCertification;
    }

    /**
     * Activate a certification by federation
     *
     * @param  CertificationAttributed  $certification  The certification to activate
     *
     * @throws Exception If the certification is already active
     */
    public function __invoke(
        CertificationAttributed $certification
    ): void {
        $certification->load('federation');

        if ($certification->isActive()) {
            throw new Exception('The certification is already active');
        }

        // Activate the certification
        $this->activate($certification);
    }

    /**
     * Check if the certification should bypass slot discount
     * (kept for backward compatibility, always returns false now)
     */
    private function shouldBypassSlotDiscount(CertificationAttributed $certification): bool
    {
        return false;
    }

    private function activate(CertificationAttributed $certification): void
    {
        $this->activateCertification->__invoke($certification);
        $certification->activator()->associate($certification->federation);
        $certification->save();
    }
}
