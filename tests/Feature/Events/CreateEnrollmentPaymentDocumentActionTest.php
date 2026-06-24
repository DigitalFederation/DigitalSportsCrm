<?php

use App\Enums\EvtEventEnrollmentRoleEnum;
use App\Enums\EvtEventFeeTypeEnum;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentType;
use Domain\EvtEvents\Actions\CreateEnrollmentPaymentDocumentAction;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    DocumentType::factory()->create([
        'name' => 'Order',
        'code' => 'ORD',
    ]);
});

test('handles flat fees correctly', function () {
    $event = Event::factory()->create();

    $flatFeePricing = Pricing::factory()->create([
        'event_id' => $event->id,
        'price_type' => EvtEventFeeTypeEnum::FLAT_FEE->value,
        'price' => 50.00,
        'description' => 'Flat Fee',
        'is_active' => true,
    ]);

    $eventFee = Pricing::factory()->create([
        'event_id' => $event->id,
        'price_type' => EvtEventFeeTypeEnum::EVENT_FEE->value,
        'price' => 15.00,
        'description' => 'Event Fee',
        'is_active' => true,
    ]);

    $individual1 = Individual::factory()->create();
    $individual2 = Individual::factory()->create();

    $federation = Federation::factory()->create();
    $federation->individuals()->attach([$individual1->id, $individual2->id]);

    $enrollment = Enrollment::factory()->create([
        'event_id' => $event->id,
        'enrollable_id' => $federation->id,
        'enrollable_type' => Federation::class,
    ]);

    $selectedIndividuals = [
        [
            'id' => $individual1->id,
            'individual_id' => $individual1->id,
            'role' => EvtEventEnrollmentRoleEnum::ATHLETE->value,
            'pricing_id' => $flatFeePricing->id,
        ],
        [
            'id' => $individual2->id,
            'individual_id' => $individual2->id,
            'role' => EvtEventEnrollmentRoleEnum::ATHLETE->value,
            'pricing_id' => $flatFeePricing->id,
        ],
    ];

    $expectedTotalCost = 50.00 + 15.00;

    $action = new CreateEnrollmentPaymentDocumentAction;
    $document = $action->execute(
        $event,
        $enrollment,
        $federation->id,
        Federation::class,
        $selectedIndividuals,
        $expectedTotalCost,
        null
    );

    expect($document)->toBeInstanceOf(Document::class);

    $flatFeeDetail = $document->details->first(function ($detail) {
        return str_contains(strtolower($detail->description), 'flat fee');
    });

    expect($flatFeeDetail)->not->toBeNull();
    expect($flatFeeDetail->quantity)->toBe(1);
    expect((float) $flatFeeDetail->unit_value)->toBe(50.00);

    expect((float) $document->total_value)->toBe($expectedTotalCost);
});

test('does not duplicate per-person fees for individuals enrolled in multiple disciplines', function () {
    $event = Event::factory()->create();

    $perPersonPricing = Pricing::factory()->create([
        'event_id' => $event->id,
        'price_type' => EvtEventFeeTypeEnum::PER_PERSON->value,
        'price' => 10.00,
        'description' => 'Per Person Fee',
        'is_active' => true,
    ]);

    $discipline1 = Discipline::factory()->create();
    $discipline2 = Discipline::factory()->create();

    $individual = Individual::factory()->create();
    $federation = Federation::factory()->create();
    $federation->individuals()->attach([$individual->id]);

    $enrollment = Enrollment::factory()->create([
        'event_id' => $event->id,
        'enrollable_id' => $federation->id,
        'enrollable_type' => Federation::class,
    ]);

    $selectedIndividuals = [
        [
            'id' => $individual->id,
            'individual_id' => $individual->id,
            'role' => EvtEventEnrollmentRoleEnum::ATHLETE->value,
            'pricing_id' => $perPersonPricing->id,
            'discipline_id' => $discipline1->id,
            'discipline_price' => null,
        ],
        [
            'id' => $individual->id,
            'individual_id' => $individual->id,
            'role' => EvtEventEnrollmentRoleEnum::ATHLETE->value,
            'pricing_id' => $perPersonPricing->id,
            'discipline_id' => $discipline2->id,
            'discipline_price' => null,
        ],
    ];

    $expectedTotalCost = 10.00;

    $action = new CreateEnrollmentPaymentDocumentAction;
    $document = $action->execute(
        $event,
        $enrollment,
        $federation->id,
        Federation::class,
        $selectedIndividuals,
        $expectedTotalCost,
        null
    );

    expect($document)->toBeInstanceOf(Document::class);
    expect($document->details)->toHaveCount(1);

    $perPersonDetail = $document->details->first(function ($detail) {
        return str_contains(strtolower($detail->description), 'per person fee');
    });

    expect($perPersonDetail)->not->toBeNull();
    expect($perPersonDetail->quantity)->toBe(1);
    expect((float) $perPersonDetail->unit_value)->toBe(10.00);

    expect((float) $document->total_value)->toBe($expectedTotalCost);
});

