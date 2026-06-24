<?php

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Livewire\EvtEvents\ReviewAndPay;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\CoachEnrollment;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\EvtEvents\States\PendingCoachEnrollmentState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Livewire\Livewire;

beforeEach(function () {
    $this->federation = Federation::factory()->create();
    $this->entity = Entity::factory()->create();
    $this->individual = Individual::factory()->create();
    $this->discipline = Discipline::factory()->create(['name' => 'Test Discipline']);

    $this->event = Event::factory()->create([
        'status_class' => ActiveEventState::class,
        'event_category' => 'competition',
        'is_visible' => true,
    ]);
});

it('correctly organizes athletes by discipline', function () {
    // Create main enrollment
    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->entity->id,
        'enrollable_type' => Entity::class,
    ]);

    // Create athlete enrollment with discipline (individual discipline)
    AthleteEnrollment::create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'discipline_id' => $this->discipline->id,
        'per_person_price' => 25.00,
        'discipline_price' => 15.00,
        'event_fee' => 10.00,
        'total_price' => 50.00,
        'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
    ]);

    $component = Livewire::test(ReviewAndPay::class, [
        'event' => $this->event,
        'model' => $this->entity,
    ]);

    // Check that enrollments are organized by discipline
    // For individual disciplines: subtotal = per_person_subtotal + discipline_fee
    // per_person: 25.00, discipline_fee: 15.00 = 40.00
    // grandTotal = subtotal + event_fee = 40.00 + 10.00 = 50.00
    expect($component->get('enrollmentsByDiscipline'))
        ->toHaveKey($this->discipline->id)
        ->and($component->get('enrollmentsByDiscipline.' . $this->discipline->id . '.discipline_name'))
        ->toBe('Test Discipline')
        ->and($component->get('enrollmentsByDiscipline.' . $this->discipline->id . '.enrollments'))
        ->toHaveCount(1)
        ->and($component->get('enrollmentsByDiscipline.' . $this->discipline->id . '.subtotal'))
        ->toBe(40.0)
        ->and($component->get('grandTotal'))
        ->toBe(50.0); // 25 per_person + 15 discipline + 10 event_fee
});

it('calculates relay discipline fee only once for multiple athletes', function () {
    // Create a relay discipline
    $relayDiscipline = Discipline::factory()->create([
        'name' => '4x100m Relay',
        'enrollment_type' => 'relay',
    ]);

    // Create main enrollment
    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->entity->id,
        'enrollable_type' => Entity::class,
    ]);

    // Create 4 athletes for the relay
    $individual2 = Individual::factory()->create();
    $individual3 = Individual::factory()->create();
    $individual4 = Individual::factory()->create();

    $athletes = [$this->individual, $individual2, $individual3, $individual4];

    foreach ($athletes as $athlete) {
        AthleteEnrollment::create([
            'enrollment_id' => $enrollment->id,
            'event_id' => $this->event->id,
            'entity_id' => $this->entity->id,
            'individual_id' => $athlete->id,
            'discipline_id' => $relayDiscipline->id,
            'per_person_price' => 10.00,
            'discipline_price' => 4.00, // This should only be counted ONCE for relay
            'event_fee' => 0,
            'total_price' => 14.00,
            'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
        ]);
    }

    $component = Livewire::test(ReviewAndPay::class, [
        'event' => $this->event,
        'model' => $this->entity,
    ]);

    // For relay: 4 athletes x 10 per-person = 40, plus 4 discipline fee (once) = 44
    // NOT: 4 athletes x 14 = 56
    expect($component->get('enrollmentsByDiscipline.' . $relayDiscipline->id . '.is_team_or_relay'))
        ->toBeTrue()
        ->and($component->get('enrollmentsByDiscipline.' . $relayDiscipline->id . '.discipline_fee'))
        ->toBe(4.0)
        ->and($component->get('enrollmentsByDiscipline.' . $relayDiscipline->id . '.per_person_subtotal'))
        ->toBe(40.0)
        ->and($component->get('enrollmentsByDiscipline.' . $relayDiscipline->id . '.subtotal'))
        ->toBe(44.0)
        ->and($component->get('grandTotal'))
        ->toBe(44.0);
});

