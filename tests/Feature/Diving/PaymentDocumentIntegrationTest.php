<?php

use App\Events\ActivateAfterPayment;
use Domain\Documents\Actions\ManuallyMarkDocumentAsPaidAction;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;
use Domain\Documents\Models\DocumentType;
use Domain\Documents\States\PaidDocumentState;
use Domain\Documents\States\PendingDocumentState;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Domain\Payments\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    DocumentType::factory()->create(['code' => 'ORD', 'name' => 'Order']);
    DocumentType::factory()->create(['code' => 'PAY', 'name' => 'Payment']);
    DocumentType::factory()->create(['code' => 'INV', 'name' => 'Invoice']);

    PaymentMethod::factory()->create([
        'name' => 'Offline Payment',
        'driver' => 'offline',
        'handler' => 'Domain\Payments\Handlers\OfflinePaymentHandler',
    ]);

    $this->federation = Federation::factory()->create([
        'is_default_federation' => true,
    ]);
});

test('payment document triggers ActivateAfterPayment event when marked as paid', function () {
    Event::fake([ActivateAfterPayment::class]);

    $individual = Individual::factory()->create();

    $document = Document::factory()->create([
        'status_class' => PendingDocumentState::class,
        'owner_type' => 'individual',
        'owner_id' => $individual->id,
        'total_value' => 100.00,
        'type_id' => DocumentType::where('code', 'ORD')->first()->id,
    ]);

    $markAsPaidAction = new ManuallyMarkDocumentAsPaidAction;
    $markAsPaidAction->execute($document->id);

    Event::assertDispatched(ActivateAfterPayment::class);

    $document->refresh();
    expect($document->status_class)->toBe(PaidDocumentState::class);
});

test('document with license detail activates license when paid', function () {
    $individual = Individual::factory()->create();

    $license = License::factory()->create([
        'requester_model' => ['Individual'],
        'unit_value_individual' => 100.00,
        'active' => true,
    ]);
    $license->federations()->attach($this->federation->id);

    $licenseAttributed = LicenseAttributed::factory()->create([
        'status_class' => PendingLicenseAttributedState::class,
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'total_value' => 100.00,
    ]);

    $document = Document::factory()->create([
        'status_class' => PendingDocumentState::class,
        'owner_type' => 'individual',
        'owner_id' => $individual->id,
        'total_value' => 100.00,
        'type_id' => DocumentType::where('code', 'ORD')->first()->id,
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_id' => $licenseAttributed->id,
        'owner_type' => LicenseAttributed::class,
        'unit_value' => 100.00,
        'total_value' => 100.00,
    ]);

    $markAsPaidAction = new ManuallyMarkDocumentAsPaidAction;
    $markAsPaidAction->execute($document->id);

    $document->refresh();
    $licenseAttributed->refresh();

    expect($document->status_class)->toBe(PaidDocumentState::class);
    expect($licenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class);
    expect($licenseAttributed->activated_at)->not->toBeNull();
});

test('entity document with license detail activates license when paid', function () {
    $entity = Entity::factory()->create();

    $license = License::factory()->create([
        'requester_model' => ['Entity'],
        'unit_value_entity' => 250.00,
        'active' => true,
    ]);
    $license->federations()->attach($this->federation->id);

    $licenseAttributed = LicenseAttributed::factory()->create([
        'status_class' => PendingLicenseAttributedState::class,
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'entity',
        'model_id' => $entity->id,
        'total_value' => 250.00,
    ]);

    $document = Document::factory()->create([
        'status_class' => PendingDocumentState::class,
        'owner_type' => 'entity',
        'owner_id' => $entity->id,
        'total_value' => 250.00,
        'type_id' => DocumentType::where('code', 'ORD')->first()->id,
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_id' => $licenseAttributed->id,
        'owner_type' => LicenseAttributed::class,
        'unit_value' => 250.00,
        'total_value' => 250.00,
    ]);

    $markAsPaidAction = new ManuallyMarkDocumentAsPaidAction;
    $markAsPaidAction->execute($document->id);

    $document->refresh();
    $licenseAttributed->refresh();

    expect($document->status_class)->toBe(PaidDocumentState::class);
    expect($licenseAttributed->status_class)->toBe(ActiveLicenseAttributedState::class);
});

test('document without activatable details still transitions to paid', function () {
    $individual = Individual::factory()->create();

    $document = Document::factory()->create([
        'status_class' => PendingDocumentState::class,
        'owner_type' => 'individual',
        'owner_id' => $individual->id,
        'total_value' => 50.00,
        'type_id' => DocumentType::where('code', 'ORD')->first()->id,
    ]);

    DocumentDetail::factory()->create([
        'document_id' => $document->id,
        'owner_id' => null,
        'owner_type' => null,
        'description' => 'Manual service fee',
        'unit_value' => 50.00,
        'total_value' => 50.00,
    ]);

    $markAsPaidAction = new ManuallyMarkDocumentAsPaidAction;
    $markAsPaidAction->execute($document->id);

    $document->refresh();
    expect($document->status_class)->toBe(PaidDocumentState::class);
});
