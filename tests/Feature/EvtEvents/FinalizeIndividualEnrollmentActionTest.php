<?php

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Enums\EvtEventEnrollmentRoleEnum;
use App\Enums\EvtEventFeeTypeEnum;
use App\Enums\EvtEventPaymentStatusEnum;
use App\Models\User;
use Domain\Documents\Actions\CreateDocumentWithDetailsAction;
use Domain\Documents\DataTransferObject\DocumentData;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentType;
use Domain\Documents\States\PaidDocumentState;
use Domain\Documents\States\PendingDocumentState;
use Domain\EvtEvents\Actions\FinalizeIndividualEnrollmentAction;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

// Helper to create common models and pricing
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->individual = Individual::factory()->create();
    $this->event = Event::factory()->create();

    // Ensure the default DocumentType exists for tests
    DocumentType::factory()->create(['code' => DocumentData::DEFAULT_TYPE_CODE]);

    // Create a PER_PERSON pricing record for most tests
    $this->perPersonPrice = 25.0;
    $this->perPersonPricing = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'price_type' => EvtEventFeeTypeEnum::PER_PERSON->value,
        'price' => $this->perPersonPrice,
        'is_active' => true,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDay(),
        'enrollment_role' => EvtEventEnrollmentRoleEnum::ATHLETE->value, // Assuming role-based pricing
    ]);

    // Mock the *underlying* document creation action globally
    $this->mock(CreateDocumentWithDetailsAction::class, function (MockInterface $mock) {
        // Default mock behavior: return a real, persisted document
        // Specific tests can override this if needed (e.g., to check for no document creation)
        $mock->shouldReceive('__invoke')->andReturnUsing(function () {
            return Document::factory()->create([
                'status_class' => PendingDocumentState::class,
                'total_value' => 0, // Actual value should be set by caller action
            ]);
        });
    });
});

// Helper to create AthleteEnrollment for tests
function createAthleteEnrollmentForTest(Enrollment $enrollment, ?Pricing $perPersonPricing = null, ?Pricing $disciplinePricing = null): AthleteEnrollment
{
    // Use the factory's withPricing state to correctly set IDs and prices
    return AthleteEnrollment::factory()
        ->forEnrollment($enrollment)
        ->withPricing($perPersonPricing, $disciplinePricing) // Use the state method
        ->create([
            'individual_id' => $enrollment->enrollable_id, // Ensure correct individual
            'status_class' => EvtAthleteEnrollmentStatusEnum::REGISTERED, // Start as registered
        ]);
}

// --- Test Cases ---

test('initial enrollment with cost creates PENDING enrollment and NEW PENDING document', function () {
    // Arrange
    // Parent Enrollment is created by GetOrCreate... action, simulate its state before Finalize
    // Use make() instead of create() initially to isolate the save step
    $parentEnrollment = Enrollment::factory()->make([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->individual->id,
        'enrollable_type' => Individual::class,
        'user_id' => $this->user->id,
        'payment_status' => EvtEventPaymentStatusEnum::PENDING->value, // Use value
        'total_price' => 0,
        'document_id' => null,
    ]);
    // We need to save it once so athlete enrollments can link to it
    $parentEnrollment->save();

    $newAthleteEnrollments = new Collection([
        // Pass the actual Pricing model
        createAthleteEnrollmentForTest($parentEnrollment, $this->perPersonPricing),
    ]);
    $expectedTotalCost = $this->perPersonPrice;

    // No need to mock CreateEnrollmentPaymentDocumentAction here, as the underlying
    // CreateDocumentWithDetailsAction is mocked in beforeEach
    /*
    $this->mockDocumentAction->shouldReceive('execute')
        ->once()
        ->andReturnUsing(function () {
            return Document::factory()->create([
                'status_class' => PendingDocumentState::class,
                'total_value' => 0,
            ]);
        });
    */

    // Act
    $action = app(FinalizeIndividualEnrollmentAction::class);
    $result = $action->execute($this->event, $this->individual, $parentEnrollment, $newAthleteEnrollments, $this->user);

    // Assert
    expect($result['success'])->toBeTrue();
    expect($result['total_cost'])->toBe($expectedTotalCost);
    $returnedDocumentId = $result['document_id']; // Capture the ID returned by the action
    expect($returnedDocumentId)->toBeUuid(); // Check if a valid UUID (from created doc) is returned

    // Assert parent enrollment state AFTER action execution
    $parentEnrollment->refresh(); // Reload from DB
    expect($parentEnrollment->payment_status)->toBe(EvtEventPaymentStatusEnum::PENDING->value); // Use value
    expect($parentEnrollment->total_price)->toBe($expectedTotalCost);
    expect($parentEnrollment->document_id)->toBe($returnedDocumentId); // Check against the returned ID

    // Assert athlete enrollment status
    // Note: Need to query fresh as the original AE object might be stale if action updated it
    $athleteEnrollment = $parentEnrollment->athleteEnrollments()->first();
    expect($athleteEnrollment)->not->toBeNull();
    expect($athleteEnrollment->status_class)->toBe(EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT); // Compare object directly
});

