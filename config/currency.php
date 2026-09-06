<?php

return [
    'code' => env('CURRENCY_CODE', 'EUR'),
    'symbol' => env('CURRENCY_SYMBOL', '€'),
    'position' => env('CURRENCY_SYMBOL_POSITION', 'after'),  // before|after
    'space' => env('CURRENCY_SYMBOL_SPACE', true),        // "1,00 €" vs "1,00€"
    'decimals' => env('CURRENCY_DECIMALS', 2),
    'decimal_separator' => env('CURRENCY_DECIMAL_SEPARATOR', ','),
    'thousands_separator' => env('CURRENCY_THOUSANDS_SEPARATOR', '.'),
];
