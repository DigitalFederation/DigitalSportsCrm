<?php

use App\Models\User;
use Domain\EventApplications\Actions\ReturnForCorrectionAction;
use Domain\EventApplications\Models\ApplicationStateHistory;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\InValidationApplicationState;
use Domain\EventApplications\States\ReturnedForCorrectionApplicationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('changes state to returned for correction', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->inValidation()->create();

    $action = new ReturnForCorrectionAction;
    $result = $action->execute($application, $user->id, 'Please fix issues');

    expect($result->status_class)->toBe(ReturnedForCorrectionApplicationState::class);
});

test('saves admin notes', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->inValidation()->create();

    $action = new ReturnForCorrectionAction;
    $result = $action->execute($application, $user->id, 'Please fix missing documents');

    expect($result->admin_notes)->toBe('Please fix missing documents');
});

test('creates state history entry with notes', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->inValidation()->create();

    $action = new ReturnForCorrectionAction;
    $action->execute($application, $user->id, 'Corrections needed');

    $history = ApplicationStateHistory::first();
    expect($history->from_state)->toBe(InValidationApplicationState::class)
        ->and($history->to_state)->toBe(ReturnedForCorrectionApplicationState::class)
        ->and($history->notes)->toBe('Corrections needed')
        ->and($history->changed_by)->toBe($user->id);
});

test('requires notes parameter', function () {
    $user = User::factory()->create();
    $application = EventApplication::factory()->inValidation()->create();

    $action = new ReturnForCorrectionAction;
    $result = $action->execute($application, $user->id, 'Required notes');

    expect($result->admin_notes)->not->toBeNull();
});
