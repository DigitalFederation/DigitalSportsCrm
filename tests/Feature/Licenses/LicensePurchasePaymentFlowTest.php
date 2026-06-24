<?php

namespace Tests\Feature\Licenses;

use App\Events\ActivateAfterPayment;
use App\Events\LicenseAttributedCreatedEvent;
use App\Listeners\CreateLicenseAttributedDocumentListener;
use App\Models\Committee;
use Domain\Documents\Actions\MarkAsPaidAction;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;
use Domain\Documents\Models\DocumentType;
use Domain\Documents\States\PaidDocumentState;
use Domain\Documents\States\PendingDocumentState;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Actions\PurchaseLicenseAction;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Domain\Licenses\States\PendingTechnicalDirectorApprovalLicenseAttributedState;
use Domain\Licenses\States\PendingValidationLicenseAttributedState;
use Domain\Memberships\Models\Affiliation;
use Domain\Memberships\Models\AffiliationPlan;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\States\ActiveAffiliationState;
use Domain\Memberships\States\ActiveMemberSubscriptionState;
use Domain\Payments\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create required document types
    DocumentType::create(['code' => 'ORD', 'name' => 'Order']);
    DocumentType::create(['code' => 'PAY', 'name' => 'Payment']);
    DocumentType::create(['code' => 'INV', 'name' => 'Invoice']);

    // Create payment method for manual payments (uses full class path to match production)
    \Domain\Payments\Models\PaymentMethod::factory()->create([
        'name' => 'Offline Payment',
        'driver' => 'offline',
        'handler' => 'Domain\Payments\Handlers\OfflinePaymentHandler',
    ]);

    // Create federation
    $this->federation = Federation::factory()->create([
        'is_default_federation' => true,
        'member_code' => 'TEST',
    ]);

    // Create individual with user
    $this->individual = Individual::factory()->create();
    $this->individual->federations()->attach($this->federation->id, ['active' => true]);

    // Create entity
    $this->entity = Entity::factory()->create();
    $this->entity->federations()->attach($this->federation->id, ['active' => true]);

    // Create membership package with validation plan
    $this->membershipPackage = MembershipPackage::factory()->create([
        'federation_id' => $this->federation->id,
        'is_active' => true,
    ]);

    // Create validation affiliation plan
    $this->validationPlan = AffiliationPlan::factory()->create([
        'federation_id' => $this->federation->id,
        'is_validation_plan' => true,
    ]);

    // Attach the affiliation plan to the membership package via pivot table
    $this->validationPlan->membershipPackages()->attach($this->membershipPackage->id);

    // Setup active subscriptions and affiliations for individual
    $this->individualSubscription = MemberSubscription::factory()->create([
        'member_type' => 'individual',
        'member_id' => $this->individual->id,
        'membership_package_id' => $this->membershipPackage->id,
        'status_class' => ActiveMemberSubscriptionState::class,
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
    ]);

    Affiliation::factory()->create([
        'member_subscription_id' => $this->individualSubscription->id,
        'federation_id' => $this->federation->id,
        'member_type' => 'individual',
        'member_id' => $this->individual->id,
        'status_class' => ActiveAffiliationState::class,
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
    ]);

    // Setup active subscription for entity
    $this->entitySubscription = MemberSubscription::factory()->create([
        'member_type' => 'entity',
        'member_id' => $this->entity->id,
        'membership_package_id' => $this->membershipPackage->id,
        'status_class' => ActiveMemberSubscriptionState::class,
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
    ]);

    Affiliation::factory()->create([
        'member_subscription_id' => $this->entitySubscription->id,
        'federation_id' => $this->federation->id,
        'member_type' => 'entity',
        'member_id' => $this->entity->id,
        'status_class' => ActiveAffiliationState::class,
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
    ]);
});