test('initial enrollment with zero cost creates PAID enrollment and NO document', function () {
    // Arrange
    $parentEnrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->individual->id,
        'enrollable_type' => Individual::class,
        'user_id' => $this->user->id,
        'payment_status' => EvtEventPaymentStatusEnum::PENDING->value, // Use value
        'total_price' => 0,
        'document_id' => null,
    ]);
    $newAthleteEnrollments = new Collection([
        // Pass null for pricing if zero cost
        createAthleteEnrollmentForTest($parentEnrollment, null, null),
    ]);
    $expectedTotalCost = 0.0;

    // Expect underlying document creation NOT to be called
    $this->mock(CreateDocumentWithDetailsAction::class, function (MockInterface $mock) {
        $mock->shouldNotReceive('__invoke');
    });

    // Act
    $action = app(FinalizeIndividualEnrollmentAction::class);
    $result = $action->execute($this->event, $this->individual, $parentEnrollment, $newAthleteEnrollments, $this->user);

    // Assert
    expect($result['success'])->toBeTrue();
    expect($result['total_cost'])->toBe($expectedTotalCost);
    expect($result['document_id'])->toBeNull();

    $parentEnrollment->refresh();
    expect($parentEnrollment->payment_status)->toBe(EvtEventPaymentStatusEnum::PAID->value); // Use value
    expect($parentEnrollment->total_price)->toBe($expectedTotalCost);
    expect($parentEnrollment->document_id)->toBeNull();

    $athleteEnrollment = $newAthleteEnrollments->first()->refresh();
    expect($athleteEnrollment->status_class)->toBe(EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED); // Compare object directly
});