test('does not duplicate per-person fees and also adds discipline prices', function () {
    $event = Event::factory()->create();

    $perPersonPricing = Pricing::factory()->create([
        'event_id' => $event->id,
        'price_type' => EvtEventFeeTypeEnum::PER_PERSON->value,
        'price' => 10.00,
        'description' => 'Per Person Fee',
        'is_active' => true,
    ]);

    $discipline1 = Discipline::factory()->create();
    $discipline2 = Discipline::factory()->create();

    $individual = Individual::factory()->create();
    $federation = Federation::factory()->create();
    $federation->individuals()->attach([$individual->id]);

    $enrollment = Enrollment::factory()->create([
        'event_id' => $event->id,
        'enrollable_id' => $federation->id,
        'enrollable_type' => Federation::class,
    ]);

    $selectedIndividuals = [
        [
            'id' => $individual->id,
            'individual_id' => $individual->id,
            'role' => EvtEventEnrollmentRoleEnum::ATHLETE->value,
            'pricing_id' => $perPersonPricing->id,
            'discipline_id' => $discipline1->id,
            'discipline_price' => 5.00,
        ],
        [
            'id' => $individual->id,
            'individual_id' => $individual->id,
            'role' => EvtEventEnrollmentRoleEnum::ATHLETE->value,
            'pricing_id' => $perPersonPricing->id,
            'discipline_id' => $discipline2->id,
            'discipline_price' => 5.00,
        ],
    ];

    $expectedTotalCost = 20.00;

    $action = new CreateEnrollmentPaymentDocumentAction;
    $document = $action->execute(
        $event,
        $enrollment,
        $federation->id,
        Federation::class,
        $selectedIndividuals,
        $expectedTotalCost,
        null
    );

    expect($document)->toBeInstanceOf(Document::class);
    expect($document->details)->toHaveCount(3);

    $perPersonDetail = $document->details->first(function ($detail) {
        return str_contains(strtolower($detail->description), 'per person fee');
    });

    expect($perPersonDetail)->not->toBeNull();
    expect($perPersonDetail->quantity)->toBe(1);
    expect((float) $perPersonDetail->unit_value)->toBe(10.00);

    expect((float) $document->total_value)->toBe($expectedTotalCost);
});

test('creates document with event fee when event has one', function () {
    $event = Event::factory()->create(['name' => 'Test Championship']);

    $eventFee = Pricing::factory()->create([
        'event_id' => $event->id,
        'price_type' => EvtEventFeeTypeEnum::EVENT_FEE->value,
        'price' => 100.00,
        'description' => 'Championship Entry',
        'is_active' => true,
    ]);

    $perPersonPricing = Pricing::factory()->create([
        'event_id' => $event->id,
        'price_type' => EvtEventFeeTypeEnum::PER_PERSON->value,
        'price' => 25.00,
        'description' => 'Per Person',
        'is_active' => true,
    ]);

    $individual = Individual::factory()->create();
    $federation = Federation::factory()->create();
    $federation->individuals()->attach([$individual->id]);

    $enrollment = Enrollment::factory()->create([
        'event_id' => $event->id,
        'enrollable_id' => $federation->id,
        'enrollable_type' => Federation::class,
    ]);

    $selectedIndividuals = [
        [
            'id' => $individual->id,
            'individual_id' => $individual->id,
            'role' => EvtEventEnrollmentRoleEnum::ATHLETE->value,
            'pricing_id' => $perPersonPricing->id,
        ],
    ];

    $expectedTotalCost = 125.00;

    $action = new CreateEnrollmentPaymentDocumentAction;
    $document = $action->execute(
        $event,
        $enrollment,
        $federation->id,
        Federation::class,
        $selectedIndividuals,
        $expectedTotalCost,
        null
    );

    expect($document)->toBeInstanceOf(Document::class);

    $eventFeeDetail = $document->details->first(function ($detail) {
        return str_contains(strtolower($detail->description), 'event fee');
    });

    expect($eventFeeDetail)->not->toBeNull();
    expect((float) $eventFeeDetail->unit_value)->toBe(100.00);
    expect($eventFeeDetail->quantity)->toBe(1);
});

test('document owner is set correctly to enrollable entity', function () {
    $event = Event::factory()->create();

    $perPersonPricing = Pricing::factory()->create([
        'event_id' => $event->id,
        'price_type' => EvtEventFeeTypeEnum::PER_PERSON->value,
        'price' => 10.00,
        'description' => 'Per Person Fee',
        'is_active' => true,
    ]);

    $individual = Individual::factory()->create();
    $federation = Federation::factory()->create();
    $federation->individuals()->attach([$individual->id]);

    $enrollment = Enrollment::factory()->create([
        'event_id' => $event->id,
        'enrollable_id' => $federation->id,
        'enrollable_type' => Federation::class,
    ]);

    $selectedIndividuals = [
        [
            'id' => $individual->id,
            'individual_id' => $individual->id,
            'role' => EvtEventEnrollmentRoleEnum::ATHLETE->value,
            'pricing_id' => $perPersonPricing->id,
        ],
    ];

    $action = new CreateEnrollmentPaymentDocumentAction;
    $document = $action->execute(
        $event,
        $enrollment,
        $federation->id,
        Federation::class,
        $selectedIndividuals,
        10.00,
        null
    );

    expect($document->owner_type)->toBe('federation');
    expect($document->owner_id)->toBe((string) $federation->id);
});
