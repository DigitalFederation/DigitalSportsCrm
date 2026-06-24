<?php

use App\Models\Country;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Geographic\Models\District;
use Domain\Imports\Actions\ValidateEntityBulkDataAction;

beforeEach(function () {
    // Ensure we have a default federation
    if (! Federation::where('is_default_federation', 1)->exists()) {
        Federation::factory()->create(['is_default_federation' => 1]);
    }

    // Ensure we have a country
    if (! Country::where('name', 'Portugal')->exists()) {
        Country::factory()->create(['name' => 'Portugal']);
    }
});

test('validates empty entity array returns empty results', function () {
    $validateAction = app(ValidateEntityBulkDataAction::class);

    $results = $validateAction->execute([]);

    expect($results['valid'])->toBeEmpty();
    expect($results['errors'])->toBeEmpty();
    expect($results['warnings'])->toBeEmpty();
});

test('validates country names are resolved correctly', function () {
    $country = Country::first() ?? Country::factory()->create(['name' => 'Portugal']);
    $validateAction = app(ValidateEntityBulkDataAction::class);

    $countryMap = $validateAction->validateCountries([$country->name]);

    expect($countryMap)->toHaveKey($country->name);
    expect($countryMap[$country->name])->toBe($country->id);
});

test('validates unknown country names return empty', function () {
    $validateAction = app(ValidateEntityBulkDataAction::class);

    $countryMap = $validateAction->validateCountries(['NonExistentCountry123']);

    expect($countryMap)->not->toHaveKey('NonExistentCountry123');
});

test('validates districts are resolved correctly', function () {
    $country = Country::first() ?? Country::factory()->create(['name' => 'Portugal']);
    $district = District::first() ?? District::factory()->create(['name' => 'Lisboa', 'country_id' => $country->id]);
    $validateAction = app(ValidateEntityBulkDataAction::class);

    $districtMap = $validateAction->validateDistricts([$district->name]);

    expect($districtMap)->toHaveKey($district->name);
    expect($districtMap[$district->name])->toBe($district->id);
});

test('validates member numbers correctly detect existing', function () {
    $country = Country::first() ?? Country::factory()->create(['name' => 'Portugal']);
    $entity = Entity::factory()->create([
        'member_number' => 99999,
        'country_id' => $country->id,
    ]);
    $validateAction = app(ValidateEntityBulkDataAction::class);

    $existing = $validateAction->validateMemberNumbers([99999, 88888]);

    expect($existing)->toContain(99999);
    expect($existing)->not->toContain(88888);
});

test('validates federation ids correctly', function () {
    $federation = Federation::first() ?? Federation::factory()->create();
    $validateAction = app(ValidateEntityBulkDataAction::class);

    $validFederations = $validateAction->validateFederations([$federation->id, 999999]);

    expect($validFederations)->toContain($federation->id);
    expect($validFederations)->not->toContain(999999);
});

test('caches country lookups for repeated queries', function () {
    $country = Country::first() ?? Country::factory()->create(['name' => 'Portugal']);
    $validateAction = app(ValidateEntityBulkDataAction::class);

    // First call - should query DB
    $countryMap1 = $validateAction->validateCountries([$country->name]);

    // Second call - should use cache
    $countryMap2 = $validateAction->validateCountries([$country->name]);

    expect($countryMap1[$country->name])->toBe($countryMap2[$country->name]);
});

test('validates multiple entities in batch', function () {
    $country = Country::first() ?? Country::factory()->create(['name' => 'Portugal']);
    $validateAction = app(ValidateEntityBulkDataAction::class);

    $entities = [
        [
            'name' => 'Entity 1',
            'country_id' => $country->id,
        ],
        [
            'name' => 'Entity 2',
            'country' => $country->name, // Test country name resolution
        ],
        [
            'name' => '', // Invalid - missing name
            'country_id' => $country->id,
        ],
    ];

    $results = $validateAction->execute($entities);

    expect(count($results['valid']))->toBe(2);
    expect(count($results['errors']))->toBe(1);
});

test('warns on invalid url formats', function () {
    $country = Country::first() ?? Country::factory()->create(['name' => 'Portugal']);
    $validateAction = app(ValidateEntityBulkDataAction::class);

    $entities = [
        [
            'name' => 'Test Entity',
            'country_id' => $country->id,
            'website' => 'not-a-valid-url',
        ],
    ];

    $results = $validateAction->execute($entities);

    expect($results['warnings'])->not->toBeEmpty();
});

test('validates with numeric country id', function () {
    $country = Country::first() ?? Country::factory()->create(['name' => 'Portugal']);
    $validateAction = app(ValidateEntityBulkDataAction::class);

    $entities = [
        [
            'name' => 'Test Entity',
            'country_id' => $country->id,
        ],
    ];

    $results = $validateAction->execute($entities);

    expect($results['valid'])->not->toBeEmpty();
    expect($results['errors'])->toBeEmpty();
});

test('handles empty district names gracefully', function () {
    $validateAction = app(ValidateEntityBulkDataAction::class);

    $districtMap = $validateAction->validateDistricts(['', null]);

    expect($districtMap)->toBeEmpty();
});

test('handles empty federation ids gracefully', function () {
    $validateAction = app(ValidateEntityBulkDataAction::class);

    $federationIds = $validateAction->validateFederations([null, '']);

    expect($federationIds)->toBeEmpty();
});
