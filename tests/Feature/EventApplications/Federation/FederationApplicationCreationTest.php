<?php

use App\Enums\EventApplicationTypeEnum;
use App\Models\User;
use Domain\EventApplications\Actions\CreateApplicationAction;
use Domain\EventApplications\Models\ApplicationTemplate;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\DraftApplicationState;
use Domain\Federations\Models\Federation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('federation can create application from active template', function () {
    $federation = Federation::factory()->create();
    $template = ApplicationTemplate::factory()->openForSubmissions()->create();
    $user = User::factory()->create();
    $user->federations()->attach($federation);

    $createAction = new CreateApplicationAction;
    $application = $createAction->execute([
        'application_type' => EventApplicationTypeEnum::FederationInitiated->value,
        'template_id' => $template->id,
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'event_name' => 'Federation Championship Event',
        'event_type' => 'competition',
    ]);

    expect($application)->toBeInstanceOf(EventApplication::class)
        ->and($application->entity_id)->toBe($federation->id)
        ->and($application->entity_type)->toBe(Federation::class)
        ->and($application->template_id)->toBe($template->id);
});

test('federation can create direct submission application', function () {
    $federation = Federation::factory()->create();
    $user = User::factory()->create();
    $user->federations()->attach($federation);

    $createAction = new CreateApplicationAction;
    $application = $createAction->execute([
        'application_type' => EventApplicationTypeEnum::DirectSubmission->value,
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'event_name' => 'Federation Training Program',
        'event_type' => 'competition',
        'start_date' => now()->addDays(30),
        'end_date' => now()->addDays(32),
    ]);

    expect($application->entity_type)->toBe(Federation::class)
        ->and($application->template_id)->toBeNull()
        ->and($application->application_type)->toBe(EventApplicationTypeEnum::DirectSubmission->value);
});

test('federation cannot create without federation association', function () {
    $federation = Federation::factory()->create();
    $template = ApplicationTemplate::factory()->openForSubmissions()->create();

    expect(EventApplication::where('entity_id', $federation->id)
        ->where('entity_type', Federation::class)
        ->count())->toBe(0);
});

test('entity_type is set to Federation class', function () {
    $federation = Federation::factory()->create();
    $user = User::factory()->create();
    $user->federations()->attach($federation);

    $application = EventApplication::factory()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'event_name' => 'Test Event',
    ]);

    expect($application->entity_type)->toBe(Federation::class)
        ->and($application->entity_type)->not->toBe(\Domain\Entities\Models\Entity::class);
});

test('duplicate prevention works at creation', function () {
    $federation = Federation::factory()->create();
    $template = ApplicationTemplate::factory()->openForSubmissions()->create();

    EventApplication::factory()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'template_id' => $template->id,
    ]);

    expect(EventApplication::where('entity_id', $federation->id)
        ->where('entity_type', Federation::class)
        ->where('template_id', $template->id)
        ->count())->toBe(1);
});

test('required fields are enforced', function () {
    $federation = Federation::factory()->create();

    $application = EventApplication::factory()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'event_name' => 'Required Name',
        'event_type' => 'competition',
    ]);

    expect($application->event_name)->not->toBeNull()
        ->and($application->event_type)->not->toBeNull()
        ->and($application->entity_id)->not->toBeNull()
        ->and($application->entity_type)->not->toBeNull();
});

test('application defaults to draft state', function () {
    $federation = Federation::factory()->create();

    $createAction = new CreateApplicationAction;
    $application = $createAction->execute([
        'application_type' => EventApplicationTypeEnum::DirectSubmission->value,
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'event_name' => 'New Event',
        'event_type' => 'organization',
    ]);

    expect($application->status_class)->toBe(DraftApplicationState::class)
        ->and($application->submitted_at)->toBeNull();
});

test('template_id is set when creating from template', function () {
    $federation = Federation::factory()->create();
    $template = ApplicationTemplate::factory()->openForSubmissions()->create();

    $createAction = new CreateApplicationAction;
    $application = $createAction->execute([
        'application_type' => EventApplicationTypeEnum::FederationInitiated->value,
        'template_id' => $template->id,
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'event_name' => 'Template Event',
        'event_type' => 'competition',
    ]);

    expect($application->template_id)->toBe($template->id)
        ->and($application->application_type)->toBe(EventApplicationTypeEnum::FederationInitiated->value);
});
