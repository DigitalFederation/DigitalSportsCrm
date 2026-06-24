<?php

use App\Enums\UserGroupEnum;
use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Organizer;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\Federations\Models\Federation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->federation = Federation::factory()->create(['is_default_federation' => true]);

    $entityGroup = Group::factory()->create(['id' => UserGroupEnum::ENTITY->value, 'code' => 'ENTITY']);
    $this->entityUser = User::factory()->create(['group_id' => $entityGroup->id]);
    $this->entity = Entity::factory()->create();
    $this->entity->users()->attach($this->entityUser);
    $this->entity->federations()->attach($this->federation->id);

    $this->event = Event::factory()->create([
        'event_category' => 'organization',
        'status_class' => ActiveEventState::class,
    ]);

    $this->actingAs($this->entityUser);
});

it('allows entity organizer to access staff enrollment index', function () {
    Organizer::factory()->create([
        'event_id' => $this->event->id,
        'organizable_id' => $this->entity->id,
        'organizable_type' => Entity::class,
    ]);

    $response = $this->get(
        route('entity.evt-events.events.staff-enrollment.index', $this->event)
    );

    $response->assertSuccessful();
    $response->assertViewIs('web.entity.evt_event.staff_enrollment.index');
});

it('returns 403 for entity that is not an organizer on staff enrollment index', function () {
    $response = $this->get(
        route('entity.evt-events.events.staff-enrollment.index', $this->event)
    );

    $response->assertForbidden();
});

it('allows entity organizer to access staff enrollment create page', function () {
    Organizer::factory()->create([
        'event_id' => $this->event->id,
        'organizable_id' => $this->entity->id,
        'organizable_type' => Entity::class,
    ]);

    $response = $this->get(
        route('entity.evt-events.events.staff-enrollment.create', $this->event)
    );

    $response->assertSuccessful();
    $response->assertViewIs('web.entity.evt_event.staff_enrollment.create');
});

it('returns 403 for entity that is not an organizer on staff enrollment create', function () {
    $response = $this->get(
        route('entity.evt-events.events.staff-enrollment.create', $this->event)
    );

    $response->assertForbidden();
});

it('passes entity and enrollments to index view', function () {
    Organizer::factory()->create([
        'event_id' => $this->event->id,
        'organizable_id' => $this->entity->id,
        'organizable_type' => Entity::class,
    ]);

    $response = $this->get(
        route('entity.evt-events.events.staff-enrollment.index', $this->event)
    );

    $response->assertViewHas('entity');
    $response->assertViewHas('enrollments');
    $response->assertViewHas('event');
});
