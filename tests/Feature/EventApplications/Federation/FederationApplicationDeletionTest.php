<?php

use App\Models\User;
use Domain\EventApplications\Models\EventApplication;
use Domain\Federations\Models\Federation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('federation can delete draft application', function () {
    $federation = Federation::factory()->create();
    $user = User::factory()->create();
    $user->federations()->attach($federation);

    $application = EventApplication::factory()->draft()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
    ]);

    $applicationId = $application->id;

    expect($application->state->canDelete())->toBeTrue();

    $application->delete();

    expect(EventApplication::withTrashed()->find($applicationId))->not->toBeNull()
        ->and(EventApplication::find($applicationId))->toBeNull();
});

test('cannot delete submitted application', function () {
    $federation = Federation::factory()->create();

    $application = EventApplication::factory()->submitted()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
    ]);

    expect($application->state->canDelete())->toBeFalse();
});

test('cannot delete another federation\'s application', function () {
    $federation1 = Federation::factory()->create();
    $federation2 = Federation::factory()->create();
    $user = User::factory()->create();
    $user->federations()->attach($federation2);

    $application = EventApplication::factory()->draft()->create([
        'entity_id' => $federation1->id,
        'entity_type' => Federation::class,
    ]);

    expect($application->entity_id)->toBe($federation1->id)
        ->and($application->entity_id)->not->toBe($federation2->id);
});

test('soft delete works correctly', function () {
    $federation = Federation::factory()->create();

    $application = EventApplication::factory()->draft()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
    ]);

    $applicationId = $application->id;

    $application->delete();

    expect(EventApplication::find($applicationId))->toBeNull()
        ->and(EventApplication::withTrashed()->find($applicationId))->not->toBeNull()
        ->and(EventApplication::withTrashed()->find($applicationId)->deleted_at)->not->toBeNull();
});

test('deleted applications don\'t count for duplicate check', function () {
    $federation = Federation::factory()->create();
    $template = \Domain\EventApplications\Models\ApplicationTemplate::factory()->create();

    $application = EventApplication::factory()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'template_id' => $template->id,
    ]);

    $application->delete();

    $activeCount = EventApplication::where('entity_id', $federation->id)
        ->where('entity_type', Federation::class)
        ->where('template_id', $template->id)
        ->whereNull('deleted_at')
        ->count();

    expect($activeCount)->toBe(0);
});
