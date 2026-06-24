<?php

use App\Enums\EvtEventEnrollmentRoleEnum;
use App\Enums\EvtEventFeeTypeEnum;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentType;
use Domain\EvtEvents\Actions\CreateEnrollmentPaymentDocumentAction;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

beforeEach(function () {
    DocumentType::factory()->create(['code' => 'ORD', 'name' => 'Order']);
});

test('creates correct document details for multiple users with different discipline counts', function () {
    $event = Event::factory()->create();
    $competition = Competition::factory()->create(['event_id' => $event->id]);

    $discipline1 = Discipline::factory()->create();
    $discipline2 = Discipline::factory()->create();
    $discipline3 = Discipline::factory()->create();

    $competition->disciplines()->attach([$discipline1->id, $discipline2->id, $discipline3->id]);

    $perPersonPricing = Pricing::factory()->create([
        'event_id' => $event->id,
        'price_type' => EvtEventFeeTypeEnum::PER_PERSON->value,
        'price' => 10.00,
        'description' => 'Per Person Fee',
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

    AthleteEnrollment::factory()->create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $event->id,
        'federation_id' => $federation->id,
        'individual_id' => $individual1->id,
        'discipline_id' => $discipline1->id,
    ]);
    AthleteEnrollment::factory()->create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $event->id,
        'federation_id' => $federation->id,
        'individual_id' => $individual1->id,
        'discipline_id' => $discipline2->id,
    ]);
    AthleteEnrollment::factory()->create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $event->id,
        'federation_id' => $federation->id,
        'individual_id' => $individual1->id,
        'discipline_id' => $discipline3->id,
    ]);
    AthleteEnrollment::factory()->create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $event->id,
        'federation_id' => $federation->id,
        'individual_id' => $individual2->id,
        'discipline_id' => $discipline1->id,
    ]);

    $selectedIndividuals = [
        [
            'id' => $individual1->id,
            'individual_id' => $individual1->id,
            'role' => EvtEventEnrollmentRoleEnum::ATHLETE->value,
            'pricing_id' => $perPersonPricing->id,
        ],
        [
            'id' => $individual2->id,
            'individual_id' => $individual2->id,
            'role' => EvtEventEnrollmentRoleEnum::ATHLETE->value,
            'pricing_id' => $perPersonPricing->id,
        ],
    ];

    $expectedTotalCost = 2 * 10.00;

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
    assertDatabaseHas('document', ['id' => $document->id]);

    $document->load('details');
    expect($document->details)->toHaveCount(1);

    $perPersonDetail = $document->details->first();
    expect($perPersonDetail->description)->toContain('Per Person Fee');
    expect($perPersonDetail->quantity)->toBe(2);
    expect((float) $perPersonDetail->unit_value)->toBe(10.00);
    expect((float) $perPersonDetail->total_value)->toBe(20.00);

    expect((float) $document->total_value)->toBe($expectedTotalCost);
});

test('creates document with per person fee for single individual', function () {
    $event = Event::factory()->create();

    $perPersonPricing = Pricing::factory()->create([
        'event_id' => $event->id,
        'price_type' => EvtEventFeeTypeEnum::PER_PERSON->value,
        'price' => 25.00,
        'description' => 'Registration Fee',
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
        25.00,
        null
    );

    expect($document)->toBeInstanceOf(Document::class);

    $document->load('details');
    $perPersonDetail = $document->details->first();

    expect($perPersonDetail->quantity)->toBe(1);
    expect((float) $perPersonDetail->unit_value)->toBe(25.00);
});
