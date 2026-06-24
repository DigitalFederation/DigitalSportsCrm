<?php

use App\Models\User;
use Domain\EventApplications\Actions\ApproveApplicationAction;
use Domain\EventApplications\Models\ApplicationStateHistory;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\ApprovedApplicationState;
use Domain\EventApplications\States\InValidationApplicationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('changes state to approved', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->inValidation()->create();

    $action = new ApproveApplicationAction;
    $result = $action->execute($application, $user->id);

    expect($result->status_class)->toBe(ApprovedApplicationState::class);
});

test('records decision timestamp', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->inValidation()->create();

    $action = new ApproveApplicationAction;
    $result = $action->execute($application, $user->id);

    expect($result->decided_at)->not->toBeNull()
        ->and($result->decided_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('saves admin notes when provided', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->inValidation()->create();

    $action = new ApproveApplicationAction;
    $result = $action->execute($application, $user->id, 'Approved with conditions');

    expect($result->admin_notes)->toBe('Approved with conditions');
});

test('creates state history entry', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->inValidation()->create();

    $action = new ApproveApplicationAction;
    $action->execute($application, $user->id, 'All requirements met');

    $history = ApplicationStateHistory::first();
    expect($history->from_state)->toBe(InValidationApplicationState::class)
        ->and($history->to_state)->toBe(ApprovedApplicationState::class)
        ->and($history->changed_by)->toBe($user->id)
        ->and($history->notes)->toBe('All requirements met');
});

test('uses default notes when not provided', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->inValidation()->create();

    $action = new ApproveApplicationAction;
    $action->execute($application, $user->id);

    $history = ApplicationStateHistory::first();
    expect($history->notes)->toBe('Application approved');
});
