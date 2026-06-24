<?php

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');

    // Create ADMIN group (required for admin users)
    $this->adminGroup = Group::firstOrCreate(['code' => 'ADMIN'], ['name' => 'Admin']);

    // Create admin user
    $this->admin = User::factory()->create([
        'email' => 'admin@example.test',
        'group_id' => $this->adminGroup->id,
        'active' => true,
    ]);
    $this->admin->assignRole('admin');
});

it('can access sport entity licenses route as admin', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('admin.sport-entity-licenses-attributed.index'));

    $response->assertStatus(200);
    $response->assertViewIs('web.admin.license_attributed.separated');
    $response->assertViewHas('committee', 'SPORT');
    $response->assertViewHas('holderType', 'entity');
    $response->assertViewHas('isInternational', false);
});

it('can access sport individual licenses route as admin', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('admin.sport-individual-licenses-attributed.index'));

    $response->assertStatus(200);
    $response->assertViewIs('web.admin.license_attributed.separated');
    $response->assertViewHas('committee', 'SPORT');
    $response->assertViewHas('holderType', 'individual');
    $response->assertViewHas('isInternational', false);
});

it('can access national diving entity licenses route as admin', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('admin.national-diving-entity-licenses-attributed.index'));

    $response->assertStatus(200);
    $response->assertViewIs('web.admin.license_attributed.separated');
    $response->assertViewHas('committee', 'DIVINGSERVICES');
    $response->assertViewHas('holderType', 'entity');
    $response->assertViewHas('isInternational', false);
});

it('can access national diving individual licenses route as admin', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('admin.national-diving-individual-licenses-attributed.index'));

    $response->assertStatus(200);
    $response->assertViewIs('web.admin.license_attributed.separated');
    $response->assertViewHas('committee', 'DIVINGSERVICES');
    $response->assertViewHas('holderType', 'individual');
    $response->assertViewHas('isInternational', false);
});

it('can access International diving entity licenses route as admin', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('admin.international-diving-entity-licenses-attributed.index'));

    $response->assertStatus(200);
    $response->assertViewIs('web.admin.license_attributed.separated');
    $response->assertViewHas('committee', 'DIVING');
    $response->assertViewHas('holderType', 'entity');
    $response->assertViewHas('isInternational', true);
});

it('can access International diving individual licenses route as admin', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('admin.international-diving-individual-licenses-attributed.index'));

    $response->assertStatus(200);
    $response->assertViewIs('web.admin.license_attributed.separated');
    $response->assertViewHas('committee', 'DIVING');
    $response->assertViewHas('holderType', 'individual');
    $response->assertViewHas('isInternational', true);
});

it('can access scientific entity licenses route as admin', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('admin.scientific-entity-licenses-attributed.index'));

    $response->assertStatus(200);
    $response->assertViewIs('web.admin.license_attributed.separated');
    $response->assertViewHas('committee', 'SCIENTIFIC');
    $response->assertViewHas('holderType', 'entity');
    $response->assertViewHas('isInternational', true);
});

it('can access scientific individual licenses route as admin', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('admin.scientific-individual-licenses-attributed.index'));

    $response->assertStatus(200);
    $response->assertViewIs('web.admin.license_attributed.separated');
    $response->assertViewHas('committee', 'SCIENTIFIC');
    $response->assertViewHas('holderType', 'individual');
    $response->assertViewHas('isInternational', true);
});

it('redirects unauthenticated users to login from admin routes', function () {
    $response = $this->get(route('admin.sport-entity-licenses-attributed.index'));

    $response->assertRedirect(route('login'));
});

it('returns correct page title for each admin route', function () {
    $this->actingAs($this->admin);

    $routes = [
        'admin.sport-entity-licenses-attributed.index' => __('licenses.admin_sport_entity_licenses_title'),
        'admin.sport-individual-licenses-attributed.index' => __('licenses.admin_sport_individual_licenses_title'),
        'admin.national-diving-entity-licenses-attributed.index' => __('licenses.admin_national_diving_entity_licenses_title'),
        'admin.national-diving-individual-licenses-attributed.index' => __('licenses.admin_national_diving_individual_licenses_title'),
        'admin.international-diving-entity-licenses-attributed.index' => __('licenses.admin_cmas_diving_entity_licenses_title'),
        'admin.international-diving-individual-licenses-attributed.index' => __('licenses.admin_cmas_diving_individual_licenses_title'),
        'admin.scientific-entity-licenses-attributed.index' => __('licenses.admin_scientific_entity_licenses_title'),
        'admin.scientific-individual-licenses-attributed.index' => __('licenses.admin_scientific_individual_licenses_title'),
    ];

    foreach ($routes as $routeName => $expectedTitle) {
        $response = $this->get(route($routeName));
        $response->assertViewHas('pageTitle', $expectedTitle);
    }
});
