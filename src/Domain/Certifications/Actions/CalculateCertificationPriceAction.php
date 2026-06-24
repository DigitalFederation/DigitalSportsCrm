<?php

namespace Domain\Certifications\Actions;

use Domain\Certifications\Models\Certification;

class CalculateCertificationPriceAction
{
    /**
     * Calculate the price for a certification based on the selected price option.
     *
     * @param  string  $priceOption  'digital' or 'digital_plus_card'
     * @param  bool  $includeTax  Whether to include tax in the calculation
     */
    public function __invoke(Certification $certification, string $priceOption = 'digital', bool $includeTax = true): float
    {
        // Get the base price for the selected option
        $basePrice = $certification->getPriceForOption($priceOption);

        // Apply tax if configured and requested
        if ($includeTax && $certification->tax_percentage > 0) {
            return $basePrice * (1 + ($certification->tax_percentage / 100));
        }

        return $basePrice;
    }

    /**
     * Legacy method for backwards compatibility.
     * Use __invoke with price option instead.
     *
     * @deprecated Use __invoke with priceOption parameter instead
     */
    public function calculateForRequester(Certification $certification, string $requesterType): float
    {
        // For backwards compatibility, use the digital price
        return $this->__invoke($certification, 'digital');
    }
}
