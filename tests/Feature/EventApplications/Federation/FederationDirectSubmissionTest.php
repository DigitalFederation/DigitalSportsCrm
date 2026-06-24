<?php

use App\Enums\EventApplicationTypeEnum;
use App\Models\User;
use Domain\EventApplications\Actions\CheckForConflictingDirectSubmissionAction;
use Domain\EventApplications\Actions\CreateApplicationAction;
use Domain\EventApplications\Models\EventApplication;
use Domain\Federations\Models\Federation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can create direct submission without template', function () {
    $federation = Federation::factory()->create();
    $user = User::factory()->create();
    $user->federations()->attach($federation);

    $createAction = new CreateApplicationAction;
    $application = $createAction->execute([
        'application_type' => EventApplicationTypeEnum::DirectSubmission->value,
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'event_name' => 'Direct Event',
        'event_type' => 'organization',
        'start_date' => now()->addDays(30),
        'end_date' => now()->addDays(32),
    ]);

    expect($application->template_id)->toBeNull()
        ->and($application->application_type)->toBe(EventApplicationTypeEnum::DirectSubmission->value)
        ->and($application->entity_type)->toBe(Federation::class);
});

test('direct submission has null template_id', function () {
    $federation = Federation::factory()->create();

    $application = EventApplication::factory()->directSubmission()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
    ]);

    expect($application->template_id)->toBeNull()
        ->and($application->application_type)->toBe(EventApplicationTypeEnum::DirectSubmission->value);
});

test('can create multiple direct submissions', function () {
    $federation = Federation::factory()->create();

    EventApplication::factory()->directSubmission()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'event_name' => 'First Event',
    ]);

    EventApplication::factory()->directSubmission()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'event_name' => 'Second Event',
    ]);

    $count = EventApplication::where('entity_id', $federation->id)
        ->where('entity_type', Federation::class)
        ->where('application_type', EventApplicationTypeEnum::DirectSubmission->value)
        ->count();

    expect($count)->toBe(2);
});

test('direct submission validation differs from template', function () {
    $federation = Federation::factory()->create();

    $directApp = EventApplication::factory()->directSubmission()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'event_name' => 'Direct Event',
    ]);

    $templateApp = EventApplication::factory()->fromTemplate()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'event_name' => 'Template Event',
    ]);

    expect($directApp->template_id)->toBeNull()
        ->and($templateApp->template_id)->not->toBeNull();
});

test('warning system for similar submissions works', function () {
    $federation = Federation::factory()->create();

    EventApplication::factory()->directSubmission()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'event_name' => 'Summer Training Camp 2025',
        'event_type' => 'competition',
    ]);

    $action = new CheckForConflictingDirectSubmissionAction;
    $conflicts = $action->execute($federation->id, [
        'event_name' => 'Summer Training',
        'event_type' => 'competition',
    ]);

    expect($conflicts)->toHaveCount(1);
});

test('conflict detection scoped to federation', function () {
    $federation1 = Federation::factory()->create();
    $federation2 = Federation::factory()->create();

    EventApplication::factory()->directSubmission()->create([
        'entity_id' => $federation1->id,
        'entity_type' => Federation::class,
        'event_name' => 'Shared Name Event',
        'event_type' => 'competition',
    ]);

    $action = new CheckForConflictingDirectSubmissionAction;
    $conflicts = $action->execute($federation2->id, [
        'event_name' => 'Shared Name Event',
        'event_type' => 'competition',
    ]);

    expect($conflicts)->toBeEmpty();
});
