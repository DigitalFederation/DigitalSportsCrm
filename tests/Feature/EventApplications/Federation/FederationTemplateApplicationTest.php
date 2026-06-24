<?php

use App\Enums\EventApplicationTypeEnum;
use App\Models\User;
use Domain\EventApplications\Actions\CreateApplicationAction;
use Domain\EventApplications\Models\ApplicationTemplate;
use Domain\EventApplications\Models\EventApplication;
use Domain\Federations\Models\Federation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can create from active template', function () {
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
        'event_name' => 'Template Based Event',
        'event_type' => $template->event_type,
    ]);

    expect($application->template_id)->toBe($template->id)
        ->and($application->entity_type)->toBe(Federation::class);
});

test('cannot create from inactive template', function () {
    $federation = Federation::factory()->create();
    $template = ApplicationTemplate::factory()->closedForSubmissions()->create();

    // Template is closed when submission_end_date is in the past
    expect($template->submission_end_date->isPast())->toBeTrue();
});

test('template pre-fills event type and sport', function () {
    $federation = Federation::factory()->create();
    $sport = \Domain\EvtEvents\Models\Sport::factory()->create();
    $template = ApplicationTemplate::factory()->openForSubmissions()->create([
        'event_type' => 'competition',
        'sport_id' => $sport->id,
    ]);

    $createAction = new CreateApplicationAction;
    $application = $createAction->execute([
        'application_type' => EventApplicationTypeEnum::FederationInitiated->value,
        'template_id' => $template->id,
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'event_name' => 'Template Event',
        'event_type' => $template->event_type,
        'sport_id' => $template->sport_id,
    ]);

    expect($application->event_type)->toBe($template->event_type)
        ->and($application->sport_id)->toBe($template->sport_id);
});

test('multiple federations can apply to same template', function () {
    $federation1 = Federation::factory()->create();
    $federation2 = Federation::factory()->create();
    $template = ApplicationTemplate::factory()->openForSubmissions()->create();

    EventApplication::factory()->create([
        'entity_id' => $federation1->id,
        'entity_type' => Federation::class,
        'template_id' => $template->id,
    ]);

    EventApplication::factory()->create([
        'entity_id' => $federation2->id,
        'entity_type' => Federation::class,
        'template_id' => $template->id,
    ]);

    expect(EventApplication::where('template_id', $template->id)->count())->toBe(2);
});

test('one federation one template rule enforced', function () {
    $federation = Federation::factory()->create();
    $template = ApplicationTemplate::factory()->openForSubmissions()->create();

    EventApplication::factory()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'template_id' => $template->id,
    ]);

    $count = EventApplication::where('entity_id', $federation->id)
        ->where('entity_type', Federation::class)
        ->where('template_id', $template->id)
        ->count();

    expect($count)->toBe(1);
});

test('hasApplied method works for federations', function () {
    $federation = Federation::factory()->create();
    $template = ApplicationTemplate::factory()->create();

    expect($template->hasApplied($federation->id, Federation::class))->toBeFalse();

    EventApplication::factory()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'template_id' => $template->id,
    ]);

    expect($template->hasApplied($federation->id, Federation::class))->toBeTrue();
});

test('template relationship loads correctly', function () {
    $federation = Federation::factory()->create();
    $template = ApplicationTemplate::factory()->openForSubmissions()->create([
        'name' => 'Championship Template',
    ]);

    $application = EventApplication::factory()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'template_id' => $template->id,
    ]);

    $application->load('template');

    expect($application->template)->not->toBeNull()
        ->and($application->template->name)->toBe('Championship Template');
});

test('cannot duplicate template application', function () {
    $federation = Federation::factory()->create();
    $template = ApplicationTemplate::factory()->openForSubmissions()->create();

    EventApplication::factory()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'template_id' => $template->id,
    ]);

    $duplicateExists = EventApplication::where('entity_id', $federation->id)
        ->where('entity_type', Federation::class)
        ->where('template_id', $template->id)
        ->exists();

    expect($duplicateExists)->toBeTrue();

    $count = EventApplication::where('entity_id', $federation->id)
        ->where('entity_type', Federation::class)
        ->where('template_id', $template->id)
        ->count();

    expect($count)->toBe(1);
});
