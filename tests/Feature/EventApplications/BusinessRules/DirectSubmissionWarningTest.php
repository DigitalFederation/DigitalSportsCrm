<?php

use App\Enums\EventApplicationTypeEnum;
use Domain\Entities\Models\Entity;
use Domain\EventApplications\Actions\CheckForConflictingDirectSubmissionAction;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\RejectedApplicationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('detects potential conflict when creating similar direct submission', function () {
    $entity = Entity::factory()->create();

    EventApplication::factory()->directSubmission()->create([
        'entity_id' => $entity->id,
        'event_name' => 'Summer Training Camp 2025',
        'event_type' => 'competition',
        'start_date' => now()->addDays(30),
    ]);

    $action = new CheckForConflictingDirectSubmissionAction;
    $conflicts = $action->execute($entity->id, [
        'event_name' => 'Summer Training Camp',
        'event_type' => 'competition',
    ]);

    expect($conflicts)->toHaveCount(1);
});

test('check for conflicting direct submission action works correctly', function () {
    $entity = Entity::factory()->create();

    EventApplication::factory()->directSubmission()->create([
        'entity_id' => $entity->id,
        'event_name' => 'Workshop Alpha',
        'event_type' => 'organization',
        'start_date' => now()->addDays(20),
    ]);

    $action = new CheckForConflictingDirectSubmissionAction;
    $conflicts = $action->execute($entity->id, [
        'event_name' => 'Workshop',
        'event_type' => 'organization',
        'start_date' => now()->addDays(20)->format('Y-m-d'),
    ]);

    expect($conflicts)->not->toBeEmpty();
});

test('warning does not block submission', function () {
    $entity = Entity::factory()->create();

    EventApplication::factory()->directSubmission()->create([
        'entity_id' => $entity->id,
        'event_name' => 'Competition A',
        'event_type' => 'competition',
    ]);

    $newApp = EventApplication::factory()->directSubmission()->create([
        'entity_id' => $entity->id,
        'event_name' => 'Competition B',
        'event_type' => 'competition',
    ]);

    expect($newApp)->toBeInstanceOf(EventApplication::class);
    expect(EventApplication::where('entity_id', $entity->id)
        ->where('application_type', EventApplicationTypeEnum::DirectSubmission->value)
        ->count())->toBe(2);
});

test('excludes rejected applications from conflict detection', function () {
    $entity = Entity::factory()->create();

    EventApplication::factory()->directSubmission()->rejected()->create([
        'entity_id' => $entity->id,
        'event_name' => 'Rejected Event',
        'event_type' => 'competition',
        'status_class' => RejectedApplicationState::class,
    ]);

    $action = new CheckForConflictingDirectSubmissionAction;
    $conflicts = $action->execute($entity->id, [
        'event_name' => 'Rejected Event',
        'event_type' => 'competition',
    ]);

    expect($conflicts)->toBeEmpty();
});

test('excludes current application when checking conflicts', function () {
    $entity = Entity::factory()->create();

    $app = EventApplication::factory()->directSubmission()->create([
        'entity_id' => $entity->id,
        'event_name' => 'My Event',
        'event_type' => 'organization',
    ]);

    $action = new CheckForConflictingDirectSubmissionAction;
    $conflicts = $action->execute($entity->id, [
        'event_name' => 'My Event',
        'event_type' => 'organization',
    ], $app->id);

    expect($conflicts)->toBeEmpty();
});

test('only checks for same entity submissions', function () {
    $entity1 = Entity::factory()->create();
    $entity2 = Entity::factory()->create();

    EventApplication::factory()->directSubmission()->create([
        'entity_id' => $entity1->id,
        'event_name' => 'Shared Name Event',
        'event_type' => 'competition',
    ]);

    $action = new CheckForConflictingDirectSubmissionAction;
    $conflicts = $action->execute($entity2->id, [
        'event_name' => 'Shared Name Event',
        'event_type' => 'competition',
    ]);

    expect($conflicts)->toBeEmpty();
});

test('partial name matching works with like operator', function () {
    $entity = Entity::factory()->create();

    EventApplication::factory()->directSubmission()->create([
        'entity_id' => $entity->id,
        'event_name' => 'Advanced Training Workshop',
        'event_type' => 'organization',
    ]);

    $action = new CheckForConflictingDirectSubmissionAction;
    $conflicts = $action->execute($entity->id, [
        'event_name' => 'Training',
    ]);

    expect($conflicts)->toHaveCount(1);
});