it('deduplicates per-person fee for athlete in multiple disciplines', function () {
    // Create two disciplines
    $discipline1 = Discipline::factory()->create(['name' => '100m Freestyle', 'enrollment_type' => 'individual']);
    $discipline2 = Discipline::factory()->create(['name' => '200m Freestyle', 'enrollment_type' => 'individual']);

    // Create main enrollment
    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->entity->id,
        'enrollable_type' => Entity::class,
    ]);

    // Enroll same athlete in both disciplines
    AthleteEnrollment::create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'discipline_id' => $discipline1->id,
        'per_person_price' => 10.00,
        'discipline_price' => 1.00,
        'event_fee' => 0,
        'total_price' => 11.00,
        'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
    ]);

    AthleteEnrollment::create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'discipline_id' => $discipline2->id,
        'per_person_price' => 10.00,
        'discipline_price' => 1.00,
        'event_fee' => 0,
        'total_price' => 11.00,
        'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
    ]);

    $component = Livewire::test(ReviewAndPay::class, [
        'event' => $this->event,
        'model' => $this->entity,
    ]);

    // Per-person should only be counted ONCE (10), plus 2 discipline fees (1+1) = 12
    // NOT: 2 x 11 = 22
    expect($component->get('grandTotal'))->toBe(12.0);
});

it('correctly reads coach price from enrollment', function () {
    // Create main enrollment
    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->entity->id,
        'enrollable_type' => Entity::class,
    ]);

    // Create coach enrollment with price (Pending state = appears in STEP 2)
    CoachEnrollment::create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'price' => 30.00,
        'status_class' => PendingCoachEnrollmentState::class,
    ]);

    $component = Livewire::test(ReviewAndPay::class, [
        'event' => $this->event,
        'model' => $this->entity,
    ]);

    // Check that the coach price is correctly read
    expect($component->get('otherEnrollments.coaches'))
        ->toHaveCount(1)
        ->and($component->get('otherEnrollments.coaches.0.price'))
        ->toBe(30.0)
        ->and($component->get('grandTotal'))
        ->toBe(30.0);
});

it('calculates correct grand total with multiple enrollment types', function () {
    // Create main enrollment
    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->entity->id,
        'enrollable_type' => Entity::class,
    ]);

    $individual2 = Individual::factory()->create();

    // Create athlete enrollment with discipline
    AthleteEnrollment::create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'discipline_id' => $this->discipline->id,
        'per_person_price' => 20.00,
        'discipline_price' => 0,
        'event_fee' => 0,
        'total_price' => 20.00,
        'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
    ]);

    // Create coach enrollment (Pending state = appears in STEP 2)
    CoachEnrollment::create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'entity_id' => $this->entity->id,
        'individual_id' => $individual2->id,
        'price' => 15.00,
        'status_class' => PendingCoachEnrollmentState::class,
    ]);

    $component = Livewire::test(ReviewAndPay::class, [
        'event' => $this->event,
        'model' => $this->entity,
    ]);

    // Grand total should be athlete (20) + coach (15) = 35
    expect($component->get('grandTotal'))->toBe(35.0);
});

it('can remove an athlete enrollment', function () {
    // Create main enrollment
    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->entity->id,
        'enrollable_type' => Entity::class,
    ]);

    // Create athlete enrollment
    $athleteEnrollment = AthleteEnrollment::create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'discipline_id' => $this->discipline->id,
        'per_person_price' => 50.00,
        'discipline_price' => 0,
        'event_fee' => 0,
        'total_price' => 50.00,
        'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
    ]);

    $component = Livewire::test(ReviewAndPay::class, [
        'event' => $this->event,
        'model' => $this->entity,
    ]);

    // Verify enrollment exists
    expect($component->get('grandTotal'))->toBe(50.0);

    // Remove the enrollment
    $component->call('removeEnrollment', $athleteEnrollment->id, 'athlete');

    // Verify enrollment was removed
    expect($component->get('grandTotal'))->toBe(0.0);
    expect(AthleteEnrollment::find($athleteEnrollment->id))->toBeNull();
});

