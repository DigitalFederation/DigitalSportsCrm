<?php

use App\Models\User;
use Domain\EventApplications\Actions\RejectApplicationAction;
use Domain\EventApplications\Models\ApplicationStateHistory;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\InValidationApplicationState;
use Domain\EventApplications\States\RejectedApplicationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('changes state to rejected', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->inValidation()->create();

    $action = new RejectApplicationAction;
    $result = $action->execute($application, $user->id, 'Does not meet criteria');

    expect($result->status_class)->toBe(RejectedApplicationState::class);
});

test('records decision timestamp', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->inValidation()->create();

    $action = new RejectApplicationAction;
    $result = $action->execute($application, $user->id, 'Rejected');

    expect($result->decided_at)->not->toBeNull()
        ->and($result->decided_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('saves admin notes', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->inValidation()->create();

    $action = new RejectApplicationAction;
    $result = $action->execute($application, $user->id, 'Missing key requirements');

    expect($result->admin_notes)->toBe('Missing key requirements');
});

test('creates state history entry with notes', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->inValidation()->create();

    $action = new RejectApplicationAction;
    $action->execute($application, $user->id, 'Rejected due to conflicts');

    $history = ApplicationStateHistory::first();
    expect($history->from_state)->toBe(InValidationApplicationState::class)
        ->and($history->to_state)->toBe(RejectedApplicationState::class)
        ->and($history->changed_by)->toBe($user->id)
        ->and($history->notes)->toBe('Rejected due to conflicts');
});

test('requires notes parameter', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->inValidation()->create();

    $action = new RejectApplicationAction;
    $result = $action->execute($application, $user->id, 'Required rejection reason');

    expect($result->admin_notes)->toBe('Required rejection reason');
});
