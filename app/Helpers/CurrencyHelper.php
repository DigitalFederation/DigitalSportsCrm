<?php

if (! function_exists('money')) {
    /**
     * Format a monetary amount using config/currency.php.
     *
     * @param  int|null  $decimals  Overrides the configured decimal places.
     */
    function money(int|float|string|null $amount, ?int $decimals = null): string
    {
        return \Support\Money::format($amount, true, $decimals);
    }
}
