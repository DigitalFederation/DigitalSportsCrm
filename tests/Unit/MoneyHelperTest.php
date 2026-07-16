<?php

test('formats the installation currency localized to the active locale', function (string $currency, string $locale, string $needle) {
    config(['app.currency' => $currency]);
    app()->setLocale($locale);

    expect(money(1234.56))->toContain($needle);
})->with([
    'EUR under pt_PT' => ['EUR', 'pt_PT', '€'],
    'BRL under pt_BR' => ['BRL', 'pt_BR', 'R$'],
    'GBP under en' => ['GBP', 'en', '£'],
]);

test('CLP renders without decimal places', function () {
    config(['app.currency' => 'CLP']);
    app()->setLocale('es');

    $formatted = money(1234.56);

    expect($formatted)->toContain('1.235');
    expect($formatted)->not->toContain(',56');
});

test('an explicit currency overrides the installation default', function () {
    config(['app.currency' => 'BRL']);
    app()->setLocale('en');

    expect(money(99.9, 'EUR'))->toContain('€');
    expect(money(99.9, 'EUR'))->not->toContain('R$');
});

test('null amounts render as zero', function () {
    config(['app.currency' => 'EUR']);
    app()->setLocale('en');

    expect(money(null))->toContain('0.00');
});

test('an unknown currency code falls back to EUR formatting', function () {
    app()->setLocale('en');

    expect(money(10, 'XXX'))->toContain('€');
});

test('currency_code and currency_symbol follow the installation currency', function () {
    config(['app.currency' => 'MYR']);

    expect(currency_code())->toBe('MYR');
    expect(currency_symbol())->toBe('RM');
});
