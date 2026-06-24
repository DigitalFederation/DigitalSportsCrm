<?php

use App\Models\User;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\ReturnedForCorrectionApplicationState;
use Domain\Federations\Models\Federation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('federation can edit draft application', function () {
    $federation = Federation::factory()->create();
    $user = User::factory()->create();
    $user->federations()->attach($federation);

    $application = EventApplication::factory()->draft()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'event_name' => 'Original Name',
    ]);

    expect($application->state->canEdit())->toBeTrue();

    $application->update(['event_name' => 'Updated Name']);

    expect($application->fresh()->event_name)->toBe('Updated Name');
});

test('federation can edit returned-for-correction application', function () {
    $federation = Federation::factory()->create();
    $user = User::factory()->create();
    $user->federations()->attach($federation);

    $application = EventApplication::factory()->returnedForCorrection()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'event_name' => 'Original Name',
    ]);

    expect($application->status_class)->toBe(ReturnedForCorrectionApplicationState::class)
        ->and($application->state->canEdit())->toBeTrue();

    $application->update(['event_name' => 'Corrected Name']);

    expect($application->fresh()->event_name)->toBe('Corrected Name');
});

test('cannot edit submitted application', function () {
    $federation = Federation::factory()->create();

    $application = EventApplication::factory()->submitted()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
    ]);

    expect($application->state->canEdit())->toBeFalse();
});

test('cannot edit another federation\'s application', function () {
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

test('update preserves entity_type as Federation', function () {
    $federation = Federation::factory()->create();

    $application = EventApplication::factory()->draft()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'event_name' => 'Original',
    ]);

    $application->update(['event_name' => 'Updated']);

    expect($application->fresh()->entity_type)->toBe(Federation::class)
        ->and($application->fresh()->event_name)->toBe('Updated');
});

test('edit page loads with correct data', function () {
    $federation = Federation::factory()->create();

    $application = EventApplication::factory()->draft()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'event_name' => 'Test Event',
        'event_type' => 'organization',
    ]);

    $retrieved = EventApplication::find($application->id);

    expect($retrieved->event_name)->toBe('Test Event')
        ->and($retrieved->event_type)->toBe('organization')
        ->and($retrieved->entity_type)->toBe(Federation::class);
});