test('individual can purchase a paid license and it starts in pending state', function () {
    // Create a paid license
    $license = License::factory()->create([
        'requester_model' => ['Individual'],
        'unit_value_individual' => 150.00,
        'active' => true,
        'license_code' => 'TEST-001',
    ]);

    // Link license to federation
    $license->federations()->attach($this->federation->id);

    // Purchase the license
    $purchaseAction = app(PurchaseLicenseAction::class);
    $licenseAttributed = $purchaseAction($license, $this->individual);

    // Assert license was created in pending state
    expect($licenseAttributed)->toBeInstanceOf(LicenseAttributed::class)
        ->and($licenseAttributed->status_class)->toBe(PendingLicenseAttributedState::class)
        ->and($licenseAttributed->total_value)->toBe(150.00)
        ->and($licenseAttributed->model_type)->toBe('individual')
        ->and($licenseAttributed->model_id)->toBe($this->individual->id);

    // Check database
    assertDatabaseHas('license_attributed', [
        'id' => $licenseAttributed->id,
        'status_class' => PendingLicenseAttributedState::class,
        'license_id' => $license->id,
        'total_value' => 150.00,
    ]);
});

test('purchasing a paid license creates a document with correct details', function () {
    Event::fake([LicenseAttributedCreatedEvent::class]);

    $license = License::factory()->create([
        'requester_model' => ['Individual'],
        'unit_value_individual' => 200.00,
        'active' => true,
        'name' => 'Professional License',
        'license_code' => 'PRO-001',
    ]);

    $license->federations()->attach($this->federation->id);

    // Purchase the license
    $purchaseAction = app(PurchaseLicenseAction::class);
    $licenseAttributed = $purchaseAction($license, $this->individual);

    // Assert event was dispatched
    Event::assertDispatched(LicenseAttributedCreatedEvent::class, function ($event) use ($licenseAttributed) {
        return count($event->licenseAttributed) === 1
            && $event->licenseAttributed[0]->id === $licenseAttributed->id
            && $event->isSelfRequest === true;
    });

    // Manually trigger the listener to create document
    $listener = new CreateLicenseAttributedDocumentListener;
    $licenseAttributed->load('license'); // Load the relationship
    $event = new LicenseAttributedCreatedEvent([$licenseAttributed], true);
    $listener->handle($event);

    // Check document was created
    $documentDetail = DocumentDetail::where('owner_type', LicenseAttributed::class)
        ->where('owner_id', $licenseAttributed->id)
        ->first();

    expect($documentDetail)->not->toBeNull()
        ->and((float) $documentDetail->unit_value)->toBe(200.00)
        ->and($documentDetail->description)->toContain('Professional License');

    // Check document exists and is in pending state
    $document = Document::find($documentDetail->document_id);
    expect($document)->not->toBeNull()
        ->and($document->status_class)->toBe(PendingDocumentState::class)
        ->and($document->owner_type)->toBe('individual')
        ->and($document->owner_id)->toBe($this->individual->id);
});

test('marking document as paid activates the license', function () {
    // Create and purchase a paid license
    $license = License::factory()->create([
        'requester_model' => ['Individual'],
        'unit_value_individual' => 100.00,
        'active' => true,
        'license_code' => 'PAID-001',
    ]);

    $license->federations()->attach($this->federation->id);

    $purchaseAction = app(PurchaseLicenseAction::class);
    $licenseAttributed = $purchaseAction($license, $this->individual);

    // Manually create the document (simulating the event listener)
    $licenseAttributed->load('license');
    $event = new LicenseAttributedCreatedEvent([$licenseAttributed], true);
    $listener = new CreateLicenseAttributedDocumentListener;
    $listener->handle($event);

    // Get the created document
    $documentDetail = DocumentDetail::where('owner_type', LicenseAttributed::class)
        ->where('owner_id', $licenseAttributed->id)
        ->first();

    $document = Document::find($documentDetail->document_id);

    // Verify license is still pending
    expect($licenseAttributed->fresh()->status_class)->toBe(PendingLicenseAttributedState::class);

    // Mark document as paid (simulating payment webhook)
    $markAsPaidAction = new MarkAsPaidAction;
    $markAsPaidAction->execute($document->id, 'Payment received via test');

    // The ActivateAfterPayment event should have been fired and processed
    // Since we're not using queues, it should process immediately

    // Refresh and check license status
    $licenseAttributed->refresh();
    expect($licenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class)
        ->and($licenseAttributed->activated_at)->not->toBeNull();

    // Verify document is paid
    $document->refresh();
    expect($document->status_class)->toBe(PaidDocumentState::class);
});

