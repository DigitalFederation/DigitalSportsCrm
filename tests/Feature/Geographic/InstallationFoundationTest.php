<?php

use App\Http\Requests\CreatePublicIndividualRequest;
use Database\Seeders\FreshInstallSeeder;
use Database\Seeders\PortugalGeographySeeder;
use Domain\Geographic\Enums\TerritorySelection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;

it('registers the Portuguese geography dataset explicitly', function () {
    $seeder = config('geography.datasets.portugal');

    expect(config('geography.dataset'))->toBe('portugal')
        ->and($seeder)->toBe(PortugalGeographySeeder::class)
        ->and(is_subclass_of($seeder, Seeder::class))->toBeTrue();
});

it('rejects an unsupported geography dataset before running install seeders', function () {
    config()->set('geography.dataset', 'brazil');

    expect(fn () => app(FreshInstallSeeder::class)->run())
        ->toThrow(
            \InvalidArgumentException::class,
            'Unsupported geography dataset [brazil].'
        );
});

it('uses country-specific geographic terminology', function () {
    app()->setLocale('pt_PT');

    expect(__('geographic.district'))->toBe('Distrito')
        ->and(__('geographic.zone'))->toBe('Zona');

    app()->setLocale('pt_BR');

    expect(__('geographic.district'))->toBe('Município')
        ->and(__('geographic.zone'))->toBe('Estado')
        ->and(__('geographic.select_district'))->toContain('Município');
});

it('accepts the generic non-listed territory selection', function () {
    $request = CreatePublicIndividualRequest::create('/', 'POST');
    $districtRules = $request->rules()['district_id'];

    $validator = Validator::make(
        ['district_id' => TerritorySelection::NOT_LISTED->value],
        ['district_id' => $districtRules]
    );

    expect($validator->passes())->toBeTrue();
});

it('rejects the legacy Portugal-specific territory selection', function () {
    $request = CreatePublicIndividualRequest::create('/', 'POST');
    $districtRules = $request->rules()['district_id'];

    $validator = Validator::make(
        ['district_id' => 'outside_portugal'],
        ['district_id' => $districtRules]
    );

    expect($validator->fails())->toBeTrue();
});
