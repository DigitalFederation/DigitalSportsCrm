<?php

use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\DraftApplicationState;
use Domain\EventApplications\States\SubmittedApplicationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('draft state has correct name', function () {
    $application = EventApplication::factory()->create();
    $state = new DraftApplicationState($application);

    expect($state->name())->toBe('draft');
});

test('draft state has correct color', function () {
    $application = EventApplication::factory()->create();
    $state = new DraftApplicationState($application);

    expect($state->color())->toBe('#6b7280');
});

test('draft state can be edited', function () {
    $application = EventApplication::factory()->create();
    $state = new DraftApplicationState($application);

    expect($state->canEdit())->toBeTrue();
});

test('draft state can be submitted', function () {
    $application = EventApplication::factory()->create();
    $state = new DraftApplicationState($application);

    expect($state->canSubmit())->toBeTrue();
});

test('draft state can transition to submitted', function () {
    $application = EventApplication::factory()->create();
    $state = new DraftApplicationState($application);

    expect($state->canTransitionTo(SubmittedApplicationState::class))->toBeTrue();
});

test('draft state cannot transition to other states', function () {
    $application = EventApplication::factory()->create();
    $state = new DraftApplicationState($application);

    expect($state->canTransitionTo(DraftApplicationState::class))->toBeFalse();
});

test('state can be instantiated with event application model', function () {
    $application = EventApplication::factory()->create([
        'status_class' => DraftApplicationState::class,
    ]);

    $state = new DraftApplicationState($application);

    expect($state)->toBeInstanceOf(DraftApplicationState::class);
});
