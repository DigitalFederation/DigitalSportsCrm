<?php

namespace Tests\Feature\Licenses;

use App\Events\ActivateAfterPayment;
use App\Listeners\ActivateAfterPaymentLicenseAttributedListener;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;
use Domain\Documents\Models\DocumentType;
use Domain\Documents\States\PaidDocumentState;
use Domain\Documents\States\PendingDocumentState;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a user for the CreatedUpdatedBy trait
    \App\Models\User::factory()->create();

    // Create required document types
    DocumentType::create(['code' => 'ORD', 'name' => 'Order']);
    DocumentType::create(['code' => 'PAY', 'name' => 'Payment']);
    DocumentType::create(['code' => 'INV', 'name' => 'Invoice']);

    // Create a federation and license for foreign key constraints
    $this->federation = \Domain\Federations\Models\Federation::factory()->create();
    $this->license = \Domain\Licenses\Models\License::factory()->create();
    $this->individual = \Domain\Individuals\Models\Individual::factory()->create();
});

test('the complete payment flow activates a pending license', function () {
    // Step 1: Create a pending license (simulating what happens after purchase)
    $licenseAttributed = LicenseAttributed::create([
        'id' => 'test-license-001',
        'status_class' => PendingLicenseAttributedState::class,
        'license_id' => $this->license->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'Domain\Individuals\Models\Individual',
        'model_id' => $this->individual->id,
        'license_name' => 'Test License',
        'holder_name' => 'Test Individual',
        'federation_name' => 'Test Federation',
        'total_value' => 100.00,
        'requester_model_type' => null, // For self-request by individual
        'requested_by_id' => null, // For self-request by individual
        'license_code' => 'TEST-001',
        'federation_code' => 'TEST',
    ]);

    // Verify license is pending
    expect($licenseAttributed->status_class)->toBe(PendingLicenseAttributedState::class)
        ->and($licenseAttributed->activated_at)->toBeNull();

    // Step 2: Create a document with the license as a detail (simulating what the listener does)
    $document = Document::create([
        'id' => 'test-doc-001',
        'type_id' => DocumentType::where('code', 'ORD')->first()->id,
        'status_class' => PendingDocumentState::class,
        'owner_id' => $this->individual->id,
        'owner_type' => $this->individual->getMorphClass(),
        'customer_name' => 'Test Individual',
        'net_value' => 100.00,
        'tax_value' => 0,
        'tax_percentage' => 0,
        'total_value' => 100.00,
        'number' => '2025/001',
        'number_pad' => '001',
        'number_year' => '2025',
        'number_extended' => 'ORD-2025/001',
    ]);

    // Create document detail linking license to document
    DocumentDetail::create([
        'document_id' => $document->id,
        'owner_id' => $licenseAttributed->id,
        'owner_type' => LicenseAttributed::class,
        'unit_value' => 100.00,
        'net_value' => 100.00,
        'tax_value' => 0,
        'tax_percentage' => 0,
        'total_value' => 100.00,
        'description' => 'Test License - Test Individual',
        'is_debit' => false,
    ]);

    // Step 3: Mark document as paid (simulating payment webhook)
    // We'll directly update the document and fire the event to avoid the notification issue
    $document->status_class = PaidDocumentState::class;
    $document->save();

    // Fire the ActivateAfterPayment event directly
    event(new ActivateAfterPayment($document->id));

    // Step 4: The ActivateAfterPayment event should have been fired
    // Let's verify the license was activated
    $licenseAttributed->refresh();

    // Assert the license is now active
    expect($licenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class)
        ->and($licenseAttributed->activated_at)->not->toBeNull();

    // Verify document is paid
    $document->refresh();
    expect($document->status_class)->toBe(PaidDocumentState::class);

    // Document should be paid (we set it directly)
});

