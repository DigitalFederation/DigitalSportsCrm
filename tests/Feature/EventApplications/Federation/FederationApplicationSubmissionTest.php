<?php

use App\Models\User;
use Domain\EventApplications\Actions\SubmitApplicationAction;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\DraftApplicationState;
use Domain\EventApplications\States\SubmittedApplicationState;
use Domain\Federations\Models\Federation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('federation can submit draft application', function () {
    $federation = Federation::factory()->create();
    $user = User::factory()->create();
    $user->federations()->attach($federation);

    $application = EventApplication::factory()->draft()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
    ]);

    $submitAction = new SubmitApplicationAction;
    $result = $submitAction->execute($application, $user->id);

    expect($result->status_class)->toBe(SubmittedApplicationState::class)
        ->and($result->submitted_at)->not->toBeNull();
});

test('submission updates submitted_at timestamp', function () {
    $federation = Federation::factory()->create();
    $user = User::factory()->create();
    $user->federations()->attach($federation);

    $application = EventApplication::factory()->draft()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
    ]);

    expect($application->submitted_at)->toBeNull();

    $submitAction = new SubmitApplicationAction;
    $result = $submitAction->execute($application, $user->id);

    expect($result->submitted_at)->not->toBeNull()
        ->and($result->submitted_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('cannot submit already submitted application', function () {
    $federation = Federation::factory()->create();
    $user = User::factory()->create();
    $user->federations()->attach($federation);

    $application = EventApplication::factory()->submitted()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
    ]);

    expect($application->state->canSubmit())->toBeFalse();
});

test('cannot submit another federation\'s application', function () {
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

test('submission triggers state transition to submitted', function () {
    $federation = Federation::factory()->create();
    $user = User::factory()->create();
    $user->federations()->attach($federation);

    $application = EventApplication::factory()->draft()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
    ]);

    expect($application->status_class)->toBe(DraftApplicationState::class);

    $submitAction = new SubmitApplicationAction;
    $result = $submitAction->execute($application, $user->id);

    expect($result->status_class)->toBe(SubmittedApplicationState::class);
});

test('user must be associated with federation', function () {
    $federation = Federation::factory()->create();
    $user = User::factory()->create();

    expect($user->federations()->where('federation.id', $federation->id)->exists())->toBeFalse();
});

test('submission requires valid application data', function () {
    $federation = Federation::factory()->create();
    $user = User::factory()->create();
    $user->federations()->attach($federation);

    $application = EventApplication::factory()->draft()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'event_name' => 'Valid Event',
        'event_type' => 'competition',
    ]);

    expect($application->event_name)->not->toBeNull()
        ->and($application->event_type)->not->toBeNull()
        ->and($application->status_class)->toBe(DraftApplicationState::class);
});
