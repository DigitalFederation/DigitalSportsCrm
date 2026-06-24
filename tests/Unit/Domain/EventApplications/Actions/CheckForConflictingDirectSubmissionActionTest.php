<?php

use App\Enums\EventApplicationTypeEnum;
use Domain\Entities\Models\Entity;
use Domain\EventApplications\Actions\CheckForConflictingDirectSubmissionAction;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\PublishedApplicationState;
use Domain\EventApplications\States\RejectedApplicationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('finds conflicting direct submissions by event name', function () {
    $entity = Entity::factory()->create();

    EventApplication::factory()->create([
        'entity_id' => $entity->id,
        'application_type' => EventApplicationTypeEnum::DirectSubmission->value,
        'event_name' => 'Summer Training Camp',
        'event_type' => 'competition',
    ]);

    $action = new CheckForConflictingDirectSubmissionAction;
    $conflicts = $action->execute($entity->id, [
        'event_name' => 'Summer Training',
        'event_type' => 'competition',
    ]);

    expect($conflicts)->toHaveCount(1);
});

test('finds conflicts by event type', function () {
    $entity = Entity::factory()->create();

    EventApplication::factory()->create([
        'entity_id' => $entity->id,
        'application_type' => EventApplicationTypeEnum::DirectSubmission->value,
        'event_name' => 'Test Event',
        'event_type' => 'organization',
    ]);

    $action = new CheckForConflictingDirectSubmissionAction;
    $conflicts = $action->execute($entity->id, [
        'event_type' => 'organization',
    ]);

    expect($conflicts)->toHaveCount(1);
});

test('finds conflicts by start date', function () {
    $entity = Entity::factory()->create();
    $date = now()->addDays(15);

    EventApplication::factory()->create([
        'entity_id' => $entity->id,
        'application_type' => EventApplicationTypeEnum::DirectSubmission->value,
        'event_name' => 'Test Event',
        'start_date' => $date,
    ]);

    $action = new CheckForConflictingDirectSubmissionAction;
    $conflicts = $action->execute($entity->id, [
        'start_date' => $date->format('Y-m-d'),
    ]);

    expect($conflicts)->toHaveCount(1);
});

test('excludes rejected applications', function () {
    $entity = Entity::factory()->create();

    EventApplication::factory()->create([
        'entity_id' => $entity->id,
        'application_type' => EventApplicationTypeEnum::DirectSubmission->value,
        'event_name' => 'Test Event',
        'status_class' => RejectedApplicationState::class,
    ]);

    $action = new CheckForConflictingDirectSubmissionAction;
    $conflicts = $action->execute($entity->id, [
        'event_name' => 'Test',
    ]);

    expect($conflicts)->toBeEmpty();
});

test('excludes published applications', function () {
    $entity = Entity::factory()->create();

    EventApplication::factory()->create([
        'entity_id' => $entity->id,
        'application_type' => EventApplicationTypeEnum::DirectSubmission->value,
        'event_name' => 'Test Event',
        'status_class' => PublishedApplicationState::class,
    ]);

    $action = new CheckForConflictingDirectSubmissionAction;
    $conflicts = $action->execute($entity->id, [
        'event_name' => 'Test',
    ]);

    expect($conflicts)->toBeEmpty();
});

test('excludes specific application id', function () {
    $entity = Entity::factory()->create();

    $app = EventApplication::factory()->create([
        'entity_id' => $entity->id,
        'application_type' => EventApplicationTypeEnum::DirectSubmission->value,
        'event_name' => 'Test Event',
    ]);

    $action = new CheckForConflictingDirectSubmissionAction;
    $conflicts = $action->execute($entity->id, [
        'event_name' => 'Test',
    ], $app->id);

    expect($conflicts)->toBeEmpty();
});

test('only returns conflicts for same entity', function () {
    $entity1 = Entity::factory()->create();
    $entity2 = Entity::factory()->create();

    EventApplication::factory()->create([
        'entity_id' => $entity1->id,
        'application_type' => EventApplicationTypeEnum::DirectSubmission->value,
        'event_name' => 'Test Event',
    ]);

    $action = new CheckForConflictingDirectSubmissionAction;
    $conflicts = $action->execute($entity2->id, [
        'event_name' => 'Test',
    ]);

    expect($conflicts)->toBeEmpty();
});
