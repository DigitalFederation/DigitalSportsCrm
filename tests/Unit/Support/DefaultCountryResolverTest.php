<?php

use App\Models\Country;
use App\Support\DefaultCountryResolver;

it('resolves the configured default country by ISO code regardless of its database id', function () {
    Country::factory()->create(['iso' => 'ES']);
    $portugal = Country::factory()->create(['iso' => 'PT']);
    config()->set('app.default_country_code', 'pt');

    $resolved = app(DefaultCountryResolver::class)->resolve();

    expect($resolved->is($portugal))->toBeTrue();
});

it('can resolve an explicit country code', function () {
    $brazil = Country::factory()->create(['iso' => 'BR']);
    config()->set('app.default_country_code', 'PT');

    $resolved = app(DefaultCountryResolver::class)->resolve(' br ');

    expect($resolved->is($brazil))->toBeTrue();
});

it('supports the deprecated default country id during the upgrade period', function () {
    $legacyCountry = Country::factory()->create(['iso' => 'BR']);
    config()->set('app.default_country_code');
    config()->set('app.legacy_default_country_id', $legacyCountry->id);

    $resolved = app(DefaultCountryResolver::class)->resolve();

    expect($resolved->is($legacyCountry))->toBeTrue();
});

it('fails explicitly when the configured country does not exist', function () {
    config()->set('app.default_country_code', 'ZZ');

    expect(fn () => app(DefaultCountryResolver::class)->resolve())
        ->toThrow(\RuntimeException::class, 'Expected exactly one country with ISO code [ZZ], found 0.');
});

it('fails explicitly when a country code is ambiguous', function () {
    Country::factory()->count(2)->create(['iso' => 'PT']);

    expect(fn () => app(DefaultCountryResolver::class)->resolve('PT'))
        ->toThrow(\RuntimeException::class, 'Expected exactly one country with ISO code [PT], found 2.');
});
