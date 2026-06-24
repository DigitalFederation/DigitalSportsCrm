<?php

namespace Domain\Documents\Actions;

/**
 * Description of CalculateAddTaxAction
 *
 * @author luispinto
 */
class CalculateAddTaxAction
{
    public function __invoke(float $value, int $quantity = 1, ?float $tax = null): array
    {

        $net_value = $value * $quantity;
        $tax_value = ($tax == null) ? 0 : $net_value * $tax;

        return [
            'quantity' => $quantity,
            'tax_number' => ($tax == null) ? 0 : $tax * 100,
            'tax_value' => $tax_value,
            'net_value' => $net_value,
            'total_value' => $tax_value + $net_value,

        ];
    }
}
