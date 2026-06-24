<?php

namespace Domain\Licenses\Actions;

use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;

class CalculateLicensePriceAction
{
    /**
     * Calculate the correct price for a license based on the requester.
     *
     * @param  License  $license  License with pricing information.
     * @param  string  $requesterType  Type of the requester ('Federation', 'Entity', 'Individual').
     * @return float|null The calculated price or null if not applicable.
     */
    public function __invoke(License $license, string $requesterType): ?float
    {

        // Determine base unit price based on requester type
        // Use specific price if configured (not null), otherwise fallback to general unit_value
        // Note: 0 is a valid price for free licenses
        switch ($requesterType) {
            case Individual::class:
                $basePrice = ($license->unit_value_individual !== null)
                    ? $license->unit_value_individual
                    : $license->unit_value;
                break;
            case Entity::class:
                $basePrice = ($license->unit_value_entity !== null)
                    ? $license->unit_value_entity
                    : $license->unit_value;
                break;
            case Federation::class:
                $basePrice = ($license->unit_value_federation !== null)
                    ? $license->unit_value_federation
                    : $license->unit_value;
                break;
            default:
                $basePrice = $license->unit_value;
                break;
        }

        // If base price is not set (null), return null
        // Allow 0 as a valid price for free licenses
        if ($basePrice === null) {
            return null;
        }

        // Calculate tax value
        $taxValue = 0;
        if ($license->tax_value !== null && $license->tax_value > 0) {
            $taxValue = $license->tax_value;
        } elseif ($license->tax_percentage !== null && $license->tax_percentage > 0) {
            $taxValue = $basePrice * ($license->tax_percentage / 100);
        }

        // Calculate and return total price (ensuring it returns a float, even for 0)
        return (float) ($basePrice + $taxValue);
    }
}
