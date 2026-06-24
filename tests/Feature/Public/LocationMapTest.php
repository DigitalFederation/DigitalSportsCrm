<?php

declare(strict_types=1);

use App\Enums\CommitteeCodeEnum;
use App\Livewire\Public\LocationMap;
use App\Models\Committee;
use App\Models\Country;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Models\LicenseType;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    Cache::flush();
    config()->set('public-map.country_id', null);
    config()->set('public-map.include_federations', false);
    config()->set('public-map.show_contact_details', false);
});

it('renders the public map without authentication when no country is configured', function () {
    $this->get(route('public.map.locations'))
        ->assertOk()
        ->assertSeeLivewire(LocationMap::class);

    Livewire::test(LocationMap::class)
        ->tap(function ($component) {
            expect($component->instance()->mapLocations)->toBe([]);
        });
});

it('only exposes configured visible entities with active licenses on the public map', function () {
    $country = Country::factory()->create();
    $otherCountry = Country::factory()->create();
    $license = publicMapEntityLicense();

    config()->set('public-map.country_id', $country->id);

    $visibleEntity = Entity::factory()->create([
        'country_id' => $country->id,
        'name' => 'Public Entity',
        'email' => 'public@example.test',
        'visible_in_map' => true,
    ]);
    publicMapActiveEntityLicense($visibleEntity, $license);

    $hiddenEntity = Entity::factory()->create([
        'country_id' => $country->id,
        'name' => 'Hidden Entity',
        'visible_in_map' => false,
    ]);
    publicMapActiveEntityLicense($hiddenEntity, $license);

    $foreignEntity = Entity::factory()->create([
        'country_id' => $otherCountry->id,
        'name' => 'Foreign Entity',
        'visible_in_map' => true,
    ]);
    publicMapActiveEntityLicense($foreignEntity, $license);

    Entity::factory()->create([
        'country_id' => $country->id,
        'name' => 'No License Entity',
        'visible_in_map' => true,
    ]);

    Livewire::test(LocationMap::class)
        ->tap(function ($component) use ($visibleEntity, $hiddenEntity, $foreignEntity) {
            $names = collect($component->instance()->mapLocations)->pluck('name');

            expect($names)
                ->toContain($visibleEntity->name)
                ->not->toContain($hiddenEntity->name)
                ->not->toContain($foreignEntity->name)
                ->not->toContain('No License Entity');
        })
        ->call('showDetails', $hiddenEntity->id, 'entity')
        ->assertSet('selectedItem', null)
        ->call('showDetails', $visibleEntity->id, 'entity')
        ->tap(function ($component) use ($visibleEntity) {
            $details = $component->instance()->selectedItemDetails;

            expect($details->name)->toBe($visibleEntity->name)
                ->and($details->email)->toBeNull();
        });
});

it('requires explicit configuration before federation locations are exposed', function () {
    $country = Country::factory()->create();
    $federation = Federation::factory()->create([
        'country_id' => $country->id,
        'name' => 'Public Federation',
        'email' => 'federation@example.test',
    ]);

    config()->set('public-map.country_id', $country->id);

    Livewire::test(LocationMap::class)
        ->tap(function ($component) use ($federation) {
            expect(collect($component->instance()->mapLocations)->pluck('name'))
                ->not->toContain($federation->name);
        });

    config()->set('public-map.include_federations', true);
    Cache::flush();

    Livewire::test(LocationMap::class)
        ->tap(function ($component) use ($federation) {
            expect(collect($component->instance()->mapLocations)->pluck('name'))
                ->toContain($federation->name);
        })
        ->call('showDetails', $federation->id, 'federation')
        ->tap(function ($component) use ($federation) {
            $details = $component->instance()->selectedItemDetails;

            expect($details->name)->toBe($federation->name)
                ->and($details->email)->toBeNull();
        });
});

function publicMapEntityLicense(): License
{
    $committee = Committee::factory()->create([
        'code' => CommitteeCodeEnum::Sport->value,
        'is_international' => false,
    ]);

    $licenseType = LicenseType::factory()->create(['id' => 1]);

    return License::factory()->create([
        'committee_id' => $committee->id,
        'type_id' => $licenseType->id,
        'active' => true,
    ]);
}

function publicMapActiveEntityLicense(Entity $entity, License $license): LicenseAttributed
{
    return LicenseAttributed::factory()->create([
        'model_type' => 'entity',
        'model_id' => $entity->id,
        'license_id' => $license->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);
}
