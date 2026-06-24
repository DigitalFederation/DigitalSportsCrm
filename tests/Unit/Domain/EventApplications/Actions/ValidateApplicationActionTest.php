<?php

use App\Models\User;
use Domain\EventApplications\Actions\ValidateApplicationAction;
use Domain\EventApplications\Models\ApplicationStateHistory;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\InValidationApplicationState;
use Domain\EventApplications\States\SubmittedApplicationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('changes state to in validation', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->submitted()->create();

    $action = new ValidateApplicationAction;
    $result = $action->execute($application, $user->id);

    expect($result->status_class)->toBe(InValidationApplicationState::class);
});

test('records validation timestamp', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->submitted()->create();

    $action = new ValidateApplicationAction;
    $result = $action->execute($application, $user->id);

    expect($result->validated_at)->not->toBeNull()
        ->and($result->validated_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('creates state history entry', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->submitted()->create();

    $action = new ValidateApplicationAction;
    $action->execute($application, $user->id);

    expect(ApplicationStateHistory::count())->toBe(1);

    $history = ApplicationStateHistory::first();
    expect($history->from_state)->toBe(SubmittedApplicationState::class)
        ->and($history->to_state)->toBe(InValidationApplicationState::class)
        ->and($history->changed_by)->toBe($user->id);
});

test('accepts custom notes', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->submitted()->create();

    $action = new ValidateApplicationAction;
    $action->execute($application, $user->id, 'Custom validation note');

    $history = ApplicationStateHistory::first();
    expect($history->notes)->toBe('Custom validation note');
});

test('uses default notes when not provided', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->submitted()->create();

    $action = new ValidateApplicationAction;
    $action->execute($application, $user->id);

    $history = ApplicationStateHistory::first();
    expect($history->notes)->toBe('Application moved to validation');
});