test('adding discipline to PENDING enrollment updates PENDING document value', function () {
    // Arrange
    $existingDocument = Document::factory()->create(['status_class' => PendingDocumentState::class, 'total_value' => $this->perPersonPrice]);
    $parentEnrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->individual->id,
        'enrollable_type' => Individual::class,
        'user_id' => $this->user->id,
        'payment_status' => EvtEventPaymentStatusEnum::PENDING->value, // Use value
        'total_price' => $this->perPersonPrice,
        'document_id' => $existingDocument->id,
    ]);
    // Existing AE that caused the initial cost
    $existingAE = createAthleteEnrollmentForTest($parentEnrollment, $this->perPersonPricing, null); // Pass Pricing
    // New AE being added (only discipline cost)
    // Need a discipline pricing model for this test case
    $disciplinePricing = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'price_type' => EvtEventFeeTypeEnum::FLAT_FEE->value, // Assume FLAT for discipline for simplicity
        'price' => 10.0,
        'is_active' => true,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDay(),
        'enrollment_role' => EvtEventEnrollmentRoleEnum::ATHLETE->value,
        'discipline_id' => Discipline::factory()->create()->id, // Needs a discipline link
    ]);
    $newAthleteEnrollments = new Collection([
        createAthleteEnrollmentForTest($parentEnrollment, null, $disciplinePricing), // Pass Discipline Pricing
    ]);
    $expectedTotalCost = $this->perPersonPrice + $disciplinePricing->price; // Per person (once) + new discipline

    // Expect document creation NOT to be called
    $this->mock(CreateDocumentWithDetailsAction::class, function (MockInterface $mock) {
        $mock->shouldNotReceive('__invoke');
    });

    // Act
    $action = app(FinalizeIndividualEnrollmentAction::class);
    $result = $action->execute($this->event, $this->individual, $parentEnrollment, $newAthleteEnrollments, $this->user);

    // Assert
    expect($result['success'])->toBeTrue();
    expect($result['total_cost'])->toBe($expectedTotalCost);
    expect($result['document_id'])->toBe($existingDocument->id); // Should keep existing doc ID

    $parentEnrollment->refresh();
    expect($parentEnrollment->payment_status)->toBe(EvtEventPaymentStatusEnum::PENDING->value); // Use value
    expect($parentEnrollment->total_price)->toBe($expectedTotalCost);
    expect($parentEnrollment->document_id)->toBe($existingDocument->id);

    // Assert document value updated
    $existingDocument->refresh();
    expect((float) $existingDocument->total_value)->toBe($expectedTotalCost); // Cast to float

    // Assert ALL athlete enrollments are PENDING_PAYMENT
    $parentEnrollment->athleteEnrollments->each(function (AthleteEnrollment $ae) {
        expect($ae->status_class)->toBe(EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT); // Compare object directly
    });
});

test('adding discipline to PAID enrollment keeps PAID status and document', function () {
    // Arrange
    $existingDocument = Document::factory()->create(['status_class' => PaidDocumentState::class, 'total_value' => $this->perPersonPrice]);
    $parentEnrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->individual->id,
        'enrollable_type' => Individual::class,
        'user_id' => $this->user->id,
        'payment_status' => EvtEventPaymentStatusEnum::PAID->value, // Use value
        'total_price' => $this->perPersonPrice,
        'document_id' => $existingDocument->id,
    ]);
    // Existing AE that was paid for
    $existingAE = createAthleteEnrollmentForTest($parentEnrollment, $this->perPersonPricing, null); // Pass Pricing
    $existingAE->status_class = EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED; // Assume it was set correctly after payment
    $existingAE->save();

    // New AE being added (only discipline cost)
    // Need a discipline pricing model for this test case
    $disciplinePricing = Pricing::factory()->create([
        'event_id' => $this->event->id,
        'price_type' => EvtEventFeeTypeEnum::FLAT_FEE->value, // Assume FLAT for discipline for simplicity
        'price' => 10.0,
        'is_active' => true,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDay(),
        'enrollment_role' => EvtEventEnrollmentRoleEnum::ATHLETE->value,
        'discipline_id' => Discipline::factory()->create()->id, // Needs a discipline link
    ]);
    $newAthleteEnrollments = new Collection([
        createAthleteEnrollmentForTest($parentEnrollment, null, $disciplinePricing), // Pass Discipline Pricing
    ]);
    // Although discipline price is added, PER_PERSON fee covers base enrollment
    $expectedTotalCost = $this->perPersonPrice + $disciplinePricing->price; // Total cost reflects all items

    // Expect document creation NOT to be called
    $this->mock(CreateDocumentWithDetailsAction::class, function (MockInterface $mock) {
        $mock->shouldNotReceive('__invoke');
    });

    // Act
    $action = app(FinalizeIndividualEnrollmentAction::class);
    $result = $action->execute($this->event, $this->individual, $parentEnrollment, $newAthleteEnrollments, $this->user);

    // Assert
    expect($result['success'])->toBeTrue();
    expect($result['total_cost'])->toBe($expectedTotalCost); // Total cost reflects added discipline
    expect($result['document_id'])->toBe($existingDocument->id); // Keep PAID document ID

    $parentEnrollment->refresh();
    expect($parentEnrollment->payment_status)->toBe(EvtEventPaymentStatusEnum::PAID->value); // Status remains PAID // Use value
    expect($parentEnrollment->total_price)->toBe($expectedTotalCost);
    expect($parentEnrollment->document_id)->toBe($existingDocument->id);

    // Assert document was NOT changed
    $existingDocument->refresh();
    expect((float) $existingDocument->total_value)->toBe($this->perPersonPrice); // Cast to float
    expect($existingDocument->status_class)->toBe(PaidDocumentState::class);

    // Assert ALL athlete enrollments are DISCIPLINE_ASSIGNED (because parent is PAID)
    $parentEnrollment->athleteEnrollments->each(function (AthleteEnrollment $ae) {
        expect($ae->status_class)->toBe(EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED); // Compare object directly
    });
});