it('shows needsPaymentDocument when entity has enrollments but no payment document', function () {
    // Create main enrollment WITHOUT document_id
    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->entity->id,
        'enrollable_type' => Entity::class,
        'document_id' => null,
    ]);

    // Create athlete enrollment with price
    AthleteEnrollment::create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'discipline_id' => $this->discipline->id,
        'per_person_price' => 25.00,
        'discipline_price' => 15.00,
        'event_fee' => 0,
        'total_price' => 40.00,
        'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
    ]);

    $component = Livewire::test(ReviewAndPay::class, [
        'event' => $this->event,
        'model' => $this->entity,
    ]);

    // grandTotal > 0 and no document_id = needsPaymentDocument should be true
    expect($component->get('grandTotal'))->toBe(40.0);

    // Check the view data
    $component
        ->assertSet('grandTotal', 40.0)
        ->assertViewHas('needsPaymentDocument', true)
        ->assertViewHas('hasPendingPayment', false);
});

it('shows hasPendingPayment when entity has enrollments with existing payment document', function () {
    // Create a mock document
    $document = \Domain\Documents\Models\Document::factory()->create();

    // Create main enrollment WITH document_id
    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->entity->id,
        'enrollable_type' => Entity::class,
        'document_id' => $document->id,
    ]);

    // Create athlete enrollment with price
    AthleteEnrollment::create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'discipline_id' => $this->discipline->id,
        'per_person_price' => 25.00,
        'discipline_price' => 15.00,
        'event_fee' => 0,
        'total_price' => 40.00,
        'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
    ]);

    $component = Livewire::test(ReviewAndPay::class, [
        'event' => $this->event,
        'model' => $this->entity,
    ]);

    // grandTotal > 0 and has document_id = hasPendingPayment should be true
    $component
        ->assertSet('grandTotal', 40.0)
        ->assertViewHas('needsPaymentDocument', false)
        ->assertViewHas('hasPendingPayment', true);
});

it('shows neither payment button when grandTotal is zero', function () {
    // Create main enrollment without document
    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->entity->id,
        'enrollable_type' => Entity::class,
        'document_id' => null,
    ]);

    // Create athlete enrollment with zero prices
    AthleteEnrollment::create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'discipline_id' => $this->discipline->id,
        'per_person_price' => 0,
        'discipline_price' => 0,
        'event_fee' => 0,
        'total_price' => 0,
        'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
    ]);

    $component = Livewire::test(ReviewAndPay::class, [
        'event' => $this->event,
        'model' => $this->entity,
    ]);

    // grandTotal == 0 = both should be false
    $component
        ->assertSet('grandTotal', 0.0)
        ->assertViewHas('needsPaymentDocument', false)
        ->assertViewHas('hasPendingPayment', false);
});

it('shows needsPaymentDocument when existing document is already paid', function () {
    // Create a document with PAID status (settled)
    $document = \Domain\Documents\Models\Document::factory()->create([
        'status_class' => \Domain\Documents\States\PaidDocumentState::class,
    ]);

    // Create enrollment pointing to the paid document
    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->entity->id,
        'enrollable_type' => Entity::class,
        'document_id' => $document->id,
    ]);

    // Create new pending athlete enrollment
    AthleteEnrollment::create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'discipline_id' => $this->discipline->id,
        'per_person_price' => 20.00,
        'discipline_price' => 0,
        'event_fee' => 0,
        'total_price' => 20.00,
        'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
    ]);

    $component = Livewire::test(ReviewAndPay::class, [
        'event' => $this->event,
        'model' => $this->entity,
    ]);

    // Document is paid/settled, so new pending athletes should trigger needsPaymentDocument
    $component
        ->assertSet('grandTotal', 20.0)
        ->assertViewHas('needsPaymentDocument', true)
        ->assertViewHas('hasPendingPayment', false);
});

