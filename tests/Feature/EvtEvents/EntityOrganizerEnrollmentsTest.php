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
use Maatwebsite\Excel\Facades\Excel;

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

it('allows entity organizer to access organizer enrollments index', function () {
    Organizer::factory()->create([
        'event_id' => $this->event->id,
        'organizable_id' => $this->entity->id,
        'organizable_type' => Entity::class,
    ]);

    $response = $this->get(
        route('entity.evt-events.events.organizer-enrollments.index', [
            'event' => $this->event,
            'enrollmentType' => 'individual',
        ])
    );

    $response->assertSuccessful();
    $response->assertViewIs('web.entity.evt_event.organizer_enrollment.index');
});

it('returns 403 for entity that is not an organizer on organizer enrollments index', function () {
    $response = $this->get(
        route('entity.evt-events.events.organizer-enrollments.index', [
            'event' => $this->event,
            'enrollmentType' => 'individual',
        ])
    );

    $response->assertForbidden();
});

it('passes event and enrollments to organizer enrollments index view', function () {
    Organizer::factory()->create([
        'event_id' => $this->event->id,
        'organizable_id' => $this->entity->id,
        'organizable_type' => Entity::class,
    ]);

    $response = $this->get(
        route('entity.evt-events.events.organizer-enrollments.index', [
            'event' => $this->event,
            'enrollmentType' => 'individual',
        ])
    );

    $response->assertViewHas('event');
    $response->assertViewHas('enrollments');
    $response->assertViewHas('enrollmentType');
});

it('allows entity organizer to export organizer enrollments', function () {
    Excel::fake();

    Organizer::factory()->create([
        'event_id' => $this->event->id,
        'organizable_id' => $this->entity->id,
        'organizable_type' => Entity::class,
    ]);

    $response = $this->post(
        route('entity.evt-events.events.organizer-enrollments.export', [
            'event' => $this->event,
            'enrollmentType' => 'individual',
        ])
    );

    $response->assertSuccessful();
});

it('redirects with error when non-organizer tries to export organizer enrollments', function () {
    $response = $this->post(
        route('entity.evt-events.events.organizer-enrollments.export', [
            'event' => $this->event,
            'enrollmentType' => 'individual',
        ])
    );

    $response->assertRedirect();
    $response->assertSessionHas('error');
});