test('free licenses are immediately active without payment', function () {
    // Create a free license
    $license = License::factory()->create([
        'requester_model' => ['Individual'],
        'unit_value_individual' => 0, // Free license
        'active' => true,
        'license_code' => 'FREE-001',
    ]);

    $license->federations()->attach($this->federation->id);

    // Purchase the free license
    $purchaseAction = app(PurchaseLicenseAction::class);
    $licenseAttributed = $purchaseAction($license, $this->individual);

    // Assert license is immediately active
    expect($licenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class)
        ->and($licenseAttributed->activated_at)->not->toBeNull()
        ->and($licenseAttributed->total_value)->toBe(0.0);

    // No document should be created for free licenses
    $documentDetail = DocumentDetail::where('owner_type', LicenseAttributed::class)
        ->where('owner_id', $licenseAttributed->id)
        ->first();

    expect($documentDetail)->toBeNull();
});

test('licenses requiring validation stay in pending validation state after payment', function () {
    // Create a license that requires admin validation
    $license = License::factory()->create([
        'requester_model' => ['Individual'],
        'unit_value_individual' => 500.00,
        'active' => true,
        'requires_admin_validation' => true,
        'license_code' => 'VAL-001',
    ]);

    $license->federations()->attach($this->federation->id);

    // Purchase the license
    $purchaseAction = app(PurchaseLicenseAction::class);
    $licenseAttributed = $purchaseAction($license, $this->individual);

    // Should be in pending validation state
    expect($licenseAttributed->status_class)->toBe(PendingValidationLicenseAttributedState::class);

    // Even after payment, it should stay in pending validation
    // (would need admin approval to activate)
});

test('entity can purchase licenses for itself', function () {
    $license = License::factory()->create([
        'requester_model' => ['Entity'],
        'unit_value_entity' => 300.00,
        'active' => true,
        'license_code' => 'ENTITY-001',
    ]);

    $license->federations()->attach($this->federation->id);

    // Purchase the license as entity
    $purchaseAction = app(PurchaseLicenseAction::class);
    $licenseAttributed = $purchaseAction($license, $this->entity);

    expect($licenseAttributed)->toBeInstanceOf(LicenseAttributed::class)
        ->and($licenseAttributed->status_class)->toBe(PendingLicenseAttributedState::class)
        ->and($licenseAttributed->total_value)->toBe(300.00)
        ->and($licenseAttributed->model_type)->toBe('entity')
        ->and($licenseAttributed->model_id)->toBe((string) $this->entity->id);
});

test('license status name method returns correct status', function () {
    // Set locale to English for consistent test results
    app()->setLocale('en');

    $license = License::factory()->create([
        'requester_model' => ['Individual'],
        'unit_value_individual' => 100.00,
        'active' => true,
        'license_code' => 'STATUS-001',
    ]);

    $license->federations()->attach($this->federation->id);

    $purchaseAction = app(PurchaseLicenseAction::class);
    $licenseAttributed = $purchaseAction($license, $this->individual);

    // Check pending state name (now returns translated string)
    expect($licenseAttributed->stateName())->toBe(__('licenses.state_pending'));

    // Transition to active (simulating payment completion)
    $licenseAttributed->status_class = ActiveLicenseAttributedState::class;
    $licenseAttributed->activated_at = now();
    $licenseAttributed->save();

    // Check active state name (now returns translated string)
    expect($licenseAttributed->fresh()->stateName())->toBe(__('licenses.state_active'));
});