it('shows hasPendingPayment when document exists but is not settled', function () {
    // Create a document with a non-settled status (e.g. pending)
    $document = \Domain\Documents\Models\Document::factory()->create([
        'status_class' => \Domain\Documents\States\PendingDocumentState::class,
    ]);

    // Create enrollment pointing to the pending document
    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->entity->id,
        'enrollable_type' => Entity::class,
        'document_id' => $document->id,
    ]);

    // Create athlete enrollment with price
    AthleteEnrollment::create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'discipline_id' => $this->discipline->id,
        'per_person_price' => 25.00,
        'discipline_price' => 0,
        'event_fee' => 0,
        'total_price' => 25.00,
        'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
    ]);

    $component = Livewire::test(ReviewAndPay::class, [
        'event' => $this->event,
        'model' => $this->entity,
    ]);

    // Document exists and is not settled = hasPendingPayment should be true
    $component
        ->assertSet('grandTotal', 25.0)
        ->assertViewHas('needsPaymentDocument', false)
        ->assertViewHas('hasPendingPayment', true);
});

it('clears stale document_id when document is soft-deleted and shows needsPaymentDocument', function () {
    // Create a document and then soft-delete it
    $document = \Domain\Documents\Models\Document::factory()->create();
    $document->delete();

    // Create enrollment pointing to the soft-deleted document
    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->entity->id,
        'enrollable_type' => Entity::class,
        'document_id' => $document->id,
    ]);

    // Create athlete enrollment with price
    AthleteEnrollment::create([
        'enrollment_id' => $enrollment->id,
        'event_id' => $this->event->id,
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'discipline_id' => $this->discipline->id,
        'per_person_price' => 25.00,
        'discipline_price' => 15.00,
        'event_fee' => 0,
        'total_price' => 40.00,
        'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
    ]);

    $component = Livewire::test(ReviewAndPay::class, [
        'event' => $this->event,
        'model' => $this->entity,
    ]);

    // Stale document_id should be cleared, allowing a new payment document
    $component
        ->assertSet('grandTotal', 40.0)
        ->assertViewHas('needsPaymentDocument', true)
        ->assertViewHas('hasPendingPayment', false);

    // Verify the enrollment's document_id was cleared in the database
    expect($enrollment->fresh()->document_id)->toBeNull();
});

it('calculates event fee only once regardless of number of athletes', function () {
    // Create an individual discipline explicitly
    $individualDiscipline = Discipline::factory()->create([
        'name' => 'Event Fee Test Discipline',
        'enrollment_type' => 'individual',
    ]);

    // Create main enrollment
    $enrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->entity->id,
        'enrollable_type' => Entity::class,
    ]);

    // Create multiple athletes with event_fee
    $individual2 = Individual::factory()->create();
    $individual3 = Individual::factory()->create();

    $athletes = [$this->individual, $individual2, $individual3];

    foreach ($athletes as $athlete) {
        AthleteEnrollment::create([
            'enrollment_id' => $enrollment->id,
            'event_id' => $this->event->id,
            'entity_id' => $this->entity->id,
            'individual_id' => $athlete->id,
            'discipline_id' => $individualDiscipline->id,
            'per_person_price' => 10.00,
            'discipline_price' => 5.00,
            'event_fee' => 50.00, // This should only be counted ONCE
            'total_price' => 65.00,
            'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED->value,
        ]);
    }

    $component = Livewire::test(ReviewAndPay::class, [
        'event' => $this->event,
        'model' => $this->entity,
    ]);

    // Expected (invoice-style breakdown):
    // - registrations.athletes: 3 athletes x 10 = 30
    // - disciplines.{id}: 3 athletes x 5 = 15
    // - other.event_fee: 50 (only once, NOT 3 x 50 = 150)
    // Total: 30 + 15 + 50 = 95
    expect($component->get('costBreakdown.registrations.athletes.total'))->toBe(30.0)
        ->and($component->get('costBreakdown.registrations.athletes.count'))->toBe(3)
        ->and($component->get('costBreakdown.disciplines.' . $individualDiscipline->id . '.total'))->toBe(15.0)
        ->and($component->get('costBreakdown.other.event_fee.total'))->toBe(50.0)
        ->and($component->get('costBreakdown.other.event_fee.count'))->toBe(1)
        ->and($component->get('grandTotal'))->toBe(95.0);
});
