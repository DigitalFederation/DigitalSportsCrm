<?php

use App\Http\Requests\CreatePublicIndividualRequest;
use App\Livewire\Geographic\AdministrativeAreaSelector;
use App\Models\Country;
use Database\Factories\DistrictFactory;
use Database\Factories\ZoneFactory;
use Database\Seeders\BrazilGeographySeeder;
use Domain\Geographic\Enums\ZoneKind;
use Domain\Geographic\Models\District;
use Domain\Geographic\Models\Zone;
use Illuminate\Support\Facades\Validator;
use Livewire\Livewire;

it('seeds the official Brazilian administrative geography idempotently', function () {
    $brazil = Country::factory()->create([
        'iso' => 'BR',
        'ioc' => 'BRA',
        'name' => 'Brazil',
    ]);

    $this->seed(BrazilGeographySeeder::class);
    $this->seed(BrazilGeographySeeder::class);

    expect(Zone::query()->where('country_id', $brazil->id)->administrativeLevelOne()->count())->toBe(27)
        ->and(District::query()->where('country_id', $brazil->id)->count())->toBe(5571);

    $saoPaulo = Zone::query()->where('code', 'BR-SP')->firstOrFail();
    $saoPauloCity = District::query()->where('code', '3550308')->firstOrFail();
    $boaEsperancaDoNorte = District::query()->where('code', '5101837')->firstOrFail();

    expect($saoPaulo->kind)->toBe(ZoneKind::ADMINISTRATIVE_LEVEL_1)
        ->and($saoPaulo->external_code)->toBe('35')
        ->and($saoPauloCity->name)->toBe('São Paulo')
        ->and($saoPauloCity->administrative_zone_id)->toBe($saoPaulo->id)
        ->and($boaEsperancaDoNorte->name)->toBe('Boa Esperança do Norte');
});

it('filters municipalities when the selected state changes', function () {
    $brazil = Country::factory()->create(['iso' => 'BR']);
    /** @var Zone $saoPaulo */
    $saoPaulo = ZoneFactory::new()->create([
        'country_id' => $brazil->id,
        'kind' => ZoneKind::ADMINISTRATIVE_LEVEL_1,
    ]);
    /** @var Zone $bahia */
    $bahia = ZoneFactory::new()->create([
        'country_id' => $brazil->id,
        'kind' => ZoneKind::ADMINISTRATIVE_LEVEL_1,
    ]);
    /** @var District $campinas */
    $campinas = DistrictFactory::new()->create([
        'country_id' => $brazil->id,
        'administrative_zone_id' => $saoPaulo->id,
    ]);
    /** @var District $salvador */
    $salvador = DistrictFactory::new()->create([
        'country_id' => $brazil->id,
        'administrative_zone_id' => $bahia->id,
    ]);

    Livewire::test(AdministrativeAreaSelector::class, ['countryId' => $brazil->id])
        ->set('selectedZoneId', $saoPaulo->id)
        ->assertSee($campinas->name)
        ->assertDontSee($salvador->name)
        ->set('selectedDistrictId', $campinas->id)
        ->set('selectedZoneId', $bahia->id)
        ->assertSet('selectedDistrictId', null)
        ->assertSee($salvador->name)
        ->assertDontSee($campinas->name);
});

it('renders the administrative selector in public registration', function () {
    config()->set('app.default_country_code', 'BR');
    app()->setLocale('pt_BR');
    $brazil = Country::factory()->create(['iso' => 'BR']);
    /** @var Zone $state */
    $state = ZoneFactory::new()->create([
        'country_id' => $brazil->id,
        'name' => 'Estado Exemplo',
        'kind' => ZoneKind::ADMINISTRATIVE_LEVEL_1,
    ]);

    $this->get(route('public.individual.create'))
        ->assertOk()
        ->assertSee('Estado Exemplo')
        ->assertSee('Município');
});

it('rejects a municipality that does not belong to the submitted state', function () {
    config()->set('app.default_country_code', 'BR');
    $brazil = Country::factory()->create(['iso' => 'BR']);
    /** @var Zone $saoPaulo */
    $saoPaulo = ZoneFactory::new()->create([
        'country_id' => $brazil->id,
        'kind' => ZoneKind::ADMINISTRATIVE_LEVEL_1,
    ]);
    /** @var Zone $bahia */
    $bahia = ZoneFactory::new()->create([
        'country_id' => $brazil->id,
        'kind' => ZoneKind::ADMINISTRATIVE_LEVEL_1,
    ]);
    /** @var District $campinas */
    $campinas = DistrictFactory::new()->create([
        'country_id' => $brazil->id,
        'administrative_zone_id' => $saoPaulo->id,
    ]);
    $request = CreatePublicIndividualRequest::create('/', 'POST', [
        'administrative_zone_id' => $bahia->id,
        'district_id' => $campinas->id,
    ]);

    $validator = Validator::make($request->all(), [
        'administrative_zone_id' => $request->rules()['administrative_zone_id'],
        'district_id' => $request->rules()['district_id'],
    ]);

    expect($validator->errors()->has('district_id'))->toBeTrue();
});
