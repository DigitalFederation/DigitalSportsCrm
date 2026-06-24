<?php

use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\RejectedApplicationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('rejected state has correct name', function () {
    $application = EventApplication::factory()->create();
    $state = new RejectedApplicationState($application);

    expect($state->name())->toBe('rejected');
});

test('rejected state has correct color', function () {
    $application = EventApplication::factory()->create();
    $state = new RejectedApplicationState($application);

    expect($state->color())->toBe('#ef4444');
});

test('rejected state cannot be edited', function () {
    $application = EventApplication::factory()->create();
    $state = new RejectedApplicationState($application);

    expect($state->canEdit())->toBeFalse();
});

test('rejected state cannot be submitted', function () {
    $application = EventApplication::factory()->create();
    $state = new RejectedApplicationState($application);

    expect($state->canSubmit())->toBeFalse();
});

test('rejected state has no valid transitions', function () {
    $application = EventApplication::factory()->create();
    $state = new RejectedApplicationState($application);

    $reflection = new ReflectionClass($state);
    $method = $reflection->getMethod('allowedTransitions');
    $method->setAccessible(true);
    $transitions = $method->invoke($state);

    expect($transitions)->toBeEmpty();
});

test('rejected state cannot transition to any state', function () {
    $application = EventApplication::factory()->create();
    $state = new RejectedApplicationState($application);

    expect($state->canTransitionTo(RejectedApplicationState::class))->toBeFalse();
});

test('state can be instantiated with event application model', function () {
    $application = EventApplication::factory()->rejected()->create();

    $state = new RejectedApplicationState($application);

    expect($state)->toBeInstanceOf(RejectedApplicationState::class);
});
