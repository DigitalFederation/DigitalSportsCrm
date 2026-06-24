<?php

use Domain\EventApplications\Actions\CheckConflictingApplicationsAction;
use Domain\EventApplications\Models\EventApplication;
use Domain\EventApplications\States\DraftApplicationState;
use Domain\EventApplications\States\PublishedApplicationState;
use Domain\EventApplications\States\RejectedApplicationState;
use Domain\EvtEvents\Models\Sport;
use Domain\Geographic\Models\District;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('finds conflicting applications by event type and dates', function () {
    $sport = Sport::factory()->create();
    $district = District::factory()->create();

    EventApplication::factory()->create([
        'event_type' => 'competition',
        'sport_id' => $sport->id,
        'district_id' => $district->id,
        'start_date' => now()->addDays(10),
        'end_date' => now()->addDays(12),
        'status_class' => DraftApplicationState::class,
    ]);

    $action = new CheckConflictingApplicationsAction;
    $conflicts = $action->execute([
        'event_type' => 'competition',
        'sport_id' => $sport->id,
        'district_id' => $district->id,
        'start_date' => now()->addDays(11),
        'end_date' => now()->addDays(13),
    ]);

    expect($conflicts)->toHaveCount(1);
});

test('excludes rejected applications from conflicts', function () {
    $sport = Sport::factory()->create();

    EventApplication::factory()->create([
        'event_type' => 'competition',
        'sport_id' => $sport->id,
        'start_date' => now()->addDays(10),
        'end_date' => now()->addDays(12),
        'status_class' => RejectedApplicationState::class,
    ]);

    $action = new CheckConflictingApplicationsAction;
    $conflicts = $action->execute([
        'event_type' => 'competition',
        'sport_id' => $sport->id,
        'start_date' => now()->addDays(11),
        'end_date' => now()->addDays(13),
    ]);

    expect($conflicts)->toBeEmpty();
});

test('excludes published applications from conflicts', function () {
    $sport = Sport::factory()->create();

    EventApplication::factory()->create([
        'event_type' => 'competition',
        'sport_id' => $sport->id,
        'start_date' => now()->addDays(10),
        'end_date' => now()->addDays(12),
        'status_class' => PublishedApplicationState::class,
    ]);

    $action = new CheckConflictingApplicationsAction;
    $conflicts = $action->execute([
        'event_type' => 'competition',
        'sport_id' => $sport->id,
        'start_date' => now()->addDays(11),
        'end_date' => now()->addDays(13),
    ]);

    expect($conflicts)->toBeEmpty();
});

test('excludes specific application id', function () {
    $sport = Sport::factory()->create();

    $app = EventApplication::factory()->create([
        'event_type' => 'competition',
        'sport_id' => $sport->id,
        'start_date' => now()->addDays(10),
        'end_date' => now()->addDays(12),
    ]);

    $action = new CheckConflictingApplicationsAction;
    $conflicts = $action->execute([
        'event_type' => 'competition',
        'sport_id' => $sport->id,
        'start_date' => now()->addDays(11),
        'end_date' => now()->addDays(13),
    ], $app->id);

    expect($conflicts)->toBeEmpty();
});

test('finds no conflicts when dates do not overlap', function () {
    $sport = Sport::factory()->create();

    EventApplication::factory()->create([
        'event_type' => 'competition',
        'sport_id' => $sport->id,
        'start_date' => now()->addDays(10),
        'end_date' => now()->addDays(12),
    ]);

    $action = new CheckConflictingApplicationsAction;
    $conflicts = $action->execute([
        'event_type' => 'competition',
        'sport_id' => $sport->id,
        'start_date' => now()->addDays(20),
        'end_date' => now()->addDays(22),
    ]);

    expect($conflicts)->toBeEmpty();
});

test('filters by sport id', function () {
    $sport1 = Sport::factory()->create();
    $sport2 = Sport::factory()->create();

    EventApplication::factory()->create([
        'event_type' => 'competition',
        'sport_id' => $sport1->id,
        'start_date' => now()->addDays(10),
        'end_date' => now()->addDays(12),
    ]);

    $action = new CheckConflictingApplicationsAction;
    $conflicts = $action->execute([
        'event_type' => 'competition',
        'sport_id' => $sport2->id,
        'start_date' => now()->addDays(11),
        'end_date' => now()->addDays(13),
    ]);

    expect($conflicts)->toBeEmpty();
});
