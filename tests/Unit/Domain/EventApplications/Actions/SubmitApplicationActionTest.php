<?php

use App\Models\User;
use Domain\EventApplications\Actions\SubmitApplicationAction;
use Domain\EventApplications\Models\ApplicationStateHistory;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\DraftApplicationState;
use Domain\EventApplications\States\SubmittedApplicationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('changes state to submitted', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->draft()->create();

    $action = new SubmitApplicationAction;
    $result = $action->execute($application, $user->id);

    expect($result->status_class)->toBe(SubmittedApplicationState::class);
});

test('records submission timestamp', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->draft()->create();

    $action = new SubmitApplicationAction;
    $result = $action->execute($application, $user->id);

    expect($result->submitted_at)->not->toBeNull()
        ->and($result->submitted_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('creates state history entry', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->draft()->create();

    $action = new SubmitApplicationAction;
    $action->execute($application, $user->id);

    expect(ApplicationStateHistory::count())->toBe(1);

    $history = ApplicationStateHistory::first();
    expect($history->application_id)->toBe($application->id)
        ->and($history->from_state)->toBe(DraftApplicationState::class)
        ->and($history->to_state)->toBe(SubmittedApplicationState::class)
        ->and($history->changed_by)->toBe($user->id);
});

test('returns fresh application instance', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->draft()->create();

    $action = new SubmitApplicationAction;
    $result = $action->execute($application, $user->id);

    expect($result->id)->toBe($application->id)
        ->and($result->status_class)->toBe(SubmittedApplicationState::class);
});