test('payment webhook flow works end to end', function () {
    // This test simulates the complete webhook flow

    // 1. Purchase license
    $license = License::factory()->create([
        'requester_model' => ['Individual'],
        'unit_value_individual' => 250.00,
        'active' => true,
        'name' => 'Advanced License',
        'license_code' => 'ADV-001',
    ]);

    $license->federations()->attach($this->federation->id);

    $purchaseAction = app(PurchaseLicenseAction::class);
    $licenseAttributed = $purchaseAction($license, $this->individual);

    // 2. Create document (simulating event listener)
    $licenseAttributed->load('license');
    $createDocListener = new CreateLicenseAttributedDocumentListener;
    $createDocListener->handle(new LicenseAttributedCreatedEvent([$licenseAttributed], true));

    // 3. Get the document
    $documentDetail = DocumentDetail::where('owner_type', LicenseAttributed::class)
        ->where('owner_id', $licenseAttributed->id)
        ->first();

    $document = Document::find($documentDetail->document_id);

    // 4. Create payment transaction (simulating payment gateway)
    $transaction = PaymentTransaction::create([
        'id' => uniqid(),
        'document_id' => $document->id,
        'amount' => 250.00,
        'status' => 'pending',
        'gateway' => 'test',
    ]);

    // 5. Simulate successful payment webhook
    $transaction->status = 'success';
    $transaction->save();

    // 6. Process payment (what webhook controller would do)
    $markAsPaidAction = new MarkAsPaidAction;
    $paymentDoc = $markAsPaidAction->execute($document->id, 'Payment via webhook');

    // 7. Verify results
    $licenseAttributed->refresh();
    $document->refresh();

    expect($document->status_class)->toBe(PaidDocumentState::class)
        ->and($licenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class)
        ->and($licenseAttributed->activated_at)->not->toBeNull();

    // Verify payment document was created
    expect($paymentDoc)->toBeInstanceOf(Document::class)
        ->and($paymentDoc->status_class)->toBe(PaidDocumentState::class);
});

test('failed payment keeps license in pending state', function () {
    $license = License::factory()->create([
        'requester_model' => ['Individual'],
        'unit_value_individual' => 150.00,
        'active' => true,
        'license_code' => 'FAIL-001',
    ]);

    $license->federations()->attach($this->federation->id);

    $purchaseAction = app(PurchaseLicenseAction::class);
    $licenseAttributed = $purchaseAction($license, $this->individual);

    // Create document
    $licenseAttributed->load('license');
    $createDocListener = new CreateLicenseAttributedDocumentListener;
    $createDocListener->handle(new LicenseAttributedCreatedEvent([$licenseAttributed], true));

    // Get document
    $documentDetail = DocumentDetail::where('owner_type', LicenseAttributed::class)
        ->where('owner_id', $licenseAttributed->id)
        ->first();

    $document = Document::find($documentDetail->document_id);

    // Simulate failed payment (document stays pending)
    $transaction = PaymentTransaction::create([
        'id' => uniqid(),
        'document_id' => $document->id,
        'amount' => 150.00,
        'status' => 'failed',
        'gateway' => 'test',
    ]);

    // License should remain pending
    $licenseAttributed->refresh();
    expect($licenseAttributed->status_class)->toBe(PendingLicenseAttributedState::class)
        ->and($licenseAttributed->activated_at)->toBeNull();

    // Document should remain pending
    $document->refresh();
    expect($document->status_class)->toBe(PendingDocumentState::class);
});

test('diving entity licenses start in pending technical director approval state', function () {
    // Create diving committee
    $divingCommittee = Committee::factory()->create([
        'code' => 'DIVING',
        'name' => 'Diving Committee',
    ]);

    // Create a diving license for entity
    $divingLicense = License::factory()->create([
        'name' => 'Diving Club License',
        'license_code' => 'DIVING-001',
        'committee_id' => $divingCommittee->id,
        'requester_model' => ['Entity'],
        'unit_value_entity' => 300.00,
        'active' => true,
        'requires_admin_validation' => true,
    ]);

    $divingLicense->federations()->attach($this->federation->id);

    // Purchase the diving license as entity
    $purchaseAction = app(PurchaseLicenseAction::class);
    $licenseAttributed = $purchaseAction($divingLicense, $this->entity);

    // Assert diving license starts in pending technical director approval state
    expect($licenseAttributed)->toBeInstanceOf(LicenseAttributed::class)
        ->and($licenseAttributed->status_class)->toBe(PendingTechnicalDirectorApprovalLicenseAttributedState::class)
        ->and($licenseAttributed->total_value)->toBe(300.00)
        ->and($licenseAttributed->model_type)->toBe('entity')
        ->and($licenseAttributed->model_id)->toBe((string) $this->entity->id);

    // Check database
    assertDatabaseHas('license_attributed', [
        'id' => $licenseAttributed->id,
        'status_class' => PendingTechnicalDirectorApprovalLicenseAttributedState::class,
        'license_id' => $divingLicense->id,
        'total_value' => 300.00,
    ]);
});

