<?php

use Illuminate\Support\Facades\Blade;
use Support\Money;

/**
 * Proves the CURRENCY_* configuration reaches a rendered template — that the
 * global money() helper is autoloaded and usable from Blade, not merely correct
 * in isolation. Unit coverage of the formatter itself lives in
 * tests/Unit/Support/MoneyTest.php.
 */
function configureCurrency(array $overrides): void
{
    config(array_merge([
        'currency.code' => 'EUR',
        'currency.symbol' => '€',
        'currency.position' => 'after',
        'currency.space' => true,
        'currency.decimals' => 2,
        'currency.decimal_separator' => ',',
        'currency.thousands_separator' => '.',
    ], $overrides));
}

it('renders amounts in the configured Brazilian real', function () {
    configureCurrency([
        'currency.code' => 'BRL',
        'currency.symbol' => 'R$',
        'currency.position' => 'before',
        'currency.space' => true,
    ]);

    $rendered = Blade::render('{{ money($price) }}', ['price' => 1234.56]);

    expect($rendered)->toBe('R$ 1.234,56');
});

it('renders the same amount in euros when configured for euros', function () {
    configureCurrency([]);

    $rendered = Blade::render('{{ money($price) }}', ['price' => 1234.56]);

    expect($rendered)->toBe('1.234,56 €');
});

it('renders the same amount in US dollars when configured for dollars', function () {
    configureCurrency([
        'currency.code' => 'USD',
        'currency.symbol' => '$',
        'currency.position' => 'before',
        'currency.space' => false,
        'currency.decimal_separator' => '.',
        'currency.thousands_separator' => ',',
    ]);

    $rendered = Blade::render('{{ money($price) }}', ['price' => 1234.56]);

    expect($rendered)->toBe('$1,234.56');
});

it('fills a translation placeholder with the configured currency code', function () {
    configureCurrency(['currency.code' => 'BRL']);

    $rendered = Blade::render(
        "{{ __('Revenue (:currency)', ['currency' => \Support\Money::code()]) }}"
    );

    expect($rendered)->toBe('Revenue (BRL)');
});

it('escapes nothing unexpected in the symbol when rendered through Blade', function () {
    configureCurrency([
        'currency.symbol' => 'R$',
        'currency.position' => 'before',
    ]);

    // Blade's {{ }} runs output through e(). "R$" must survive intact, and the
    // separator must remain a normal space rather than becoming &nbsp;.
    $rendered = Blade::render('{{ money($price) }}', ['price' => 1]);

    expect($rendered)->toBe('R$ 1,00')
        ->and($rendered)->not->toContain('&')
        ->and(mb_strpos($rendered, "\u{00A0}"))->toBeFalse();
});

it('exposes money() as a globally autoloaded function', function () {
    expect(function_exists('money'))->toBeTrue()
        ->and(money(1))->toBe(Money::format(1));
});
