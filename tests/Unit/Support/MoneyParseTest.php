<?php

use Support\Money;

/**
 * Money::parse() reads a human-typed amount back into a float. It is the inverse
 * of Money::format(), so an operator can copy a fee off the screen and type it
 * back regardless of how the installation is configured.
 */
it('reads the format the application displays', function (string $input, ?float $expected) {
    expect(Money::parse($input))->toBe($expected);
})->with([
    // Plain
    ['250', 250.0],
    ['1234', 1234.0],
    ['0', 0.0],

    // Either decimal separator
    ['1234.56', 1234.56],
    ['1234,56', 1234.56],
    ['250,00', 250.0],
    ['0,5', 0.5],
    ['1,5', 1.5],

    // Both separators: the trailing one wins
    ['1.234,56', 1234.56],
    ['1,234.56', 1234.56],
    ['1.234.567,89', 1234567.89],
    ['1,234,567.89', 1234567.89],

    // Thousands only
    ['1.234', 1234.0],
    ['1,234', 1234.0],
    ['1 234,56', 1234.56],

    // With the symbol, as displayed
    ['R$ 1.234,56', 1234.56],
    ['1.234,56 €', 1234.56],
    ['$1,234.56', 1234.56],
    ['€0,00', 0.0],

    // Negative
    ['-12,50', -12.5],
    ['-1.234,56', -1234.56],

    // Not an amount
    ['', null],
    ['abc', null],
    ['-', null],
]);

it('passes through numbers untouched', function () {
    expect(Money::parse(1234.56))->toBe(1234.56)
        ->and(Money::parse(250))->toBe(250.0)
        ->and(Money::parse(null))->toBeNull();
});

it('round-trips what format() produced, for every preset', function (array $config) {
    config($config);

    foreach ([0, 0.5, 250, 1234.56, 1000000, -12.5] as $amount) {
        expect(Money::parse(Money::format($amount)))->toBe((float) $amount);
    }
})->with([
    'EUR' => [['currency.symbol' => '€', 'currency.position' => 'after', 'currency.space' => true,
        'currency.decimal_separator' => ',', 'currency.thousands_separator' => '.']],
    'USD' => [['currency.symbol' => '$', 'currency.position' => 'before', 'currency.space' => false,
        'currency.decimal_separator' => '.', 'currency.thousands_separator' => ',']],
    'BRL' => [['currency.symbol' => 'R$', 'currency.position' => 'before', 'currency.space' => true,
        'currency.decimal_separator' => ',', 'currency.thousands_separator' => '.']],
]);

it('does not read a three-digit group as a decimal', function () {
    // "1.234" is a thousands separator, not 1 euro 234. Only one or two trailing
    // digits mark the decimal place.
    expect(Money::parse('1.234'))->toBe(1234.0)
        ->and(Money::parse('1,234'))->toBe(1234.0);
});