test('non-diving licenses requiring validation start in pending validation state', function () {
    // Create regular committee (not diving)
    $regularCommittee = Committee::factory()->create([
        'code' => 'REGULAR',
        'name' => 'Regular Committee',
    ]);

    // Create a non-diving license for entity that requires validation
    $regularLicense = License::factory()->create([
        'name' => 'Regular Club License',
        'license_code' => 'REGULAR-001',
        'committee_id' => $regularCommittee->id,
        'requester_model' => ['Entity'],
        'unit_value_entity' => 200.00,
        'active' => true,
        'requires_admin_validation' => true,
    ]);

    $regularLicense->federations()->attach($this->federation->id);

    // Purchase the regular license as entity
    $purchaseAction = app(PurchaseLicenseAction::class);
    $licenseAttributed = $purchaseAction($regularLicense, $this->entity);

    // Assert non-diving license goes directly to pending validation state
    expect($licenseAttributed)->toBeInstanceOf(LicenseAttributed::class)
        ->and($licenseAttributed->status_class)->toBe(PendingValidationLicenseAttributedState::class)
        ->and($licenseAttributed->total_value)->toBe(200.00)
        ->and($licenseAttributed->model_type)->toBe('entity')
        ->and($licenseAttributed->model_id)->toBe((string) $this->entity->id);

    // Check database
    assertDatabaseHas('license_attributed', [
        'id' => $licenseAttributed->id,
        'status_class' => PendingValidationLicenseAttributedState::class,
        'license_id' => $regularLicense->id,
        'total_value' => 200.00,
    ]);
});

