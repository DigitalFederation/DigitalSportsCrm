<?php

use App\Models\Group;
use App\Models\User;
use Domain\EvtEvents\Models\Event;
use Domain\Federations\Models\Federation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');

    $this->federationGroup = Group::firstOrCreate(['code' => 'FEDERATION'], ['name' => 'Federation']);

    // Main federation with is_default_federation = true.
    $this->mainFederation = Federation::factory()->create([
        'name' => 'Primary Federation',
        'is_default_federation' => true,
    ]);

    // Non-main federation (territorial)
    $this->otherFederation = Federation::factory()->create([
        'name' => 'Regional Federation',
        'is_default_federation' => false,
        'is_local' => true,
    ]);

    // Main federation admin user
    $this->mainFederationAdmin = User::factory()->create([
        'email' => 'main-federation@example.test',
        'group_id' => $this->federationGroup->id,
        'active' => true,
    ]);
    $this->mainFederationAdmin->assignRole('federation-admin');
    $this->mainFederationAdmin->federations()->attach($this->mainFederation->id);

    // Non-main federation admin user
    $this->otherFederationAdmin = User::factory()->create([
        'email' => 'other-federation@example.test',
        'group_id' => $this->federationGroup->id,
        'active' => true,
    ]);
    $this->otherFederationAdmin->assignRole('federation-admin');
    $this->otherFederationAdmin->federations()->attach($this->otherFederation->id);

    // Create a test event
    $this->event = Event::factory()->active()->create([
        'name' => 'Test Event',
        'is_visible' => true,
    ]);
});

// -- Index Tests --

test('main federation admin can access events index', function () {
    $this->actingAs($this->mainFederationAdmin)
        ->get(route('federation.evt-events.events.index'))
        ->assertSuccessful();
});

test('other federation admin can access events index', function () {
    $this->actingAs($this->otherFederationAdmin)
        ->get(route('federation.evt-events.events.index'))
        ->assertSuccessful();
});

// -- Create Tests --

test('main federation admin can access create event page', function () {
    $this->actingAs($this->mainFederationAdmin)
        ->get(route('federation.evt-events.events.create', 'organization'))
        ->assertSuccessful();
});

test('main federation admin can access create competition page', function () {
    $this->actingAs($this->mainFederationAdmin)
        ->get(route('federation.evt-events.events.create', 'competition'))
        ->assertSuccessful();
});

test('non-main federation admin cannot access create event page', function () {
    $this->actingAs($this->otherFederationAdmin)
        ->get(route('federation.evt-events.events.create', 'organization'))
        ->assertForbidden();
});

// -- Store Tests --

test('main federation admin can create an organization event', function () {
    $eventData = [
        'name' => 'New Federation Event',
        'start_date' => now()->addMonth()->format('Y-m-d'),
        'end_date' => now()->addMonth()->addDays(3)->format('Y-m-d'),
        'event_category' => 'organization',
        'status_class' => 'ActiveEventState',
        'enrollment_type' => 'all',
    ];

    $this->actingAs($this->mainFederationAdmin)
        ->post(route('federation.evt-events.events.store'), $eventData)
        ->assertRedirect();

    $this->assertDatabaseHas('evt_events', [
        'name' => 'New Federation Event',
        'event_category' => 'organization',
    ]);
});

test('non-main federation admin cannot create events', function () {
    $eventData = [
        'name' => 'Unauthorized Event',
        'start_date' => now()->addMonth()->format('Y-m-d'),
        'end_date' => now()->addMonth()->addDays(3)->format('Y-m-d'),
        'event_category' => 'organization',
        'status_class' => 'ActiveEventState',
        'enrollment_type' => 'all',
    ];

    $this->actingAs($this->otherFederationAdmin)
        ->post(route('federation.evt-events.events.store'), $eventData)
        ->assertForbidden();
});

// -- Edit Tests --

test('main federation admin can access full edit page', function () {
    $this->actingAs($this->mainFederationAdmin)
        ->get(route('federation.evt-events.events.edit', $this->event))
        ->assertSuccessful();
});

// -- Destroy Tests --

test('main federation admin can delete an event', function () {
    $event = Event::factory()->active()->create(['name' => 'Event To Delete']);

    $this->actingAs($this->mainFederationAdmin)
        ->delete(route('federation.evt-events.events.destroy', $event))
        ->assertRedirect(route('federation.evt-events.events.index'));
});

test('non-main federation admin cannot delete events', function () {
    $this->actingAs($this->otherFederationAdmin)
        ->delete(route('federation.evt-events.events.destroy', $this->event))
        ->assertForbidden();
});

// -- Export Tests --

test('main federation admin can access events export', function () {
    $this->actingAs($this->mainFederationAdmin)
        ->get(route('federation.evt-events.events.export'))
        ->assertSuccessful();
});

test('non-main federation admin cannot access events export', function () {
    $this->actingAs($this->otherFederationAdmin)
        ->get(route('federation.evt-events.events.export'))
        ->assertForbidden();
});
