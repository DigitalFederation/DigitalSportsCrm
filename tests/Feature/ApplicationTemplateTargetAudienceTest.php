<?php

use Domain\EventApplications\Models\ApplicationTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('target_audience defaults to both in factory', function () {
    $template = ApplicationTemplate::factory()->create();

    expect($template->target_audience)->toBe('both');
});

test('template can be created with target_audience entities', function () {
    $template = ApplicationTemplate::factory()->create([
        'target_audience' => 'entities',
    ]);

    expect($template->fresh()->target_audience)->toBe('entities');
});

test('template can be created with target_audience federations', function () {
    $template = ApplicationTemplate::factory()->create([
        'target_audience' => 'federations',
    ]);

    expect($template->fresh()->target_audience)->toBe('federations');
});

test('filtering templates for entities excludes federations-only templates', function () {
    $entitiesOnly = ApplicationTemplate::factory()->openForSubmissions()->create([
        'target_audience' => 'entities',
        'state' => 'open',
    ]);
    $federationsOnly = ApplicationTemplate::factory()->openForSubmissions()->create([
        'target_audience' => 'federations',
        'state' => 'open',
    ]);
    $both = ApplicationTemplate::factory()->openForSubmissions()->create([
        'target_audience' => 'both',
        'state' => 'open',
    ]);

    $entityTemplates = ApplicationTemplate::query()
        ->where('state', 'open')
        ->whereIn('target_audience', ['entities', 'both'])
        ->pluck('id');

    expect($entityTemplates)->toContain($entitiesOnly->id)
        ->toContain($both->id)
        ->not->toContain($federationsOnly->id);
});

test('filtering templates for federations excludes entities-only templates', function () {
    $entitiesOnly = ApplicationTemplate::factory()->openForSubmissions()->create([
        'target_audience' => 'entities',
        'state' => 'open',
    ]);
    $federationsOnly = ApplicationTemplate::factory()->openForSubmissions()->create([
        'target_audience' => 'federations',
        'state' => 'open',
    ]);
    $both = ApplicationTemplate::factory()->openForSubmissions()->create([
        'target_audience' => 'both',
        'state' => 'open',
    ]);

    $federationTemplates = ApplicationTemplate::query()
        ->where('is_active', true)
        ->whereIn('target_audience', ['federations', 'both'])
        ->pluck('id');

    expect($federationTemplates)->toContain($federationsOnly->id)
        ->toContain($both->id)
        ->not->toContain($entitiesOnly->id);
});

test('target_audience is fillable on ApplicationTemplate model', function () {
    $template = new ApplicationTemplate;
    expect($template->getFillable())->toContain('target_audience');
});
