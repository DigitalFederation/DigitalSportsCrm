<?php

use Domain\Documents\Models\DocumentType;
use Domain\EvtEvents\Actions\CreateEnrollmentPaymentDocumentAction;
use Domain\EvtEvents\Actions\GetWaitingListSelectedIndividualsAction;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\EvtEvents\Services\EnrollmentsCostCalculationService;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    DocumentType::factory()->create([
        'code' => 'ORD',
    ]);
    // Create required entities
    $this->federation = Federation::factory()->create();
    $this->event = Event::factory()->create();
    $this->discipline1 = Discipline::factory()->create();
    $this->discipline2 = Discipline::factory()->create();
    $this->discipline3 = Discipline::factory()->create();
    $this->individual1 = Individual::factory()->create();
    $this->individual2 = Individual::factory()->create();
    $this->individual3 = Individual::factory()->create();

    $this->pricingPerPerson = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'price_type' => 'PER_PERSON',
        'price' => 10.00,
        'is_active' => true,
    ]);

    $this->disciplinePricing1 = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'discipline_id' => $this->discipline1->id,
        'price_type' => 'PER_DISCIPLINE',
        'price' => 7.00,
        'is_active' => true,
        'description' => '100M MEN',
        'enrollment_role' => 'ATHLETE',
    ]);

    $this->disciplinePricing2 = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'discipline_id' => $this->discipline2->id,
        'price_type' => 'PER_DISCIPLINE',
        'price' => 5.00,
        'is_active' => true,
        'description' => '100M WOMEN',
        'enrollment_role' => 'ATHLETE',
    ]);

    $this->disciplinePricing3 = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'discipline_id' => $this->discipline3->id,
        'price_type' => 'PER_DISCIPLINE',
        'price' => 9.00,
        'is_active' => true,
        'description' => '2X50M MEN',
        'enrollment_role' => 'ATHLETE',
    ]);

    $this->eventFee = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'price_type' => 'EVENT_FEE',
        'price' => 1000.00,
        'is_active' => true,
        'description' => 'Event Fee',
        'enrollment_role' => 'ATHLETE',
    ]);

    $this->enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->federation->id,
        'enrollable_type' => Federation::class,
    ]);
});

it('correctly calculates total cost and creates payment document for multiple disciplines', function () {
    // Create enrollments
    $athleteEnrollment1 = AthleteEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'individual_id' => $this->individual1->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'discipline_id' => $this->discipline1->id,
        'per_person_pricing_id' => $this->pricingPerPerson->id,
        'per_person_price' => 10.00,
        'event_fee_pricing_id' => $this->eventFee->id,
        'event_fee' => 1000.00,
        'discipline_pricing_id' => $this->disciplinePricing1->id,
        'discipline_price' => 7.00,
    ]);

    $athleteEnrollment2 = AthleteEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'individual_id' => $this->individual2->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'discipline_id' => $this->discipline2->id,
        'per_person_pricing_id' => $this->pricingPerPerson->id,
        'per_person_price' => 10.00,
        'event_fee_pricing_id' => $this->eventFee->id,
        'event_fee' => 1000.00,
        'discipline_pricing_id' => $this->disciplinePricing2->id,
        'discipline_price' => 5.00,
    ]);

    $athleteEnrollment3 = AthleteEnrollment::factory()->create([
        'enrollment_id' => $this->enrollment->id,
        'individual_id' => $this->individual3->id,
        'event_id' => $this->event->id,
        'federation_id' => $this->federation->id,
        'discipline_id' => $this->discipline3->id,
        'per_person_pricing_id' => $this->pricingPerPerson->id,
        'per_person_price' => 10.00,
        'event_fee_pricing_id' => $this->eventFee->id,
        'event_fee' => 1000.00,
        'discipline_pricing_id' => $this->disciplinePricing3->id,
        'discipline_price' => 9.00,
    ]);

    $federationId = $this->federation->id;
    // Fetch pending enrollments
    $pendingEnrollments = Enrollment::where('event_id', $this->event->id)
        ->where('enrollable_type', Federation::class)
        ->where('enrollable_id', $this->federation->id)
        ->where(function ($query) use ($federationId) {
            $query->whereHas('athleteEnrollments', function ($subQuery) use ($federationId) {
                $subQuery->where('federation_id', $federationId);
            });
        })
        ->whereNull('activated_at')
        ->with(
            'event',
            'individualEnrollments',
            'athleteEnrollments.individual',
            'coachEnrollments.individual',
            'refereeEnrollments.individual',
            'teamOfficialEnrollments.individual'
        )
        ->get();

    // Prepare selected individuals using the new action
    $getSelectedIndividualsAction = new GetWaitingListSelectedIndividualsAction;
    $selectedIndividuals = $getSelectedIndividualsAction->execute($pendingEnrollments);

    // Calculate total cost
    $costCalculationService = new EnrollmentsCostCalculationService;
    $totalCost = $costCalculationService->calculateTotalCost($this->event, $pendingEnrollments);

    // Correct total cost calculation: Event Fee + (3 x 10) + 7 + 5 + 9
    $expectedTotalCost = 1000.00 + (3 * 10.00) + 7.00 + 5.00 + 9.00;
    expect($totalCost)->toBe($expectedTotalCost);

    // Create payment document
    $documentAction = new CreateEnrollmentPaymentDocumentAction;
    $document = $documentAction->execute(
        $this->event,
        $this->enrollment,
        (string) $this->federation->id,
        Federation::class,
        $selectedIndividuals,
        $totalCost,
        null
    );

    $this->assertDatabaseHas('document', [
        'owner_type' => 'federation',
        'owner_id' => $this->federation->id,
    ]);

    $this->assertDatabaseHas('document_detail', [
        'unit_value' => 1000.00,
        'quantity' => 1,
    ]);

    $this->assertDatabaseHas('document_detail', [
        'unit_value' => 10.00,
        'quantity' => 3,
    ]);

    $this->assertDatabaseHas('document_detail', [
        'unit_value' => 7.00,
        'quantity' => 1,
    ]);

    $this->assertDatabaseHas('document_detail', [
        'unit_value' => 5.00,
        'quantity' => 1,
    ]);

    $this->assertDatabaseHas('document_detail', [
        'unit_value' => 9.00,
        'quantity' => 1,
    ]);

    // Verify athlete enrollments
    $this->assertDatabaseHas('evt_athletes_enrollment', [
        'individual_id' => $this->individual1->id,
        'per_person_price' => 10.00,
        'per_person_pricing_id' => $this->pricingPerPerson->id,
        'discipline_price' => 7.00,
        'discipline_pricing_id' => $this->disciplinePricing1->id,
    ]);

    $this->assertDatabaseHas('evt_athletes_enrollment', [
        'individual_id' => $this->individual2->id,
        'per_person_price' => 10.00,
        'per_person_pricing_id' => $this->pricingPerPerson->id,
        'discipline_price' => 5.00,
        'discipline_pricing_id' => $this->disciplinePricing2->id,
    ]);

    $this->assertDatabaseHas('evt_athletes_enrollment', [
        'individual_id' => $this->individual3->id,
        'per_person_price' => 10.00,
        'per_person_pricing_id' => $this->pricingPerPerson->id,
        'discipline_price' => 9.00,
        'discipline_pricing_id' => $this->disciplinePricing3->id,
    ]);
});
