<?php

use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\Federations\Models\Federation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->federation = Federation::factory()->create();

    $entityGroup = Group::factory()->create(['code' => 'ENTITY']);
    $this->entityUser = User::factory()->create(['group_id' => $entityGroup->id]);
    $this->entity = Entity::factory()->create();
    $this->entity->users()->attach($this->entityUser);
    $this->entity->federations()->attach($this->federation->id);

    $this->event = Event::factory()->create([
        'event_category' => 'organization',
        'status_class' => ActiveEventState::class,
        'enrollment_type' => 'only_individuals',
    ]);

    $this->actingAs($this->entityUser);
});

it('redirects to create page when enrollments are empty and no view=list param', function () {
    $response = $this->get(
        route('entity.evt-events.events.individual-enrollment.index', $this->event)
    );

    $response->assertRedirect(action(
        [\App\Http\Controllers\Entity\EvtEvents\Enrollments\IndividualEnrollmentController::class, 'create'],
        ['event' => $this->event]
    ));
});

it('shows the index page when enrollments are empty and view=list param is passed', function () {
    $response = $this->get(
        route('entity.evt-events.events.individual-enrollment.index', $this->event) . '?view=list'
    );

    $response->assertSuccessful();
    $response->assertViewIs('web.entity.evt_event.individual_enrollment.index');
});

it('allows entity to export individual enrollments', function () {
    Excel::fake();

    $response = $this->get(
        route('entity.evt-events.events.individual-enrollment.export', $this->event)
    );

    $response->assertSuccessful();
});
