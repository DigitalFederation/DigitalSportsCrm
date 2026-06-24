<?php

namespace Domain\Documents\Actions;

/**
 * Description of CalculateSubtractTaxAction
 *
 * @author luispinto
 */
class CalculateSubtractTaxAction
{
    public function __invoke(float $unit_value, int $quantity = 1, ?float $tax = null): array
    {

        $net_value = $unit_value * $quantity;
        $tax_value = ($tax == null) ? null : $net_value * ($tax / 100);
        $total_value = $net_value + $tax_value;

        return [
            'quantity' => $quantity,
            'tax_number' => ($tax == null) ? 0 : $tax,
            'tax_value' => $tax_value,
            'net_value' => $net_value,
            'total_value' => $total_value,

        ];
    }
}
