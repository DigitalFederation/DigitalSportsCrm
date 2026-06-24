<?php

use Domain\EvtEvents\Actions\CalculateOrganizationalEventEnrollmentFeeAction;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;

beforeEach(function () {
    // Setup common preconditions for tests
    $this->event = Event::factory()->create();
    $this->action = new CalculateOrganizationalEventEnrollmentFeeAction;
});

it('calculates total fee for organizational event with active pricing', function () {
    $flatFee = 100.0;
    $numberOfEnrollees = 5;

    Pricing::factory()->create([
        'event_id' => $this->event->id,
        'price_type' => 'flat_fee',
        'price' => $flatFee,
        'is_active' => true,
    ]);

    $totalFee = $this->action->execute($this->event->id, $numberOfEnrollees);

    expect($totalFee)->toEqual($flatFee * $numberOfEnrollees);
});

it('throws an exception for organizational event with no active pricing', function () {
    $this->action->execute($this->event->id, 3);
})->throws(Exception::class);

it('throws an exception for organizational event with inactive pricing', function () {
    Pricing::factory()->create([
        'event_id' => $this->event->id,
        'price_type' => 'flat_fee',
        'is_active' => false,
    ]);

    $this->action->execute($this->event->id, 3);
})->throws(Exception::class);
