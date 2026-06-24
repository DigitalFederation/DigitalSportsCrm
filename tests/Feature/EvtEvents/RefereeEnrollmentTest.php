<?php

use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\States\ActiveRefereeEnrollmentState;
use Domain\EvtEvents\States\CanceledRefereeEnrollmentState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->federation = Federation::factory()->create();
    $this->event = Event::factory()->create();
    $this->individual = Individual::factory()->create();

    $this->enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);
});

it('creates referee enrollment successfully', function () {
    $refereeEnrollment = RefereeEnrollment::create([
        'enrollment_id' => $this->enrollment->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
        'event_id' => $this->event->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
        'price' => 0,
        'price_type' => 'free',
    ]);

    expect($refereeEnrollment)->toBeInstanceOf(RefereeEnrollment::class)
        ->and($refereeEnrollment->federation_id)->toBe($this->federation->id)
        ->and($refereeEnrollment->individual_id)->toBe($this->individual->id)
        ->and($refereeEnrollment->event_id)->toBe($this->event->id)
        ->and($refereeEnrollment->status_class)->toBe(ActiveRefereeEnrollmentState::class);
});

it('creates referee enrollment using factory', function () {
    $refereeEnrollment = RefereeEnrollment::factory()
        ->forEvent($this->event)
        ->forFederation($this->federation)
        ->forIndividual($this->individual)
        ->create([
            'enrollment_id' => $this->enrollment->id,
        ]);

    expect($refereeEnrollment)->toBeInstanceOf(RefereeEnrollment::class)
        ->and($refereeEnrollment->federation_id)->toBe($this->federation->id)
        ->and($refereeEnrollment->individual_id)->toBe($this->individual->id)
        ->and($refereeEnrollment->event_id)->toBe($this->event->id);
});

it('loads relationships correctly', function () {
    $refereeEnrollment = RefereeEnrollment::create([
        'enrollment_id' => $this->enrollment->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
        'event_id' => $this->event->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
        'price' => 0,
        'price_type' => 'free',
    ]);

    $loaded = RefereeEnrollment::with(['federation', 'individual', 'event', 'enrollment'])
        ->find($refereeEnrollment->id);

    expect($loaded->federation)->toBeInstanceOf(Federation::class)
        ->and($loaded->individual)->toBeInstanceOf(Individual::class)
        ->and($loaded->event)->toBeInstanceOf(Event::class)
        ->and($loaded->enrollment)->toBeInstanceOf(Enrollment::class);
});

it('transitions state from active to canceled', function () {
    $refereeEnrollment = RefereeEnrollment::create([
        'enrollment_id' => $this->enrollment->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
        'event_id' => $this->event->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
        'price' => 0,
        'price_type' => 'free',
    ]);

    $refereeEnrollment->cancel();

    expect($refereeEnrollment->fresh()->status_class)
        ->toBe(CanceledRefereeEnrollmentState::class);
});

it('transitions state from canceled to active', function () {
    $refereeEnrollment = RefereeEnrollment::create([
        'enrollment_id' => $this->enrollment->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
        'event_id' => $this->event->id,
        'status_class' => CanceledRefereeEnrollmentState::class,
        'price' => 0,
        'price_type' => 'free',
    ]);

    $refereeEnrollment->activate();

    expect($refereeEnrollment->fresh()->status_class)
        ->toBe(ActiveRefereeEnrollmentState::class);
});

it('handles attributes correctly', function () {
    $refereeEnrollment = RefereeEnrollment::create([
        'enrollment_id' => $this->enrollment->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
        'event_id' => $this->event->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
        'price' => 0,
        'price_type' => 'free',
    ]);

    $attribute = \Domain\EvtEvents\Models\Attribute::factory()->create();

    $refereeEnrollment->attributes()->create([
        'attribute_id' => $attribute->id,
        'value' => 'test_value',
    ]);

    $loaded = RefereeEnrollment::with('attributes.attribute')
        ->find($refereeEnrollment->id);

    expect($loaded->attributes)->toHaveCount(1)
        ->and($loaded->attributes->first()->value)->toBe('test_value')
        ->and($loaded->attributes->first()->attribute->id)->toBe($attribute->id);
});

it('returns correct state name and color', function () {
    $refereeEnrollment = RefereeEnrollment::create([
        'enrollment_id' => $this->enrollment->id,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
        'event_id' => $this->event->id,
        'status_class' => ActiveRefereeEnrollmentState::class,
        'price' => 0,
        'price_type' => 'free',
    ]);

    expect($refereeEnrollment->stateName())->toBe(__('events.active'))
        ->and($refereeEnrollment->stateColor())->toBe('bg-green-100 text-green-800');
});
