<?php

use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\EventApplications\Actions\CheckDuplicateApplicationAction;
use Domain\EventApplications\Models\ApplicationTemplate;
use Domain\EventApplications\Models\EventApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('entity cannot submit duplicate application for same template', function () {
    $entity = Entity::factory()->create();
    $template = ApplicationTemplate::factory()->openForSubmissions()->create();
    $user = User::factory()->create();

    EventApplication::factory()->create([
        'entity_id' => $entity->id,
        'template_id' => $template->id,
        'event_name' => 'First Application',
    ]);

    $action = new CheckDuplicateApplicationAction;
    $hasDuplicate = $action->execute($entity->id, $template->id);

    expect($hasDuplicate)->toBeTrue();
    expect(EventApplication::where('entity_id', $entity->id)
        ->where('template_id', $template->id)
        ->count())->toBe(1);
});

test('multiple entities can apply to same template', function () {
    $entity1 = Entity::factory()->create();
    $entity2 = Entity::factory()->create();
    $template = ApplicationTemplate::factory()->openForSubmissions()->create();

    EventApplication::factory()->create([
        'entity_id' => $entity1->id,
        'template_id' => $template->id,
    ]);

    $action = new CheckDuplicateApplicationAction;
    $hasDuplicate = $action->execute($entity2->id, $template->id);

    expect($hasDuplicate)->toBeFalse();

    EventApplication::factory()->create([
        'entity_id' => $entity2->id,
        'template_id' => $template->id,
    ]);

    expect(EventApplication::where('template_id', $template->id)->count())->toBe(2);
});

test('entity can apply to multiple different templates', function () {
    $entity = Entity::factory()->create();
    $template1 = ApplicationTemplate::factory()->openForSubmissions()->create(['name' => 'Template 1']);
    $template2 = ApplicationTemplate::factory()->openForSubmissions()->create(['name' => 'Template 2']);

    EventApplication::factory()->create([
        'entity_id' => $entity->id,
        'template_id' => $template1->id,
    ]);

    EventApplication::factory()->create([
        'entity_id' => $entity->id,
        'template_id' => $template2->id,
    ]);

    expect(EventApplication::where('entity_id', $entity->id)->count())->toBe(2);
});

test('soft deleted applications do not count as duplicates', function () {
    $entity = Entity::factory()->create();
    $template = ApplicationTemplate::factory()->openForSubmissions()->create();

    EventApplication::factory()->create([
        'entity_id' => $entity->id,
        'template_id' => $template->id,
        'deleted_at' => now(),
    ]);

    $action = new CheckDuplicateApplicationAction;
    $hasDuplicate = $action->execute($entity->id, $template->id);

    expect($hasDuplicate)->toBeFalse();
});

test('database unique constraint prevents duplicates at db level', function () {
    $entity = Entity::factory()->create();
    $template = ApplicationTemplate::factory()->openForSubmissions()->create();

    EventApplication::factory()->create([
        'entity_id' => $entity->id,
        'template_id' => $template->id,
    ]);

    $this->assertDatabaseHas('event_applications', [
        'entity_id' => $entity->id,
        'template_id' => $template->id,
    ]);
});

test('template has applied method works correctly', function () {
    $entity = Entity::factory()->create();
    $template = ApplicationTemplate::factory()->create();

    expect($template->hasApplied($entity->id))->toBeFalse();

    EventApplication::factory()->create([
        'entity_id' => $entity->id,
        'template_id' => $template->id,
    ]);

    expect($template->hasApplied($entity->id))->toBeTrue();
});

test('template get entity application returns correct application', function () {
    $entity = Entity::factory()->create();
    $template = ApplicationTemplate::factory()->create();

    $app = EventApplication::factory()->create([
        'entity_id' => $entity->id,
        'template_id' => $template->id,
        'event_name' => 'Specific Event',
    ]);

    $retrieved = $template->getEntityApplication($entity->id);

    expect($retrieved)->not->toBeNull()
        ->and($retrieved->id)->toBe($app->id)
        ->and($retrieved->event_name)->toBe('Specific Event');
});

test('application scope hasApplied works correctly', function () {
    $entity = Entity::factory()->create();
    $template = ApplicationTemplate::factory()->create();

    expect(EventApplication::query()->hasApplied($entity->id, $template->id))->toBeFalse();

    EventApplication::factory()->create([
        'entity_id' => $entity->id,
        'template_id' => $template->id,
    ]);

    expect(EventApplication::query()->hasApplied($entity->id, $template->id))->toBeTrue();
});

test('check duplicate action returns existing application', function () {
    $entity = Entity::factory()->create();
    $template = ApplicationTemplate::factory()->create();

    $existing = EventApplication::factory()->create([
        'entity_id' => $entity->id,
        'template_id' => $template->id,
        'event_name' => 'Existing',
    ]);

    $action = new CheckDuplicateApplicationAction;
    $retrieved = $action->getExistingApplication($entity->id, $template->id);

    expect($retrieved)->not->toBeNull()
        ->and($retrieved->id)->toBe($existing->id);
});
