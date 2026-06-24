<?php

use App\Models\User;
use Domain\EventApplications\Actions\PublishApplicationAction;
use Domain\EventApplications\Models\ApplicationStateHistory;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\ApprovedApplicationState;
use Domain\EventApplications\States\PublishedApplicationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('changes state to published', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->approved()->create();

    $action = new PublishApplicationAction;
    $result = $action->execute($application, $user->id);

    expect($result->status_class)->toBe(PublishedApplicationState::class);
});

test('records publication timestamp', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->approved()->create();

    $action = new PublishApplicationAction;
    $result = $action->execute($application, $user->id);

    expect($result->published_at)->not->toBeNull()
        ->and($result->published_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('saves published event id when provided', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->approved()->create();

    $action = new PublishApplicationAction;
    $result = $action->execute($application, $user->id, 123);

    expect($result->published_event_id)->toBe(123);
});

test('creates state history entry', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->approved()->create();

    $action = new PublishApplicationAction;
    $action->execute($application, $user->id);

    $history = ApplicationStateHistory::first();
    expect($history->from_state)->toBe(ApprovedApplicationState::class)
        ->and($history->to_state)->toBe(PublishedApplicationState::class)
        ->and($history->changed_by)->toBe($user->id)
        ->and($history->notes)->toBe('Application published to calendar');
});

test('works without published event id', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->approved()->create();

    $action = new PublishApplicationAction;
    $result = $action->execute($application, $user->id);

    expect($result->published_event_id)->toBeNull()
        ->and($result->status_class)->toBe(PublishedApplicationState::class);
});