test('removing from PENDING (cost still > 0) updates PENDING document value', function () {
    // Arrange
    // Need pricing models for both discipline prices
    $disciplinePricing1 = Pricing::factory()->create(['event_id' => $this->event->id, 'price_type' => EvtEventFeeTypeEnum::FLAT_FEE->value, 'price' => 10.0, 'is_active' => true, 'enrollment_role' => EvtEventEnrollmentRoleEnum::ATHLETE->value, 'discipline_id' => Discipline::factory()->create()->id]);
    $disciplinePricing2 = Pricing::factory()->create(['event_id' => $this->event->id, 'price_type' => EvtEventFeeTypeEnum::FLAT_FEE->value, 'price' => 15.0, 'is_active' => true, 'enrollment_role' => EvtEventEnrollmentRoleEnum::ATHLETE->value, 'discipline_id' => Discipline::factory()->create()->id]);

    $initialTotalCost = $this->perPersonPrice + $disciplinePricing1->price + $disciplinePricing2->price;
    $expectedFinalCost = $disciplinePricing2->price; // Expected: 15.0

    $existingDocument = Document::factory()->create(['status_class' => PendingDocumentState::class, 'total_value' => $initialTotalCost]);
    $parentEnrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->individual->id,
        'enrollable_type' => Individual::class,
        'user_id' => $this->user->id,
        'payment_status' => EvtEventPaymentStatusEnum::PENDING->value, // Use value
        'total_price' => $initialTotalCost,
        'document_id' => $existingDocument->id,
    ]);
    // Athlete enrollments (one includes per_person fee)
    $aeToRemove = createAthleteEnrollmentForTest($parentEnrollment, $this->perPersonPricing, $disciplinePricing1); // Pass Pricing
    $aeToKeep = createAthleteEnrollmentForTest($parentEnrollment, null, $disciplinePricing2); // Pass Pricing

    // Simulate the removal (soft delete) - this happens *before* Finalize is called by Remove action
    $aeToRemove->delete();

    // Act: Finalize is called with an empty collection for 'new' AEs
    $action = app(FinalizeIndividualEnrollmentAction::class);
    $result = $action->execute($this->event, $this->individual, $parentEnrollment, new Collection, $this->user);

    // Assert
    expect($result['success'])->toBeTrue();
    expect($result['total_cost'])->toBe($expectedFinalCost); // Cost updated to 15.0
    expect($result['document_id'])->toBe($existingDocument->id);

    $parentEnrollment->refresh();
    expect($parentEnrollment->payment_status)->toBe(EvtEventPaymentStatusEnum::PENDING->value); // Use value
    expect($parentEnrollment->total_price)->toBe($expectedFinalCost);
    expect($parentEnrollment->document_id)->toBe($existingDocument->id);

    $existingDocument->refresh();
    expect((float) $existingDocument->total_value)->toBe($expectedFinalCost); // Cast to float // Document value updated

    // Assert remaining athlete enrollment is PENDING_PAYMENT
    $aeToKeep->refresh();
    expect($aeToKeep->status_class)->toBe(EvtAthleteEnrollmentStatusEnum::PENDING_PAYMENT); // Compare object directly
});

