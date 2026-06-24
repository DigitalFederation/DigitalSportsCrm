<?php

use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\InValidationApplicationState;
use Domain\EventApplications\States\SubmittedApplicationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('submitted state has correct name', function () {
    $application = EventApplication::factory()->create();
    $state = new SubmittedApplicationState($application);

    expect($state->name())->toBe('submitted');
});

test('submitted state has correct color', function () {
    $application = EventApplication::factory()->create();
    $state = new SubmittedApplicationState($application);

    expect($state->color())->toBe('#3b82f6');
});

test('submitted state cannot be edited', function () {
    $application = EventApplication::factory()->create();
    $state = new SubmittedApplicationState($application);

    expect($state->canEdit())->toBeFalse();
});

test('submitted state cannot be submitted again', function () {
    $application = EventApplication::factory()->create();
    $state = new SubmittedApplicationState($application);

    expect($state->canSubmit())->toBeFalse();
});

test('submitted state can transition to in validation', function () {
    $application = EventApplication::factory()->create();
    $state = new SubmittedApplicationState($application);

    expect($state->canTransitionTo(InValidationApplicationState::class))->toBeTrue();
});

test('submitted state cannot transition to other states', function () {
    $application = EventApplication::factory()->create();
    $state = new SubmittedApplicationState($application);

    expect($state->canTransitionTo(SubmittedApplicationState::class))->toBeFalse();
});

test('state can be instantiated with event application model', function () {
    $application = EventApplication::factory()->submitted()->create();

    $state = new SubmittedApplicationState($application);

    expect($state)->toBeInstanceOf(SubmittedApplicationState::class);
});