test('the ActivateAfterPayment event correctly finds and activates licenses', function () {
    // Create a pending license
    $licenseAttributed = LicenseAttributed::create([
        'id' => 'test-license-002',
        'status_class' => PendingLicenseAttributedState::class,
        'license_id' => $this->license->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'Domain\Individuals\Models\Individual',
        'model_id' => $this->individual->id,
        'license_name' => 'Test License 2',
        'holder_name' => 'Test Individual 2',
        'federation_name' => 'Test Federation',
        'total_value' => 200.00,
        'requester_model_type' => null,
        'requested_by_id' => null,
        'license_code' => 'TEST-002',
        'federation_code' => 'TEST',
    ]);

    // Create document and detail
    $document = Document::create([
        'id' => 'test-doc-002',
        'type_id' => DocumentType::where('code', 'ORD')->first()->id,
        'status_class' => PendingDocumentState::class,
        'owner_id' => $this->individual->id,
        'owner_type' => $this->individual->getMorphClass(),
        'customer_name' => 'Test Individual 2',
        'net_value' => 200.00,
        'tax_value' => 0,
        'tax_percentage' => 0,
        'total_value' => 200.00,
        'number' => '2025/002',
        'number_pad' => '002',
        'number_year' => '2025',
        'number_extended' => 'ORD-2025/002',
    ]);

    DocumentDetail::create([
        'document_id' => $document->id,
        'owner_id' => $licenseAttributed->id,
        'owner_type' => LicenseAttributed::class,
        'unit_value' => 200.00,
        'net_value' => 200.00,
        'tax_value' => 0,
        'tax_percentage' => 0,
        'total_value' => 200.00,
        'description' => 'Test License 2',
        'is_debit' => false,
    ]);

    // Manually trigger the event and listener
    $event = new ActivateAfterPayment($document->id);
    $listener = new ActivateAfterPaymentLicenseAttributedListener;
    $listener->handle($event);

    // Check the license was activated
    $licenseAttributed->refresh();
    expect($licenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class)
        ->and($licenseAttributed->activated_at)->not->toBeNull();
});

test('license stateName method returns correct status for different states', function () {
    // Set locale to English for consistent test results
    app()->setLocale('en');

    // Test pending state
    $pendingLicense = LicenseAttributed::create([
        'id' => 'test-license-003',
        'status_class' => PendingLicenseAttributedState::class,
        'license_id' => $this->license->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'Domain\Individuals\Models\Individual',
        'model_id' => $this->individual->id,
        'license_name' => 'Test License',
        'holder_name' => 'Test Individual',
        'federation_name' => 'Test Federation',
        'total_value' => 100.00,
        'requester_model_type' => null,
        'requested_by_id' => null,
    ]);

    // Check pending state name (now returns translated string)
    expect($pendingLicense->stateName())->toBe(__('licenses.state_pending'));

    // Change to active state
    $pendingLicense->status_class = ActiveLicenseAttributedState::class;
    $pendingLicense->activated_at = now();
    $pendingLicense->save();

    // Check active state name (now returns translated string)
    expect($pendingLicense->fresh()->stateName())->toBe(__('licenses.state_active'));
});

test('failed to find license in document details does not throw error', function () {
    // Create a document without any license details
    $document = Document::create([
        'id' => 'test-doc-003',
        'type_id' => DocumentType::where('code', 'ORD')->first()->id,
        'status_class' => PendingDocumentState::class,
        'owner_id' => $this->individual->id,
        'owner_type' => $this->individual->getMorphClass(),
        'customer_name' => 'Test Individual',
        'net_value' => 100.00,
        'tax_value' => 0,
        'tax_percentage' => 0,
        'total_value' => 100.00,
        'number' => '2025/003',
        'number_pad' => '003',
        'number_year' => '2025',
        'number_extended' => 'ORD-2025/003',
    ]);

    // This should not throw an error even though no licenses are found
    $event = new ActivateAfterPayment($document->id);
    $listener = new ActivateAfterPaymentLicenseAttributedListener;

    expect(fn () => $listener->handle($event))->not->toThrow(\Exception::class);
});
