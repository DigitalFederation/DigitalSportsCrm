<?php

use App\Models\Sport;
use Domain\Licenses\DataTransferObject\LicenseData;
use Domain\Licenses\Models\License;

it('has a sports many-to-many relationship', function () {
    $license = License::factory()->create();
    $sports = Sport::factory()->count(2)->create();

    $license->sports()->sync($sports->pluck('id'));

    expect($license->sports)->toHaveCount(2);
    expect($sports->first()->licensesViaPivot)->toHaveCount(1);
});

it('filters licenses by sport via pivot in scopeFilterSport', function () {
    $sport = Sport::factory()->create();
    $licensePivot = License::factory()->create(['sport_id' => null]);
    $licensePivot->sports()->attach($sport);

    $licenseLegacy = License::factory()->create(['sport_id' => $sport->id]);

    $licenseOther = License::factory()->create(['sport_id' => null]);

    $results = License::filterSport($sport->id)->pluck('id');

    expect($results)->toContain($licensePivot->id)
        ->toContain($licenseLegacy->id)
        ->not->toContain($licenseOther->id);
});

it('derives sport_id from sport_ids in LicenseData', function () {
    $sports = Sport::factory()->count(2)->create();

    $data = LicenseData::fromArray([
        'committee_id' => 1,
        'type_id' => 1,
        'name' => 'Test',
        'sport_ids' => $sports->pluck('id')->toArray(),
    ]);

    expect($data->sport_id)->toBe($sports->first()->id);
    expect($data->sport_ids)->toHaveCount(2);
});

it('sets sport_id to null when sport_ids is empty in LicenseData', function () {
    $data = LicenseData::fromArray([
        'committee_id' => 1,
        'type_id' => 1,
        'name' => 'Test',
        'sport_ids' => [],
    ]);

    expect($data->sport_id)->toBeNull();
    expect($data->sport_ids)->toBeEmpty();
});

it('falls back to sport_id when sport_ids not present in LicenseData', function () {
    $sport = Sport::factory()->create();

    $data = LicenseData::fromArray([
        'committee_id' => 1,
        'type_id' => 1,
        'name' => 'Test',
        'sport_id' => $sport->id,
    ]);

    expect($data->sport_id)->toBe($sport->id);
    expect($data->sport_ids)->toBeNull();
});

it('factory afterCreating syncs sport into pivot', function () {
    $license = License::factory()->create();

    expect($license->sports->pluck('id'))->toContain($license->sport_id);
});

it('filters certifications by sport through license pivot', function () {
    $sport = Sport::factory()->create();
    $license = License::factory()->create(['sport_id' => null]);
    $license->sports()->attach($sport);

    $certification = \Domain\Certifications\Models\Certification::factory()->create([
        'license_id' => $license->id,
    ]);

    $results = \Domain\Certifications\Models\Certification::filterSport($sport->id)->pluck('id');
    expect($results)->toContain($certification->id);
});

it('filters certification_attributed by sport through license pivot', function () {
    $sport = Sport::factory()->create();
    $license = License::factory()->create(['sport_id' => null]);
    $license->sports()->attach($sport);

    $certification = \Domain\Certifications\Models\Certification::factory()->create([
        'license_id' => $license->id,
    ]);

    $attributed = \Domain\Certifications\Models\CertificationAttributed::factory()->create([
        'certification_id' => $certification->id,
    ]);

    $results = \Domain\Certifications\Models\CertificationAttributed::sport($sport->id)->pluck('id');
    expect($results)->toContain($attributed->id);
});
