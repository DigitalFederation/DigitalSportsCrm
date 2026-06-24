<?php

use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\PublishedApplicationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('published state has correct name', function () {
    $application = EventApplication::factory()->create();
    $state = new PublishedApplicationState($application);

    expect($state->name())->toBe('published');
});

test('published state has correct color', function () {
    $application = EventApplication::factory()->create();
    $state = new PublishedApplicationState($application);

    expect($state->color())->toBe('#a855f7');
});

test('published state cannot be edited', function () {
    $application = EventApplication::factory()->create();
    $state = new PublishedApplicationState($application);

    expect($state->canEdit())->toBeFalse();
});

test('published state cannot be submitted', function () {
    $application = EventApplication::factory()->create();
    $state = new PublishedApplicationState($application);

    expect($state->canSubmit())->toBeFalse();
});

test('published state has no valid transitions', function () {
    $application = EventApplication::factory()->create();
    $state = new PublishedApplicationState($application);

    $reflection = new ReflectionClass($state);
    $method = $reflection->getMethod('allowedTransitions');
    $method->setAccessible(true);
    $transitions = $method->invoke($state);

    expect($transitions)->toBeEmpty();
});

test('published state cannot transition to any state', function () {
    $application = EventApplication::factory()->create();
    $state = new PublishedApplicationState($application);

    expect($state->canTransitionTo(PublishedApplicationState::class))->toBeFalse();
});

test('state can be instantiated with event application model', function () {
    $application = EventApplication::factory()->published()->create();

    $state = new PublishedApplicationState($application);

    expect($state)->toBeInstanceOf(PublishedApplicationState::class);
});
