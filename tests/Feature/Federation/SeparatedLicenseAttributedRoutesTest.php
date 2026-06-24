<?php

use App\Models\Group;
use App\Models\User;
use Domain\Federations\Models\Federation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');

    // Create groups
    $this->federationGroup = Group::firstOrCreate(['code' => 'FEDERATION'], ['name' => 'Federation']);

    // Create federation (main federation)
    $this->federation = Federation::factory()->create([
        'name' => 'Test Federation',
        'is_default_federation' => true,
        'is_local' => false,
    ]);

    // Create federation admin user
    $this->federationAdmin = User::factory()->create([
        'email' => 'federation@example.test',
        'group_id' => $this->federationGroup->id,
        'active' => true,
    ]);
    $this->federationAdmin->assignRole('federation-admin');
    $this->federationAdmin->federations()->attach($this->federation->id);
});

it('can access sport entity licenses route as federation admin', function () {
    $this->actingAs($this->federationAdmin);

    $response = $this->get(route('federation.sport-entity-licenses-attributed.index'));

    $response->assertStatus(200);
    $response->assertViewIs('web.federation.license_attributed.separated');
    $response->assertViewHas('committee', 'SPORT');
    $response->assertViewHas('holderType', 'entity');
    $response->assertViewHas('isInternational', false);
});

it('can access sport individual licenses route as federation admin', function () {
    $this->actingAs($this->federationAdmin);

    $response = $this->get(route('federation.sport-individual-licenses-attributed.index'));

    $response->assertStatus(200);
    $response->assertViewIs('web.federation.license_attributed.separated');
    $response->assertViewHas('committee', 'SPORT');
    $response->assertViewHas('holderType', 'individual');
    $response->assertViewHas('isInternational', false);
});

it('can access national diving entity licenses route as federation admin', function () {
    $this->actingAs($this->federationAdmin);

    $response = $this->get(route('federation.national-diving-entity-licenses-attributed.index'));

    $response->assertStatus(200);
    $response->assertViewIs('web.federation.license_attributed.separated');
    $response->assertViewHas('committee', 'DIVINGSERVICES');
    $response->assertViewHas('holderType', 'entity');
    $response->assertViewHas('isInternational', false);
});

it('can access national diving individual licenses route as federation admin', function () {
    $this->actingAs($this->federationAdmin);

    $response = $this->get(route('federation.national-diving-individual-licenses-attributed.index'));

    $response->assertStatus(200);
    $response->assertViewIs('web.federation.license_attributed.separated');
    $response->assertViewHas('committee', 'DIVINGSERVICES');
    $response->assertViewHas('holderType', 'individual');
    $response->assertViewHas('isInternational', false);
});

it('can access International diving entity licenses route as federation admin', function () {
    $this->actingAs($this->federationAdmin);

    $response = $this->get(route('federation.international-diving-entity-licenses-attributed.index'));

    $response->assertStatus(200);
    $response->assertViewIs('web.federation.license_attributed.separated');
    $response->assertViewHas('committee', 'DIVING');
    $response->assertViewHas('holderType', 'entity');
    $response->assertViewHas('isInternational', true);
});

it('can access International diving individual licenses route as federation admin', function () {
    $this->actingAs($this->federationAdmin);

    $response = $this->get(route('federation.international-diving-individual-licenses-attributed.index'));

    $response->assertStatus(200);
    $response->assertViewIs('web.federation.license_attributed.separated');
    $response->assertViewHas('committee', 'DIVING');
    $response->assertViewHas('holderType', 'individual');
    $response->assertViewHas('isInternational', true);
});

it('can access scientific entity licenses route as federation admin', function () {
    $this->actingAs($this->federationAdmin);

    $response = $this->get(route('federation.scientific-entity-licenses-attributed.index'));

    $response->assertStatus(200);
    $response->assertViewIs('web.federation.license_attributed.separated');
    $response->assertViewHas('committee', 'SCIENTIFIC');
    $response->assertViewHas('holderType', 'entity');
    $response->assertViewHas('isInternational', true);
});

it('can access scientific individual licenses route as federation admin', function () {
    $this->actingAs($this->federationAdmin);

    $response = $this->get(route('federation.scientific-individual-licenses-attributed.index'));

    $response->assertStatus(200);
    $response->assertViewIs('web.federation.license_attributed.separated');
    $response->assertViewHas('committee', 'SCIENTIFIC');
    $response->assertViewHas('holderType', 'individual');
    $response->assertViewHas('isInternational', true);
});

it('redirects unauthenticated users to login', function () {
    $response = $this->get(route('federation.sport-entity-licenses-attributed.index'));

    $response->assertRedirect(route('login'));
});

it('returns correct page title for each route', function () {
    $this->actingAs($this->federationAdmin);

    $routes = [
        'federation.sport-entity-licenses-attributed.index' => __('licenses.federation_sport_entity_licenses_title'),
        'federation.sport-individual-licenses-attributed.index' => __('licenses.federation_sport_individual_licenses_title'),
        'federation.national-diving-entity-licenses-attributed.index' => __('licenses.federation_national_diving_entity_licenses_title'),
        'federation.national-diving-individual-licenses-attributed.index' => __('licenses.federation_national_diving_individual_licenses_title'),
        'federation.international-diving-entity-licenses-attributed.index' => __('licenses.federation_cmas_diving_entity_licenses_title'),
        'federation.international-diving-individual-licenses-attributed.index' => __('licenses.federation_cmas_diving_individual_licenses_title'),
        'federation.scientific-entity-licenses-attributed.index' => __('licenses.federation_scientific_entity_licenses_title'),
        'federation.scientific-individual-licenses-attributed.index' => __('licenses.federation_scientific_individual_licenses_title'),
    ];

    foreach ($routes as $routeName => $expectedTitle) {
        $response = $this->get(route($routeName));
        $response->assertViewHas('pageTitle', $expectedTitle);
    }
});