test('removing last discipline from PENDING sets enrollment to PAID and unlinks document', function () {
    // Arrange
    $initialTotalCost = $this->perPersonPrice;
    $expectedFinalCost = 0.0;

    $existingDocument = Document::factory()->create(['status_class' => PendingDocumentState::class, 'total_value' => $initialTotalCost]);
    $parentEnrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->individual->id,
        'enrollable_type' => Individual::class,
        'user_id' => $this->user->id,
        'payment_status' => EvtEventPaymentStatusEnum::PENDING->value, // Start as PENDING
        'total_price' => $initialTotalCost,
        'document_id' => $existingDocument->id,
    ]);
    $aeToRemove = createAthleteEnrollmentForTest($parentEnrollment, $this->perPersonPricing, null); // Pass Pricing

    // Simulate removal
    $aeToRemove->delete();

    // Expect document creation NOT to be called
    $this->mock(CreateDocumentWithDetailsAction::class, function (MockInterface $mock) {
        $mock->shouldNotReceive('__invoke');
    });

    // Act
    $action = app(FinalizeIndividualEnrollmentAction::class);
    $result = $action->execute($this->event, $this->individual, $parentEnrollment, new Collection, $this->user);

    // Assert
    expect($result['success'])->toBeTrue();
    expect($result['total_cost'])->toBe($expectedFinalCost); // Cost becomes 0
    expect($result['document_id'])->toBeNull(); // Document should be unlinked

    $parentEnrollment->refresh();
    expect($parentEnrollment->payment_status)->toBe(EvtEventPaymentStatusEnum::PAID->value); // Status becomes PAID
    expect($parentEnrollment->total_price)->toBe($expectedFinalCost); // Price becomes 0
    expect($parentEnrollment->document_id)->toBeNull(); // Document is unlinked

    // Optional: Check athlete enrollment status (should be DISCIPLINE_ASSIGNED as parent is now PAID)
    // Since the only AE was removed, we might expect 0 AEs. Let's assert that.
    expect($parentEnrollment->athleteEnrollments()->count())->toBe(0);

    // Assertions on the original $existingDocument are less relevant now it's unlinked.
    // We can optionally check if it was deleted/cancelled if that logic is added.
    // $existingDocument->refresh();
    // expect($existingDocument->status_class)->toBe(CanceledDocumentState::class); // Example if cancellation logic was added
});

test('removing last discipline from PAID keeps PAID status and PAID document', function () {
    // Arrange
    $initialTotalCost = $this->perPersonPrice;
    $expectedFinalCost = 0.0;

    $existingDocument = Document::factory()->create(['status_class' => PaidDocumentState::class, 'total_value' => $initialTotalCost]);
    $parentEnrollment = Enrollment::factory()->create([
        'event_id' => $this->event->id,
        'enrollable_id' => $this->individual->id,
        'enrollable_type' => Individual::class,
        'user_id' => $this->user->id,
        'payment_status' => EvtEventPaymentStatusEnum::PAID->value, // Use value
        'total_price' => $initialTotalCost,
        'document_id' => $existingDocument->id,
    ]);
    $aeToRemove = createAthleteEnrollmentForTest($parentEnrollment, $this->perPersonPricing, null); // Pass Pricing
    $aeToRemove->status_class = EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED; // Assume was assigned after payment
    $aeToRemove->save();

    // Simulate removal
    $aeToRemove->delete();

    // Act
    $action = app(FinalizeIndividualEnrollmentAction::class);
    $result = $action->execute($this->event, $this->individual, $parentEnrollment, new Collection, $this->user);

    // Assert
    expect($result['success'])->toBeTrue();
    expect($result['total_cost'])->toBe($expectedFinalCost);
    expect($result['document_id'])->toBe($existingDocument->id); // Document link KEPT

    $parentEnrollment->refresh();
    expect($parentEnrollment->payment_status)->toBe(EvtEventPaymentStatusEnum::PAID->value); // Status KEPT PAID // Use value
    expect($parentEnrollment->total_price)->toBe($expectedFinalCost);
    expect($parentEnrollment->document_id)->toBe($existingDocument->id); // Link KEPT

    $existingDocument->refresh();
    expect((float) $existingDocument->total_value)->toBe($initialTotalCost); // Cast to float // Document value UNCHANGED
    expect($existingDocument->status_class)->toBe(PaidDocumentState::class); // Status remains PAID
});