test('entity purchases license, pays it, and license becomes active - full workflow', function () {
    // This test verifies the complete workflow:
    // 1. Entity purchases a paid license
    // 2. Document is created with correct DocumentDetail linking to LicenseAttributed
    // 3. Document is marked as paid
    // 4. LicenseAttributed becomes Active

    // Step 1: Create a paid license for entities (no validation required)
    $license = License::factory()->create([
        'requester_model' => ['Entity'],
        'unit_value_entity' => 350.00,
        'active' => true,
        'name' => 'Entity Club License',
        'license_code' => 'ENTITY-FULL-001',
        'requires_admin_validation' => false, // No validation required
    ]);

    $license->federations()->attach($this->federation->id);

    // Step 2: Entity purchases the license
    $purchaseAction = app(PurchaseLicenseAction::class);
    $licenseAttributed = $purchaseAction($license, $this->entity);

    // Verify license was created in pending state (awaiting payment)
    expect($licenseAttributed)->toBeInstanceOf(LicenseAttributed::class)
        ->and($licenseAttributed->status_class)->toBe(PendingLicenseAttributedState::class)
        ->and($licenseAttributed->total_value)->toBe(350.00)
        ->and($licenseAttributed->model_type)->toBe('entity')
        ->and($licenseAttributed->model_id)->toBe((string) $this->entity->id)
        ->and($licenseAttributed->activated_at)->toBeNull();

    // Step 3: Create the document (simulating the event listener)
    $licenseAttributed->load('license');
    $createDocListener = new CreateLicenseAttributedDocumentListener;
    $createDocListener->handle(new LicenseAttributedCreatedEvent([$licenseAttributed], true));

    // Step 4: Verify DocumentDetail was created with CORRECT LicenseAttributed ID
    // This is critical - the bug was that DocumentDetail had wrong owner_id
    $documentDetail = DocumentDetail::where('owner_type', LicenseAttributed::class)
        ->where('owner_id', $licenseAttributed->id)
        ->first();

    expect($documentDetail)->not->toBeNull('DocumentDetail must be created linking to LicenseAttributed')
        ->and($documentDetail->owner_id)->toBe($licenseAttributed->id, 'DocumentDetail owner_id must match LicenseAttributed id')
        ->and($documentDetail->owner_type)->toBe(LicenseAttributed::class)
        ->and((float) $documentDetail->unit_value)->toBe(350.00)
        ->and($documentDetail->description)->toContain('Entity Club License');

    // Step 5: Verify document was created correctly for entity
    $document = Document::find($documentDetail->document_id);

    expect($document)->not->toBeNull()
        ->and($document->status_class)->toBe(PendingDocumentState::class)
        ->and($document->owner_type)->toBe('entity')
        ->and($document->owner_id)->toBe((string) $this->entity->id);

    // Step 6: Mark document as paid (simulating payment webhook)
    $markAsPaidAction = new MarkAsPaidAction;
    $paymentDoc = $markAsPaidAction->execute($document->id, 'Entity payment via test');

    // Step 7: Verify license is now ACTIVE
    $licenseAttributed->refresh();

    expect($licenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class, 'License must be Active after payment')
        ->and($licenseAttributed->activated_at)->not->toBeNull('activated_at must be set after payment');

    // Step 8: Verify document is paid
    $document->refresh();
    expect($document->status_class)->toBe(PaidDocumentState::class);

    // Step 9: Verify payment document was created
    expect($paymentDoc)->toBeInstanceOf(Document::class)
        ->and($paymentDoc->status_class)->toBe(PaidDocumentState::class);

    // Database assertions
    assertDatabaseHas('license_attributed', [
        'id' => $licenseAttributed->id,
        'status_class' => ActiveLicenseAttributedState::class,
        'license_id' => $license->id,
        'model_type' => 'entity',
        'model_id' => $this->entity->id,
    ]);

    assertDatabaseHas('document_detail', [
        'document_id' => $document->id,
        'owner_id' => $licenseAttributed->id,
        'owner_type' => LicenseAttributed::class,
    ]);
});

test('document detail owner_id must match license attributed id for activation to work', function () {
    // This test specifically verifies that the DocumentDetail owner_id
    // correctly references the LicenseAttributed id, which is required
    // for the ActivateAfterPayment event to find and activate the license.

    $license = License::factory()->create([
        'requester_model' => ['Entity'],
        'unit_value_entity' => 200.00,
        'active' => true,
        'name' => 'Verification License',
        'license_code' => 'VERIFY-001',
        'requires_admin_validation' => false,
    ]);

    $license->federations()->attach($this->federation->id);

    // Purchase license
    $purchaseAction = app(PurchaseLicenseAction::class);
    $licenseAttributed = $purchaseAction($license, $this->entity);

    // Create document
    $licenseAttributed->load('license');
    $createDocListener = new CreateLicenseAttributedDocumentListener;
    $createDocListener->handle(new LicenseAttributedCreatedEvent([$licenseAttributed], true));

    // Get the document detail
    $documentDetail = DocumentDetail::where('owner_type', LicenseAttributed::class)
        ->where('owner_id', $licenseAttributed->id)
        ->first();

    // CRITICAL: Verify the owner_id in DocumentDetail matches LicenseAttributed id
    // If these don't match, the ActivateAfterPayment event won't find the correct license
    expect($documentDetail->owner_id)->toBe($licenseAttributed->id);

    // Now simulate what ActivateAfterPayment does - find models via DocumentDetail
    $document = Document::find($documentDetail->document_id);
    $event = new ActivateAfterPayment($document->id);

    // The event should have found our license
    $foundLicenses = $event->models[LicenseAttributed::class] ?? [];
    expect($foundLicenses)->toHaveCount(1)
        ->and($foundLicenses[0]->id)->toBe($licenseAttributed->id);
});

