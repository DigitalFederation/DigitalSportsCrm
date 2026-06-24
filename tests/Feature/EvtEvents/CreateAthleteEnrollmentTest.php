<?php

use App\Services\EnrollmentEligibilityService;
use Domain\EvtEvents\Actions\CreateAthleteEnrollmentAction;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create required entities
    $this->federation = Federation::factory()->create();
    $this->event = Event::factory()->create([
        'start_registration' => Carbon::now()->subDay(),
        'end_registration' => Carbon::now()->addWeek(),
        'start_date' => Carbon::now()->addWeeks(2),
        'end_date' => Carbon::now()->addWeeks(3),
        'event_category' => 'competition', // Ensure it's a sport event
        'allow_individual_enrollment' => true,
    ]);
    $this->discipline = Discipline::factory()->create();
    $this->individual = Individual::factory()->create();

    $this->perPersonPricing = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'price_type' => 'PER_PERSON',
        'price' => 100.00,
        'is_active' => true,
    ]);

    $this->disciplinePricing = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'discipline_id' => $this->discipline->id,
        'price_type' => 'PER_DISCIPLINE',
        'price' => 50.00,
        'is_active' => true,
    ]);

    $this->eventFeePricing = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'price_type' => 'EVENT_FEE',
        'price' => 25.00,
        'is_active' => true,
    ]);

    $this->enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);
});

it('successfully creates athlete enrollment with all pricing types', function () {
    $action = new CreateAthleteEnrollmentAction;
    // Mock the EnrollmentEligibilityService
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
        $this->eventFeePricing->id,
        $this->discipline->id,
        []  // Empty array for attributeValues
    );

    $this->assertDatabaseHas('evt_athletes_enrollment', [
        'individual_id' => $this->individual->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'discipline_id' => $this->discipline->id,
        'per_person_pricing_id' => $this->perPersonPricing->id,
        'per_person_price' => 100.00,
        'discipline_pricing_id' => $this->disciplinePricing->id,
        'discipline_price' => 50.00,
        'event_fee_pricing_id' => $this->eventFeePricing->id,
        'event_fee' => 25.00,
        'total_price' => 175.00,  // 100 + 50 + 25
    ]);
});

it('rejects athlete enrollment when paid per-person pricing exists but no pricing option is selected', function () {
    $action = new CreateAthleteEnrollmentAction;

    $this->mock(EnrollmentEligibilityService::class, function ($mock) {
        $mock->shouldReceive('canEnrollInEvent')->andReturn(true);
    });

    expect(fn () => $action->execute(
        $this->event,
        $this->federation,
        $this->individual->id,
        $this->enrollment,
        null,
        null,
        null,
        $this->discipline->id,
        []
    ))->toThrow(ValidationException::class);

    $this->assertDatabaseMissing('evt_athletes_enrollment', [
        'individual_id' => $this->individual->id,
        'event_id' => $this->event->id,
    ]);
});
