<?php

use Domain\Entities\Models\Entity;
use Domain\EventApplications\Actions\CheckDuplicateApplicationAction;
use Domain\EventApplications\Models\ApplicationTemplate;
use Domain\EventApplications\Models\EventApplication;
use Domain\Federations\Models\Federation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('federation cannot apply twice to same template', function () {
    $federation = Federation::factory()->create();
    $template = ApplicationTemplate::factory()->openForSubmissions()->create();

    EventApplication::factory()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'template_id' => $template->id,
    ]);

    $action = new CheckDuplicateApplicationAction;
    $hasDuplicate = $action->execute($federation->id, $template->id, Federation::class);

    expect($hasDuplicate)->toBeTrue();
});

test('multiple federations can apply to same template', function () {
    $federation1 = Federation::factory()->create();
    $federation2 = Federation::factory()->create();
    $template = ApplicationTemplate::factory()->openForSubmissions()->create();

    EventApplication::factory()->create([
        'entity_id' => $federation1->id,
        'entity_type' => Federation::class,
        'template_id' => $template->id,
    ]);

    $action = new CheckDuplicateApplicationAction;
    $hasDuplicate = $action->execute($federation2->id, $template->id, Federation::class);

    expect($hasDuplicate)->toBeFalse();

    EventApplication::factory()->create([
        'entity_id' => $federation2->id,
        'entity_type' => Federation::class,
        'template_id' => $template->id,
    ]);

    expect(EventApplication::where('template_id', $template->id)->count())->toBe(2);
});

test('federation can apply to multiple different templates', function () {
    $federation = Federation::factory()->create();
    $template1 = ApplicationTemplate::factory()->openForSubmissions()->create(['name' => 'Template 1']);
    $template2 = ApplicationTemplate::factory()->openForSubmissions()->create(['name' => 'Template 2']);

    EventApplication::factory()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'template_id' => $template1->id,
    ]);

    EventApplication::factory()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'template_id' => $template2->id,
    ]);

    expect(EventApplication::where('entity_id', $federation->id)
        ->where('entity_type', Federation::class)
        ->count())->toBe(2);
});

test('soft deleted applications don\'t block new applications', function () {
    $federation = Federation::factory()->create();
    $template = ApplicationTemplate::factory()->openForSubmissions()->create();

    EventApplication::factory()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'template_id' => $template->id,
        'deleted_at' => now(),
    ]);

    $action = new CheckDuplicateApplicationAction;
    $hasDuplicate = $action->execute($federation->id, $template->id, Federation::class);

    expect($hasDuplicate)->toBeFalse();
});

test('CheckDuplicateApplicationAction works for Federation', function () {
    $federation = Federation::factory()->create();
    $template = ApplicationTemplate::factory()->openForSubmissions()->create();

    $action = new CheckDuplicateApplicationAction;
    $hasDuplicate = $action->execute($federation->id, $template->id, Federation::class);

    expect($hasDuplicate)->toBeFalse();

    EventApplication::factory()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'template_id' => $template->id,
    ]);

    $hasDuplicate = $action->execute($federation->id, $template->id, Federation::class);

    expect($hasDuplicate)->toBeTrue();
});

test('hasApplied scope works correctly', function () {
    $federation = Federation::factory()->create();
    $template = ApplicationTemplate::factory()->create();

    expect(EventApplication::query()->hasApplied($federation->id, $template->id, Federation::class))->toBeFalse();

    EventApplication::factory()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'template_id' => $template->id,
    ]);

    expect(EventApplication::query()->hasApplied($federation->id, $template->id, Federation::class))->toBeTrue();
});

test('duplicate check ignores other entity types (Entity vs Federation)', function () {
    $federation = Federation::factory()->create();
    $entity = Entity::factory()->create();
    $template = ApplicationTemplate::factory()->openForSubmissions()->create();

    EventApplication::factory()->create([
        'entity_id' => $entity->id,
        'entity_type' => Entity::class,
        'template_id' => $template->id,
    ]);

    $action = new CheckDuplicateApplicationAction;
    $hasDuplicate = $action->execute($federation->id, $template->id, Federation::class);

    expect($hasDuplicate)->toBeFalse();
});

test('database allows federation + entity with same template', function () {
    $federation = Federation::factory()->create();
    $entity = Entity::factory()->create();
    $template = ApplicationTemplate::factory()->openForSubmissions()->create();

    EventApplication::factory()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'template_id' => $template->id,
    ]);

    EventApplication::factory()->create([
        'entity_id' => $entity->id,
        'entity_type' => Entity::class,
        'template_id' => $template->id,
    ]);

    $federationApps = EventApplication::where('entity_type', Federation::class)
        ->where('template_id', $template->id)
        ->count();

    $entityApps = EventApplication::where('entity_type', Entity::class)
        ->where('template_id', $template->id)
        ->count();

    expect($federationApps)->toBe(1)
        ->and($entityApps)->toBe(1);
});

test('getExistingApplication returns federation application', function () {
    $federation = Federation::factory()->create();
    $template = ApplicationTemplate::factory()->create();

    $app = EventApplication::factory()->create([
        'entity_id' => $federation->id,
        'entity_type' => Federation::class,
        'template_id' => $template->id,
        'event_name' => 'Existing Federation Event',
    ]);

    $action = new CheckDuplicateApplicationAction;
    $retrieved = $action->getExistingApplication($federation->id, $template->id, Federation::class);

    expect($retrieved)->not->toBeNull()
        ->and($retrieved->id)->toBe($app->id)
        ->and($retrieved->entity_type)->toBe(Federation::class);
});
