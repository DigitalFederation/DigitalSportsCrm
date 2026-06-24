<?php

use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\ReturnedForCorrectionApplicationState;
use Domain\EventApplications\States\SubmittedApplicationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('returned for correction state has correct name', function () {
    $application = EventApplication::factory()->create();
    $state = new ReturnedForCorrectionApplicationState($application);

    expect($state->name())->toBe('returned_for_correction');
});

test('returned for correction state has correct color', function () {
    $application = EventApplication::factory()->create();
    $state = new ReturnedForCorrectionApplicationState($application);

    expect($state->color())->toBe('#f97316');
});

test('returned for correction state can be edited', function () {
    $application = EventApplication::factory()->create();
    $state = new ReturnedForCorrectionApplicationState($application);

    expect($state->canEdit())->toBeTrue();
});

test('returned for correction state can be submitted', function () {
    $application = EventApplication::factory()->create();
    $state = new ReturnedForCorrectionApplicationState($application);

    expect($state->canSubmit())->toBeTrue();
});

test('returned for correction state can transition to submitted', function () {
    $application = EventApplication::factory()->create();
    $state = new ReturnedForCorrectionApplicationState($application);

    expect($state->canTransitionTo(SubmittedApplicationState::class))->toBeTrue();
});

test('returned for correction state cannot transition to other states', function () {
    $application = EventApplication::factory()->create();
    $state = new ReturnedForCorrectionApplicationState($application);

    expect($state->canTransitionTo(ReturnedForCorrectionApplicationState::class))->toBeFalse();
});

test('state can be instantiated with event application model', function () {
    $application = EventApplication::factory()->returnedForCorrection()->create();

    $state = new ReturnedForCorrectionApplicationState($application);

    expect($state)->toBeInstanceOf(ReturnedForCorrectionApplicationState::class);
});
