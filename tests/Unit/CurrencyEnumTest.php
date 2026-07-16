<?php

use App\Enums\CurrencyEnum;

test('all nine supported currencies exist', function () {
    $codes = array_map(fn (CurrencyEnum $c) => $c->value, CurrencyEnum::cases());

    expect($codes)->toBe(['EUR', 'USD', 'BRL', 'GBP', 'COP', 'MXN', 'ARS', 'CLP', 'MYR']);
});

test('CLP is the only zero-decimal currency', function () {
    foreach (CurrencyEnum::cases() as $currency) {
        expect($currency->decimals())->toBe($currency === CurrencyEnum::Clp ? 0 : 2);
    }
});

test('symbols map to the expected glyphs', function () {
    expect(CurrencyEnum::Eur->symbol())->toBe('€');
    expect(CurrencyEnum::Brl->symbol())->toBe('R$');
    expect(CurrencyEnum::Gbp->symbol())->toBe('£');
    expect(CurrencyEnum::Myr->symbol())->toBe('RM');
    expect(CurrencyEnum::Usd->symbol())->toBe('$');
});

test('current() follows the app config and falls back to EUR', function () {
    config(['app.currency' => 'BRL']);
    expect(CurrencyEnum::current())->toBe(CurrencyEnum::Brl);

    config(['app.currency' => 'INVALID']);
    expect(CurrencyEnum::current())->toBe(CurrencyEnum::Eur);
});

test('options() lists all currencies keyed by code', function () {
    $options = CurrencyEnum::options();

    expect($options)->toHaveCount(9);
    expect($options['CLP'])->toBe('Peso Chileno (CLP)');
});