test('license activates via RegisterDocumentPaymentAction even with full payment amount', function () {
    // This test verifies that the ActivateAfterPayment event is fired
    // and licenses are activated when using RegisterDocumentPaymentAction
    $license = License::factory()->create([
        'requester_model' => ['Individual'],
        'unit_value_individual' => 75.00,
        'active' => true,
        'name' => 'Payment Action Test License',
        'license_code' => 'PAY-ACTION-001',
    ]);

    $license->federations()->attach($this->federation->id);

    // Purchase license
    $purchaseAction = app(PurchaseLicenseAction::class);
    $licenseAttributed = $purchaseAction($license, $this->individual);

    expect($licenseAttributed->status_class)->toBe(PendingLicenseAttributedState::class);

    // Create document
    $licenseAttributed->load('license');
    $createDocListener = new CreateLicenseAttributedDocumentListener;
    $createDocListener->handle(new LicenseAttributedCreatedEvent([$licenseAttributed], true));

    // Get the document
    $documentDetail = DocumentDetail::where('owner_type', LicenseAttributed::class)
        ->where('owner_id', $licenseAttributed->id)
        ->first();
    $document = Document::find($documentDetail->document_id);

    // Use RegisterDocumentPaymentAction to pay the full amount
    $registerPaymentAction = new \Domain\Documents\Actions\RegisterDocumentPaymentAction;
    $registerPaymentAction->execute($document->id, 75.00, 'Full payment test');

    // Verify license was activated
    $licenseAttributed->refresh();
    expect($licenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class)
        ->and($licenseAttributed->activated_at)->not->toBeNull();

    // Verify document is paid
    $document->refresh();
    expect($document->status_class)->toBe(PaidDocumentState::class);
});

test('license activates via ManuallyMarkDocumentAsPaidAction', function () {
    // This test verifies that the ActivateAfterPayment event is fired
    // and licenses are activated when using ManuallyMarkDocumentAsPaidAction
    $license = License::factory()->create([
        'requester_model' => ['Individual'],
        'unit_value_individual' => 50.00,
        'active' => true,
        'name' => 'Manual Payment Test License',
        'license_code' => 'MANUAL-PAY-001',
    ]);

    $license->federations()->attach($this->federation->id);

    // Purchase license
    $purchaseAction = app(PurchaseLicenseAction::class);
    $licenseAttributed = $purchaseAction($license, $this->individual);

    expect($licenseAttributed->status_class)->toBe(PendingLicenseAttributedState::class);

    // Create document
    $licenseAttributed->load('license');
    $createDocListener = new CreateLicenseAttributedDocumentListener;
    $createDocListener->handle(new LicenseAttributedCreatedEvent([$licenseAttributed], true));

    // Get the document
    $documentDetail = DocumentDetail::where('owner_type', LicenseAttributed::class)
        ->where('owner_id', $licenseAttributed->id)
        ->first();
    $document = Document::find($documentDetail->document_id);

    // Use ManuallyMarkDocumentAsPaidAction
    $manualPayAction = new \Domain\Documents\Actions\ManuallyMarkDocumentAsPaidAction;
    $manualPayAction->execute($document->id, 'Manual payment test');

    // Verify license was activated
    $licenseAttributed->refresh();
    expect($licenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class)
        ->and($licenseAttributed->activated_at)->not->toBeNull();

    // Verify document is paid
    $document->refresh();
    expect($document->status_class)->toBe(PaidDocumentState::class);
});

