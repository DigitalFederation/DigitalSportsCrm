<?php

namespace Domain\Individuals\Actions;

use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;

class ValidLicenseAvailableToIndividualAction
{
    public function __invoke(Individual $individual, int $license_id): bool
    {
        $license = License::findOrFail($license_id);

        if ($license->professionalRole()->first()?->code === 'ATHLETE') {
            return true;
        }

        $certifications = $individual->certifications()->where('status_class', ActiveCertificationAttributedState::class)->get();

        $license_available_to_individual = false;
        foreach ($certifications as $certification) {
            if ($certification->license_id === $license_id) {
                $license_available_to_individual = true;
            }
        }

        return $license_available_to_individual;
    }
}
