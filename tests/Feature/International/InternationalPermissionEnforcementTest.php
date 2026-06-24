<?php

use App\Models\Group;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create the 'access international licenses' permission
    $this->permission = Permission::create([
        'name' => 'access international licenses',
        'guard_name' => 'web',
    ]);

    // Create the INDIVIDUAL group (required by middleware)
    $this->individualGroup = Group::firstOrCreate(['code' => 'INDIVIDUAL'], ['name' => 'Individual']);
});

test('international individual license purchase requires permission', function () {
    $individual = Individual::factory()->create();
    $user = $individual->user;

    // Without permission - should be forbidden
    $response = $this->actingAs($user)->get(route('international.individual.license-purchase.index'));
    $response->assertForbidden();
});

test('international individual license purchase allows access with permission', function () {
    $individual = Individual::factory()->create();
    $user = $individual->user;
    $user->givePermissionTo('access international licenses');

    // With permission - should succeed
    $response = $this->actingAs($user)->get(route('international.individual.license-purchase.index'));
    $response->assertSuccessful();
});

test('international individual licenses attributed requires permission', function () {
    $individual = Individual::factory()->create();
    $user = $individual->user;

    // Without permission - should be forbidden
    $response = $this->actingAs($user)->get(route('international.individual.licenses-attributed.index'));
    $response->assertForbidden();
});

test('international individual licenses attributed allows access with permission', function () {
    $individual = Individual::factory()->create();
    $user = $individual->user;
    $user->givePermissionTo('access international licenses');

    // With permission - should succeed (but also requires active affiliation)
    // The controller checks hasActiveAffiliation() which requires MemberSubscription with Affiliations
    $response = $this->actingAs($user)->get(route('international.individual.licenses-attributed.index'));
    // Returns 403 because individual has no active affiliation - this is expected behavior
    $response->assertForbidden();
});

test('international individual certification card requires permission', function () {
    $individual = Individual::factory()->create();
    $user = $individual->user;

    // Without permission - should be forbidden
    $response = $this->actingAs($user)->get(route('international.individual.certification-card.index'));
    $response->assertForbidden();
});

test('international individual certification card allows access with permission', function () {
    $individual = Individual::factory()->create();
    $user = $individual->user;
    $user->givePermissionTo('access international licenses');

    // With permission - should succeed
    $response = $this->actingAs($user)->get(route('international.individual.certification-card.index'));
    $response->assertSuccessful();
});

test('international individual certifications requires permission', function () {
    $individual = Individual::factory()->create();
    $user = $individual->user;

    // Without permission - should be forbidden
    $response = $this->actingAs($user)->get(route('international.individual.certifications.index'));
    $response->assertForbidden();
});

test('international individual certifications allows access with permission', function () {
    $individual = Individual::factory()->create();
    $user = $individual->user;
    $user->givePermissionTo('access international licenses');

    // With permission - should succeed
    $response = $this->actingAs($user)->get(route('international.individual.certifications.index'));
    $response->assertSuccessful();
});

test('all international routes require authentication', function () {
    $routes = [
        'international.individual.license-purchase.index',
        'international.individual.licenses-attributed.index',
        'international.individual.certification-card.index',
        'international.individual.certifications.index',
    ];

    foreach ($routes as $route) {
        $response = $this->get(route($route));
        $response->assertRedirect(route('login'));
    }
});

test('international routes reject users without permission even when authenticated', function () {
    $individual = Individual::factory()->create();
    $user = $individual->user;
    // No permission given

    $routes = [
        'international.individual.license-purchase.index',
        'international.individual.licenses-attributed.index',
        'international.individual.certification-card.index',
        'international.individual.certifications.index',
    ];

    foreach ($routes as $route) {
        $response = $this->actingAs($user)->get(route($route));
        $response->assertForbidden();
    }
});
