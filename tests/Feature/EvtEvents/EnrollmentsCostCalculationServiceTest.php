<?php

use App\Enums\EvtDisciplineEnrollmentTypeEnum;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\EvtEvents\Services\EnrollmentsCostCalculationService;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create required entities
    $this->federation = Federation::factory()->create();
    $this->event = Event::factory()->create();
    $this->disciplineIndividual = Discipline::factory()->create(['enrollment_type' => EvtDisciplineEnrollmentTypeEnum::individual]);
    $this->disciplineTeam = Discipline::factory()->create(['enrollment_type' => EvtDisciplineEnrollmentTypeEnum::team]);
    $this->disciplineRelay = Discipline::factory()->create(['enrollment_type' => EvtDisciplineEnrollmentTypeEnum::relay]);
    $this->individual1 = Individual::factory()->create();
    $this->individual2 = Individual::factory()->create();
    $this->individual3 = Individual::factory()->create();

    // Create pricing models
    $this->pricingPerPerson = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'price_type' => 'PER_PERSON',
        'price' => 10.00,
        'is_active' => true,
    ]);

    $this->pricingDisciplineIndividual = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'discipline_id' => $this->disciplineIndividual->id,
        'price_type' => 'PER_DISCIPLINE',
        'price' => 7.00,
        'is_active' => true,
    ]);

    $this->pricingDisciplineTeam = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'discipline_id' => $this->disciplineTeam->id,
        'price_type' => 'PER_DISCIPLINE',
        'price' => 20.00,
        'is_active' => true,
    ]);

    $this->pricingDisciplineRelay = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'discipline_id' => $this->disciplineRelay->id,
        'price_type' => 'PER_DISCIPLINE',
        'price' => 30.00,
        'is_active' => true,
    ]);

    $this->enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);
});

it('calculates total cost for individual discipline correctly', function () {
    AthleteEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'individual_id' => $this->individual1->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'discipline_id' => $this->disciplineIndividual->id,
        'per_person_pricing_id' => $this->pricingPerPerson->id,
        'per_person_price' => 10.00,
        'discipline_pricing_id' => $this->pricingDisciplineIndividual->id,
        'discipline_price' => 7.00,
        'total_price' => 17.00,
    ]);

    $service = new EnrollmentsCostCalculationService;
    $totalCost = $service->calculateTotalCost($this->event, EloquentCollection::make([$this->enrollment]));

    // Expected cost: PER_PERSON (10) + PER_DISCIPLINE (7)
    expect($totalCost)->toBe(17.00);
});

it('calculates total cost for team discipline as flat fee', function () {
    AthleteEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'individual_id' => $this->individual1->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'discipline_id' => $this->disciplineTeam->id,
        'per_person_pricing_id' => $this->pricingPerPerson->id,
        'per_person_price' => 10.00,
        'discipline_pricing_id' => $this->pricingDisciplineTeam->id,
        'discipline_price' => 20.00,
        'total_price' => 30.00,
    ]);

    AthleteEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'individual_id' => $this->individual2->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'discipline_id' => $this->disciplineTeam->id,
        'per_person_pricing_id' => $this->pricingPerPerson->id,
        'per_person_price' => 10.00,
        'discipline_pricing_id' => $this->pricingDisciplineTeam->id,
        'discipline_price' => 20.00,
        'total_price' => 30.00,
    ]);

    $service = new EnrollmentsCostCalculationService;
    $processedDisciplines = [];
    $totalCost = $service->calculateTotalCost($this->event, EloquentCollection::make([$this->enrollment]), false);

    // Expected cost: 2 x PER_PERSON (10 + 10) + PER_DISCIPLINE as flat fee (20)
    expect($totalCost)->toBe(60.00);
});

it('calculates total cost for relay discipline as flat fee', function () {
    AthleteEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'individual_id' => $this->individual1->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'discipline_id' => $this->disciplineRelay->id,
        'per_person_pricing_id' => $this->pricingPerPerson->id,
        'per_person_price' => 10.00,
        'discipline_pricing_id' => $this->pricingDisciplineRelay->id,
        'discipline_price' => 30.00,
        'total_price' => 40.00,
    ]);

    AthleteEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'individual_id' => $this->individual2->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'discipline_id' => $this->disciplineRelay->id,
        'per_person_pricing_id' => $this->pricingPerPerson->id,
        'per_person_price' => 10.00,
        'discipline_pricing_id' => $this->pricingDisciplineRelay->id,
        'discipline_price' => 30.00,
        'total_price' => 40.00,
    ]);

    $service = new EnrollmentsCostCalculationService;
    $totalCost = $service->calculateTotalCost($this->event, EloquentCollection::make([$this->enrollment]));

    // Expected cost: 2 x PER_PERSON (10 + 10) + PER_DISCIPLINE as flat fee (30)
    expect($totalCost)->toBe(80.00);
});

it('calculates total cost correctly for an individual enrolled in multiple disciplines', function () {
    // Enroll individual1 in two different disciplines
    AthleteEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'individual_id' => $this->individual1->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'discipline_id' => $this->disciplineIndividual->id,
        'per_person_pricing_id' => $this->pricingPerPerson->id,
        'per_person_price' => 10.00,
        'discipline_pricing_id' => $this->pricingDisciplineIndividual->id,
        'discipline_price' => 7.00,
        'total_price' => 17.00,
    ]);

    AthleteEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'individual_id' => $this->individual1->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'discipline_id' => $this->disciplineTeam->id,
        'per_person_pricing_id' => $this->pricingPerPerson->id,
        'per_person_price' => 10.00,
        'discipline_pricing_id' => $this->pricingDisciplineTeam->id,
        'discipline_price' => 20.00,
        'total_price' => 30.00,
    ]);

    // Enroll individual2 in one discipline
    AthleteEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'individual_id' => $this->individual2->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'discipline_id' => $this->disciplineIndividual->id,
        'per_person_pricing_id' => $this->pricingPerPerson->id,
        'per_person_price' => 10.00,
        'discipline_pricing_id' => $this->pricingDisciplineIndividual->id,
        'discipline_price' => 7.00,
        'total_price' => 17.00,
    ]);

    $service = new EnrollmentsCostCalculationService;
    $totalCost = $service->calculateTotalCost($this->event, EloquentCollection::make([$this->enrollment]));

    // Expected cost:
    // 2 x PER_PERSON (10 + 10) [for individual1 and individual2]
    // + 2 x PER_DISCIPLINE for individual1 (7 + 20)
    // + 1 x PER_DISCIPLINE for individual2 (7)
    // Total: 20 + 27 + 7 = 54
    expect($totalCost)->toBe(54.00);
});
