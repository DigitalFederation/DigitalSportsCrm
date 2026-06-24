<?php

namespace Domain\Certifications\Actions;

use Carbon\Carbon;
use Domain\Certifications\Models\Certification;

class CalculateCertificationValidityDatesAction
{
    /**
     * Calculate the validity dates for a certification.
     * Most certifications are lifetime, but some may have expiration.
     */
    public function __invoke(Certification $certification): array
    {
        $startDate = Carbon::now();

        // Check if the certification has a validity period defined
        // This would need to be added to the certification model if needed
        // For now, we'll assume all certifications are lifetime

        return [
            'current_term_starts_at' => $startDate,
            'current_term_ends_at' => null, // Lifetime certification
        ];
    }
}
