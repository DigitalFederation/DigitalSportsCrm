<?php

use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\ApprovedApplicationState;
use Domain\EventApplications\States\InValidationApplicationState;
use Domain\EventApplications\States\RejectedApplicationState;
use Domain\EventApplications\States\ReturnedForCorrectionApplicationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('in validation state has correct name', function () {
    $application = EventApplication::factory()->create();
    $state = new InValidationApplicationState($application);

    expect($state->name())->toBe('in_validation');
});

test('in validation state has correct color', function () {
    $application = EventApplication::factory()->create();
    $state = new InValidationApplicationState($application);

    expect($state->color())->toBe('#eab308');
});

test('in validation state cannot be edited', function () {
    $application = EventApplication::factory()->create();
    $state = new InValidationApplicationState($application);

    expect($state->canEdit())->toBeFalse();
});

test('in validation state cannot be submitted', function () {
    $application = EventApplication::factory()->create();
    $state = new InValidationApplicationState($application);

    expect($state->canSubmit())->toBeFalse();
});

test('in validation state can transition to approved', function () {
    $application = EventApplication::factory()->create();
    $state = new InValidationApplicationState($application);

    expect($state->canTransitionTo(ApprovedApplicationState::class))->toBeTrue();
});

test('in validation state can transition to rejected', function () {
    $application = EventApplication::factory()->create();
    $state = new InValidationApplicationState($application);

    expect($state->canTransitionTo(RejectedApplicationState::class))->toBeTrue();
});

test('in validation state can transition to returned for correction', function () {
    $application = EventApplication::factory()->create();
    $state = new InValidationApplicationState($application);

    expect($state->canTransitionTo(ReturnedForCorrectionApplicationState::class))->toBeTrue();
});

test('in validation state has exactly three valid transitions', function () {
    $application = EventApplication::factory()->create();
    $state = new InValidationApplicationState($application);

    $reflection = new ReflectionClass($state);
    $method = $reflection->getMethod('allowedTransitions');
    $method->setAccessible(true);
    $transitions = $method->invoke($state);

    expect($transitions)->toHaveCount(3);
    expect($transitions)->toContain(ApprovedApplicationState::class);
    expect($transitions)->toContain(RejectedApplicationState::class);
    expect($transitions)->toContain(ReturnedForCorrectionApplicationState::class);
});

test('state can be instantiated with event application model', function () {
    $application = EventApplication::factory()->inValidation()->create();

    $state = new InValidationApplicationState($application);

    expect($state)->toBeInstanceOf(InValidationApplicationState::class);
});
