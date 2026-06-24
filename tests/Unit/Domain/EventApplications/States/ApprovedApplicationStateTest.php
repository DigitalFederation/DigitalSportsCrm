<?php

use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\ApprovedApplicationState;
use Domain\EventApplications\States\PublishedApplicationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('approved state has correct name', function () {
    $application = EventApplication::factory()->create();
    $state = new ApprovedApplicationState($application);

    expect($state->name())->toBe('approved');
});

test('approved state has correct color', function () {
    $application = EventApplication::factory()->create();
    $state = new ApprovedApplicationState($application);

    expect($state->color())->toBe('#22c55e');
});

test('approved state cannot be edited', function () {
    $application = EventApplication::factory()->create();
    $state = new ApprovedApplicationState($application);

    expect($state->canEdit())->toBeFalse();
});

test('approved state cannot be submitted', function () {
    $application = EventApplication::factory()->create();
    $state = new ApprovedApplicationState($application);

    expect($state->canSubmit())->toBeFalse();
});

test('approved state can transition to published', function () {
    $application = EventApplication::factory()->create();
    $state = new ApprovedApplicationState($application);

    expect($state->canTransitionTo(PublishedApplicationState::class))->toBeTrue();
});

test('approved state cannot transition to other states', function () {
    $application = EventApplication::factory()->create();
    $state = new ApprovedApplicationState($application);

    expect($state->canTransitionTo(ApprovedApplicationState::class))->toBeFalse();
});

test('state can be instantiated with event application model', function () {
    $application = EventApplication::factory()->approved()->create();

    $state = new ApprovedApplicationState($application);

    expect($state)->toBeInstanceOf(ApprovedApplicationState::class);
});