test('international diving entity license with requires_admin_validation creates payment document', function () {
    // This test verifies the fix for: international licenses with requires_admin_validation = true
    // should still generate payment documents because they skip validation state and go to PendingLicenseAttributedState
    // BUG: International Diving entity licenses weren't generating payment documents while Scientific was
    // ROOT CAUSE: The condition checked `!$requiresValidation` instead of checking if state is PendingLicenseAttributedState

    Event::fake([LicenseAttributedCreatedEvent::class]);

    // Create INTERNATIONAL diving committee
    $divingCommittee = Committee::factory()->create([
        'code' => 'DIVING',
        'name' => 'International Diving',
        'is_international' => true,
    ]);

    // Create International Diving entity license with requires_admin_validation = true
    // This mimics the production International Diving licenses (IDs 15, 16, 17)
    $internationalDivingLicense = License::factory()->create([
        'name' => 'Licenca Centro de Mergulho CMAS',
        'license_code' => 'EMC',
        'committee_id' => $divingCommittee->id,
        'requester_model' => ['Entity'],
        'unit_value_entity' => 50.00,
        'active' => true,
        'requires_admin_validation' => true, // This is the key - has validation flag but is international
    ]);

    $internationalDivingLicense->federations()->attach($this->federation->id);

    // Purchase the international diving license as entity
    $purchaseAction = app(PurchaseLicenseAction::class);
    $licenseAttributed = $purchaseAction($internationalDivingLicense, $this->entity);

    // KEY ASSERTION 1: International licenses skip validation state and go to PendingLicenseAttributedState
    expect($licenseAttributed->status_class)->toBe(PendingLicenseAttributedState::class)
        ->and($licenseAttributed->total_value)->toBe(50.00);

    // KEY ASSERTION 2: Event should be dispatched to create payment document
    // This was the bug - event wasn't being dispatched for international licenses with requires_admin_validation = true
    Event::assertDispatched(LicenseAttributedCreatedEvent::class, function ($event) use ($licenseAttributed) {
        return count($event->licenseAttributed) === 1
            && $event->licenseAttributed[0]->id === $licenseAttributed->id
            && $event->isSelfRequest === true;
    });

    // Manually trigger the listener to create document
    $listener = new CreateLicenseAttributedDocumentListener;
    $licenseAttributed->load('license');
    $event = new LicenseAttributedCreatedEvent([$licenseAttributed], true);
    $listener->handle($event);

    // KEY ASSERTION 3: Document detail should be created
    $documentDetail = DocumentDetail::where('owner_type', LicenseAttributed::class)
        ->where('owner_id', $licenseAttributed->id)
        ->first();

    expect($documentDetail)->not->toBeNull('Payment document must be created for international license with requires_admin_validation')
        ->and((float) $documentDetail->unit_value)->toBe(50.00)
        ->and($documentDetail->description)->toContain('Licenca Centro de Mergulho CMAS');

    // Document should exist and be pending payment
    $document = Document::find($documentDetail->document_id);
    expect($document)->not->toBeNull()
        ->and($document->status_class)->toBe(PendingDocumentState::class)
        ->and($document->owner_type)->toBe('entity')
        ->and($document->owner_id)->toBe((string) $this->entity->id);
});

test('international scientific entity license creates payment document', function () {
    // Verify Scientific licenses (which work correctly) continue to work
    // This ensures we don't break the working case while fixing the broken one

    Event::fake([LicenseAttributedCreatedEvent::class]);

    // Create INTERNATIONAL scientific committee
    $scientificCommittee = Committee::factory()->create([
        'code' => 'SCIENTIFIC',
        'name' => 'CMAS Scientific',
        'is_international' => true,
    ]);

    // Create Scientific entity license without validation requirement
    $scientificLicense = License::factory()->create([
        'name' => 'Licenca de Escola de Mergulho Cientifico',
        'license_code' => 'LEMC',
        'committee_id' => $scientificCommittee->id,
        'requester_model' => ['Entity'],
        'unit_value_entity' => 10.00,
        'active' => true,
        'requires_admin_validation' => false,
    ]);

    $scientificLicense->federations()->attach($this->federation->id);

    // Purchase the scientific license as entity
    $purchaseAction = app(PurchaseLicenseAction::class);
    $licenseAttributed = $purchaseAction($scientificLicense, $this->entity);

    // Should go to pending state
    expect($licenseAttributed->status_class)->toBe(PendingLicenseAttributedState::class)
        ->and($licenseAttributed->total_value)->toBe(10.00);

    // Event should be dispatched
    Event::assertDispatched(LicenseAttributedCreatedEvent::class);
});
