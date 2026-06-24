<?php

use App\Enums\EvtCompetitionStatusEnum;
use App\Enums\EvtEventEnrollmentTypeEnum;
use App\Models\Group;
use App\Models\Sport;
use Database\Factories\UserFactory;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\EventRole;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');
    // Set up required entities and permissions
    $this->group = Group::factory()->create(['code' => 'ADMIN']);
    $this->user = UserFactory::new()->create([
        'group_id' => $this->group->id,
    ]);
    $this->user->assignRole('admin');
    $this->sport = Sport::factory()->create();

    $this->eventData = [
        'name' => 'Test Organizational Event',
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->addDays(5)->format('Y-m-d'),
        'event_category' => 'organization',
        'status_class' => ActiveEventState::class,
        'enrollment_type' => 'open', // Assuming you have predefined enrollment types
        'organization_type' => 'conference', // Assuming you have predefined organization types
        'venue' => 'Test Venue',
        'venue_address' => '123 Test St.',
        'venue_city' => 'Test City',
        'venue_country' => 'Test Country',
    ];

    $this->competitionEventData = [
        'name' => 'Test Competition Event',
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->addDays(10)->format('Y-m-d'),
        'event_category' => 'competition',
        'status_class' => ActiveEventState::class,
        'enrollment_type' => EvtEventEnrollmentTypeEnum::only_federations->name,
        'venue' => 'Test Competition Venue',
        'venue_address' => '123 Test Ave',
        'venue_city' => 'Competition City',
        'venue_country' => 'Competition Country',
        'sport_id' => $this->sport->id,
        // Nesting the competition fields correctly
        'competition' => [
            'status_class' => EvtCompetitionStatusEnum::APPROVED->value,
            'sport_id' => $this->sport->id,
            'competition_start_date' => now()->format('Y-m-d'),
            'competition_end_date' => now()->addDays(5)->format('Y-m-d'),
        ],
    ];

});

it('creates a new organizational event', function () {
    // Acting as the user with permissions to create events
    $this->actingAs($this->user);

    // Making a POST request to the route handling event creation
    $response = $this->post(route('admin.evt-events.events.store'), $this->eventData);
    // Additional assertions to verify the database has the new event
    $event = Event::where('name', $this->eventData['name'])->first();
    // Assertions to ensure that the event was created successfully
    $response->assertRedirect(route('admin.evt-events.events.show', $event->id));
    $response->assertSessionHas('success', 'Event created successfully.');

    expect($event)->not->toBeNull();
    expect($event->status_class)->toEqual(ActiveEventState::class);
    expect($event->isOrganizationEvent())->toBeTrue();
});
it('creates a new competition event', function () {
    // Acting as the user with permissions to create events
    $this->actingAs($this->user);

    // Making a POST request to the route handling event creation
    $response = $this->post(route('admin.evt-events.events.store'), $this->competitionEventData);
    $event = Event::where('name', $this->competitionEventData['name'])
        ->with('competition')
        ->first();

    // Assertions to ensure that the competition event was created successfully
    $response->assertRedirect(route('admin.evt-events.events.show', $event->id));
    $response->assertSessionHas('success', 'Event created successfully.');

    // Additional assertions to verify the database has the new competition event

    expect($event)->not->toBeNull();
    expect($event->status_class)->toEqual(ActiveEventState::class);
    expect($event->isSportEvent())->toBeTrue();
    expect($event->competition->sport_id)->toEqual($this->sport->id);

});

it('can clear event management roles using empty values', function () {
    // Acting as the user with permissions
    $this->actingAs($this->user);

    // Create an individual to assign as a role
    $individual = Individual::factory()->create();

    // Create an event first
    $event = Event::factory()->create([
        'name' => 'Test Event for Role Clearing',
        'event_category' => 'organization',
        'status_class' => ActiveEventState::class,
    ]);

    // Assign a technical delegate initially
    EventRole::firstOrCreate([
        'event_id' => $event->id,
        'individual_id' => $individual->id,
        'role' => EventRole::ROLE_TECHNICAL_DELEGATE,
    ]);

    // Verify the role exists
    expect(EventRole::where('event_id', $event->id)
        ->where('role', EventRole::ROLE_TECHNICAL_DELEGATE)
        ->exists())->toBeTrue();

    // Update the event with empty role values (simulating the clear button action)
    $updateData = [
        'name' => 'Test Event for Role Clearing',
        'event_category' => 'organization',
        'status_class' => 'ActiveEventState',
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->addDays(5)->format('Y-m-d'),
        'enrollment_type' => 'open',
        'venue' => 'Test Venue',
        'venue_address' => '123 Test St.',
        'venue_city' => 'Test City',
        'venue_country' => 'Test Country',
        'technical_delegate_id' => '', // Empty string to simulate cleared form field
        'chief_judge_id' => '',
        'competition_director_id' => '',
    ];

    $response = $this->put(route('admin.evt-events.events.update', $event->id), $updateData);

    // Verify the response is successful
    $response->assertRedirect(route('admin.evt-events.events.show', $event->id));
    $response->assertSessionHas('success', 'Event updated successfully.');

    // Verify the role was removed from the database
    expect(EventRole::where('event_id', $event->id)
        ->where('role', EventRole::ROLE_TECHNICAL_DELEGATE)
        ->exists())->toBeFalse();
});
