<?php

use App\Models\Country;
use App\Models\Group;
use App\Models\User;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\EvtEvents\States\ArchiveEventState;
use Domain\Federations\Models\Federation;

beforeEach(function () {
    // Create a federation user
    $group = Group::factory()->create(['code' => 'FEDERATION']);
    $this->user = User::factory()->create(['group_id' => $group->id]);
    $this->federation = Federation::factory()->create();
    $this->user->federations()->attach($this->federation->id);
    $this->actingAs($this->user);
});

it('displays events open to federations', function () {

    // Create various types of events
    $activeEventForAll = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'enrollment_type' => 'all',
        'name' => 'Event for All',
        'event_geographical_coverage' => 'international',
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ]);
    $activeEventForFederations = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'enrollment_type' => 'only_federations',
        'name' => 'Event for Federations',
        'event_geographical_coverage' => 'international',
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ]);

    // Perform the action
    $response = $this->get(route('federation.evt-events.events.index'));

    // Assertions
    $response->assertStatus(200)
        ->assertSee('Event for All')
        ->assertSee('Event for Federations');
});

it('does not display archived events', function () {
    // Federation event visibility is controlled by archived status (the table's default
    // filter hides ArchiveEventState), not by event date — a past-but-active event still
    // appears, so hiding is asserted via the archived state rather than a past date.
    Event::factory()->create([
        'status_class' => ArchiveEventState::class,
        'enrollment_type' => 'all',
        'name' => 'Archived Event',
        'event_geographical_coverage' => 'international',
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ]);

    $response = $this->get(route('federation.evt-events.events.index'));

    $response->assertStatus(200)
        ->assertDontSee('Archived Event');
});
it('does not display national events outside user federation country', function () {
    $foreignCountryId = 999; // ID of a country that is not the user's federation country
    Country::factory()->create(['id' => $foreignCountryId]);

    Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'enrollment_type' => 'all',
        'name' => 'Foreign National Event',
        'event_geographical_coverage' => 'national',
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ])->countries()->attach($foreignCountryId);

    $response = $this->get(route('federation.evt-events.events.index'));

    $response->assertStatus(200)
        ->assertDontSee('Foreign National Event');
});
it('does not display events exclusive to individuals or entities', function () {
    Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'enrollment_type' => 'only_individuals',
        'name' => 'Individuals Only Event',
        'event_geographical_coverage' => 'international',
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
    ]);

    $response = $this->get(route('federation.evt-events.events.index'));

    $response->assertStatus(200)
        ->assertDontSee('Individuals Only Event');
});
