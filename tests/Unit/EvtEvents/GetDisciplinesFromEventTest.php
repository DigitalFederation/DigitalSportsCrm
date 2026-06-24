<?php

use Domain\EvtEvents\Actions\GetDisciplinesFromEventAction;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\DisciplineTemplate;
use Domain\EvtEvents\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns expected structure when event has no competition', function () {
    $event = Event::factory()->create();

    $action = new GetDisciplinesFromEventAction;
    $result = $action->execute($event);

    expect($result)->toBeArray()
        ->toHaveKeys(['disciplines', 'has_individual', 'has_relay', 'has_male', 'has_female', 'has_mixed', 'styles', 'distances'])
        ->and($result['disciplines'])->toBeCollection()
        ->toBeEmpty();
});

it('returns disciplines and filter options when competition exists', function () {
    $event = Event::factory()->create();
    $template = DisciplineTemplate::factory()->create();
    $competition = Competition::factory()->create([
        'event_id' => $event->id,
        'discipline_template_id' => $template->id,
    ]);

    $discipline = Discipline::factory()->create([
        'enrollment_type' => 'individual',
        'gender' => 'male',
        'style' => 'freestyle',
        'distance' => '100m',
    ]);

    $template->disciplines()->attach($discipline->id);

    $action = new GetDisciplinesFromEventAction;
    $result = $action->execute($event);

    expect($result)->toBeArray()
        ->and($result['disciplines'])->toHaveCount(1)
        ->and($result['has_individual'])->toBeTrue()
        ->and($result['has_male'])->toBeTrue()
        ->and($result['styles'])->toContain('freestyle');
});
