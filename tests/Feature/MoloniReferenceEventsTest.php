<?php

use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Event;

it('can create an event with moloni_reference', function () {
    $event = Event::factory()->create([
        'moloni_reference' => 'EVT-REF-001',
    ]);

    expect($event->moloni_reference)->toBe('EVT-REF-001');
    $this->assertDatabaseHas('evt_events', [
        'id' => $event->id,
        'moloni_reference' => 'EVT-REF-001',
    ]);
});

it('can update an event with moloni_reference', function () {
    $event = Event::factory()->create([
        'moloni_reference' => null,
    ]);

    $event->update(['moloni_reference' => 'EVT-REF-002']);

    expect($event->fresh()->moloni_reference)->toBe('EVT-REF-002');
});

it('can create a competition with moloni_reference', function () {
    $competition = Competition::factory()->create([
        'moloni_reference' => 'COMP-REF-001',
    ]);

    expect($competition->moloni_reference)->toBe('COMP-REF-001');
    $this->assertDatabaseHas('evt_competitions', [
        'id' => $competition->id,
        'moloni_reference' => 'COMP-REF-001',
    ]);
});

it('can update a competition with moloni_reference', function () {
    $competition = Competition::factory()->create([
        'moloni_reference' => null,
    ]);

    $competition->update(['moloni_reference' => 'COMP-REF-002']);

    expect($competition->fresh()->moloni_reference)->toBe('COMP-REF-002');
});

it('allows null moloni_reference on events', function () {
    $event = Event::factory()->create([
        'moloni_reference' => null,
    ]);

    expect($event->moloni_reference)->toBeNull();
});

it('allows null moloni_reference on competitions', function () {
    $competition = Competition::factory()->create([
        'moloni_reference' => null,
    ]);

    expect($competition->moloni_reference)->toBeNull();
});
