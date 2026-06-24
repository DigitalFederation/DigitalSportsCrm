<?php

use App\Services\EnrollmentEligibilityService;
use Domain\EvtEvents\Actions\CreateAthleteEnrollmentAction;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->federation = Federation::factory()->create();
    $this->event = Event::factory()->create([
        'start_registration' => now()->subDay(),
        'end_registration' => now()->addDay(),
    ]);
    $this->disciplineWithPrice = Discipline::factory()->create();
    $this->disciplineWithoutPrice = Discipline::factory()->create();
    $this->individual = Individual::factory()->create();

    $this->perPersonPricing = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'price_type' => 'PER_PERSON',
        'price' => 50.00,
        'is_active' => true,
    ]);

    $this->disciplinePricing = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'discipline_id' => $this->disciplineWithPrice->id,
        'price_type' => 'PER_DISCIPLINE',
        'price' => 25.00,
        'is_active' => true,
    ]);

    $this->enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->individual->id,
        'enrollable_type' => Individual::class,
    ]);
});

it('applies PER_PERSON price when enrolling an individual', function () {
    $action = new CreateAthleteEnrollmentAction;
    $this->mock(EnrollmentEligibilityService::class, function ($mock) {
        $mock->shouldReceive('canEnrollInEvent')->andReturn(true);
    });

    $athleteEnrollment = $action->execute(
        $this->event,
        $this->federation,
        $this->individual->id,
        $this->enrollment,
        $this->perPersonPricing->id,
        $this->disciplinePricing->id,
        null,
        $this->disciplineWithPrice->id,
        []
    );

    expect($athleteEnrollment)->toBeInstanceOf(AthleteEnrollment::class)
        ->and($athleteEnrollment->per_person_pricing_id)->toBe($this->perPersonPricing->id)
        ->and($athleteEnrollment->per_person_price)->toBe(50.00)
        ->and($athleteEnrollment->discipline_pricing_id)->toBe($this->disciplinePricing->id)
        ->and($athleteEnrollment->discipline_price)->toBe(25.00)
        ->and($athleteEnrollment->total_price)->toBe(75.00);
});

it('does not apply PER_PERSON price when it does not exist', function () {
    $this->perPersonPricing->delete();

    $action = new CreateAthleteEnrollmentAction;
    $this->mock(EnrollmentEligibilityService::class, function ($mock) {
        $mock->shouldReceive('canEnrollInEvent')->andReturn(true);
    });

    $athleteEnrollment = $action->execute(
        $this->event,
        $this->federation,
        $this->individual->id,
        $this->enrollment,
        null,
        $this->disciplinePricing->id,
        null,
        $this->disciplineWithPrice->id,
        []
    );

    expect($athleteEnrollment)->toBeInstanceOf(AthleteEnrollment::class)
        ->and($athleteEnrollment->per_person_pricing_id)->toBeNull()
        ->and($athleteEnrollment->per_person_price)->toBe(0.00)
        ->and($athleteEnrollment->discipline_pricing_id)->toBe($this->disciplinePricing->id)
        ->and($athleteEnrollment->discipline_price)->toBe(25.00)
        ->and($athleteEnrollment->total_price)->toBe(25.00);
});

it('applies PER_PERSON price when enrolling in a discipline without price', function () {
    $action = new CreateAthleteEnrollmentAction;
    $this->mock(EnrollmentEligibilityService::class, function ($mock) {
        $mock->shouldReceive('canEnrollInEvent')->andReturn(true);
    });

    $athleteEnrollment = $action->execute(
        $this->event,
        $this->federation,
        $this->individual->id,
        $this->enrollment,
        $this->perPersonPricing->id,
        null,
        null,
        $this->disciplineWithoutPrice->id,
        []
    );

    expect($athleteEnrollment)->toBeInstanceOf(AthleteEnrollment::class)
        ->and($athleteEnrollment->per_person_pricing_id)->toBe($this->perPersonPricing->id)
        ->and($athleteEnrollment->per_person_price)->toBe(50.00)
        ->and($athleteEnrollment->discipline_pricing_id)->toBeNull()
        ->and($athleteEnrollment->discipline_price)->toBe(0.00)
        ->and($athleteEnrollment->total_price)->toBe(50.00);
});
