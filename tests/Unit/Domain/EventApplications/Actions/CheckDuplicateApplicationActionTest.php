<?php

use Domain\Entities\Models\Entity;
use Domain\EventApplications\Actions\CheckDuplicateApplicationAction;
use Domain\EventApplications\Models\ApplicationTemplate;
use Domain\EventApplications\Models\EventApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('returns true when entity already applied to template', function () {
    $entity = Entity::factory()->create();
    $template = ApplicationTemplate::factory()->create();

    EventApplication::factory()->create([
        'entity_id' => $entity->id,
        'template_id' => $template->id,
    ]);

    $action = new CheckDuplicateApplicationAction;
    $result = $action->execute($entity->id, $template->id);

    expect($result)->toBeTrue();
});

test('returns false when entity has not applied to template', function () {
    $entity = Entity::factory()->create();
    $template = ApplicationTemplate::factory()->create();

    $action = new CheckDuplicateApplicationAction;
    $result = $action->execute($entity->id, $template->id);

    expect($result)->toBeFalse();
});

test('returns false for direct submissions with no template', function () {
    $entity = Entity::factory()->create();

    $action = new CheckDuplicateApplicationAction;
    $result = $action->execute($entity->id, null);

    expect($result)->toBeFalse();
});

test('returns false when only soft deleted applications exist', function () {
    $entity = Entity::factory()->create();
    $template = ApplicationTemplate::factory()->create();

    EventApplication::factory()->create([
        'entity_id' => $entity->id,
        'template_id' => $template->id,
        'deleted_at' => now(),
    ]);

    $action = new CheckDuplicateApplicationAction;
    $result = $action->execute($entity->id, $template->id);

    expect($result)->toBeFalse();
});

test('get existing application returns correct application', function () {
    $entity = Entity::factory()->create();
    $template = ApplicationTemplate::factory()->create();

    $existingApp = EventApplication::factory()->create([
        'entity_id' => $entity->id,
        'template_id' => $template->id,
        'event_name' => 'Existing Event',
    ]);

    $action = new CheckDuplicateApplicationAction;
    $result = $action->getExistingApplication($entity->id, $template->id);

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($existingApp->id)
        ->and($result->event_name)->toBe('Existing Event');
});

test('get existing application returns null when no application exists', function () {
    $entity = Entity::factory()->create();
    $template = ApplicationTemplate::factory()->create();

    $action = new CheckDuplicateApplicationAction;
    $result = $action->getExistingApplication($entity->id, $template->id);

    expect($result)->toBeNull();
});

test('different entities can apply to same template', function () {
    $entity1 = Entity::factory()->create();
    $entity2 = Entity::factory()->create();
    $template = ApplicationTemplate::factory()->create();

    EventApplication::factory()->create([
        'entity_id' => $entity1->id,
        'template_id' => $template->id,
    ]);

    $action = new CheckDuplicateApplicationAction;
    $result = $action->execute($entity2->id, $template->id);

    expect($result)->toBeFalse();
});
