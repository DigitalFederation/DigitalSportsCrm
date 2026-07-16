<?php

use Domain\Geographic\Models\District;
use Domain\Geographic\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=CountrySeeder');
    $this->artisan('db:seed --class=BrazilGeographicSeeder');
});

test('it seeds the 27 federative units as zones', function () {
    expect(Zone::count())->toBe(27);
    expect(Zone::where('code', 'SP')->value('name'))->toBe('São Paulo');
    expect(Zone::where('code', 'DF')->value('name'))->toBe('Distrito Federal');
});

test('it seeds every IBGE municipality as a district', function () {
    expect(District::count())->toBe(5571);
    expect(District::whereRaw('CHAR_LENGTH(code) <> 7')->count())->toBe(0);
});

test('districts use the official IBGE code', function () {
    expect(District::where('code', '3550308')->value('name'))->toBe('São Paulo');
    expect(District::where('code', '3304557')->value('name'))->toBe('Rio de Janeiro');
    expect(District::where('code', '5300108')->value('name'))->toBe('Brasília');
});

test('every municipality is attached to exactly one state zone', function () {
    $orphans = District::doesntHave('zones')->count();
    expect($orphans)->toBe(0);

    $multi = District::has('zones', '>', 1)->count();
    expect($multi)->toBe(0);
});

test('municipality counts per state match the IBGE totals', function (string $uf, int $expected) {
    $zone = Zone::where('code', $uf)->firstOrFail();

    expect($zone->districts()->count())->toBe($expected);
})->with([
    'São Paulo' => ['SP', 645],
    'Minas Gerais' => ['MG', 853],
    'Bahia' => ['BA', 417],
    'Rio Grande do Sul' => ['RS', 497],
    'Distrito Federal' => ['DF', 1],
    'Roraima' => ['RR', 15],
]);

test('the seeder is idempotent', function () {
    $this->artisan('db:seed --class=BrazilGeographicSeeder');

    expect(Zone::count())->toBe(27);
    expect(District::count())->toBe(5571);
    expect(DB::table('district_zone')->count())->toBe(5571);
});
