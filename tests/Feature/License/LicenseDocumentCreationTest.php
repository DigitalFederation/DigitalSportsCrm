<?php

use App\Events\LicenseAttributedCreatedEvent;
use App\Listeners\CreateLicenseAttributedDocumentListener;
use App\Models\Committee;
use App\Models\User;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentType;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Actions\PurchaseLicenseAction;
use Domain\Licenses\Actions\PurchaseLicenseForGroupAction;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create required document types
    DocumentType::factory()->create(['code' => 'ORD', 'name' => 'Order']);

    // Create test data
    $this->user = User::factory()->create();
    $this->federation = Federation::factory()->create();
    $this->committee = Committee::factory()->create();
});

it('creates document for individual when individual purchases license for themselves', function () {
    $individual = Individual::factory()->create();
    $individual->federations()->attach($this->federation);

    $license = License::factory()->create([
        'committee_id' => $this->committee->id,
        'requester_model' => Individual::class,
        'unit_value_individual' => 100,
        'active' => true,
    ]);

    // Create license attributed (simulating what PurchaseLicenseAction does)
    $licenseAttributed = LicenseAttributed::create([
        'status_class' => PendingLicenseAttributedState::class,
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'license_name' => $license->name,
        'holder_name' => $individual->name,
        'federation_name' => $this->federation->legal_name,
        'total_value' => 100,
        'requester_model_type' => 'individual',
        'request_type' => 'direct',
    ]);

    // Trigger the event (isSelfRequest = true)
    $event = new LicenseAttributedCreatedEvent([$licenseAttributed], true);
    $listener = new CreateLicenseAttributedDocumentListener;
    $listener->handle($event);

    // Verify document was created for the individual
    $document = Document::where('owner_type', 'individual')
        ->where('owner_id', $individual->id)
        ->first();

    expect($document)
        ->not->toBeNull()
        ->and($document->owner_type)->toBe('individual')
        ->and($document->owner_id)->toBe((string) $individual->id)
        ->and($document->details()->count())->toBe(1);
});

it('creates document for entity when entity purchases license for themselves', function () {
    $this->actingAs($this->user);
    $entity = Entity::factory()->create();
    $entity->federations()->attach($this->federation);

    $license = License::factory()->create([
        'committee_id' => $this->committee->id,
        'requester_model' => Entity::class,
        'unit_value_entity' => 200,
        'active' => true,
    ]);

    // Create license attributed (simulating what PurchaseLicenseAction does)
    $licenseAttributed = LicenseAttributed::create([
        'status_class' => PendingLicenseAttributedState::class,
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'entity',
        'model_id' => $entity->id,
        'license_name' => $license->name,
        'holder_name' => $entity->name,
        'federation_name' => $this->federation->legal_name,
        'total_value' => 200,
        'requester_model_type' => 'entity',
        'request_type' => 'direct',
    ]);

    // Trigger the event (isSelfRequest = true)
    $event = new LicenseAttributedCreatedEvent([$licenseAttributed], true);
    $listener = new CreateLicenseAttributedDocumentListener;
    $listener->handle($event);

    // Verify document was created for the entity
    $document = Document::where('owner_type', 'entity')
        ->where('owner_id', $entity->id)
        ->first();

    expect($document)
        ->not->toBeNull()
        ->and($document->owner_type)->toBe('entity')
        ->and($document->owner_id)->toBe((string) $entity->id)
        ->and($document->details()->count())->toBe(1);
});

it('creates document for entity when entity purchases licenses for members', function () {
    $this->actingAs($this->user);
    $entity = Entity::factory()->create();
    $entity->federations()->attach($this->federation);

    $individual1 = Individual::factory()->create();
    $individual2 = Individual::factory()->create();
    $entity->individuals()->attach([$individual1->id, $individual2->id]);

    $license = License::factory()->create([
        'committee_id' => $this->committee->id,
        'requester_model' => Individual::class,
        'allow_entity_group_request' => true,
        'unit_value_entity' => 75,
        'active' => true,
    ]);

    // Create license attributed records (simulating what PurchaseLicenseForGroupAction does)
    $licenseAttributed1 = LicenseAttributed::create([
        'status_class' => PendingLicenseAttributedState::class,
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'individual',
        'model_id' => $individual1->id,
        'license_name' => $license->name,
        'holder_name' => $individual1->name,
        'federation_name' => $this->federation->legal_name,
        'total_value' => 75,
        'requester_model_type' => 'entity',
        'requested_by_id' => $entity->id,
        'request_type' => 'entity_group',
    ]);

    $licenseAttributed2 = LicenseAttributed::create([
        'status_class' => PendingLicenseAttributedState::class,
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'individual',
        'model_id' => $individual2->id,
        'license_name' => $license->name,
        'holder_name' => $individual2->name,
        'federation_name' => $this->federation->legal_name,
        'total_value' => 75,
        'requester_model_type' => 'entity',
        'requested_by_id' => $entity->id,
        'request_type' => 'entity_group',
    ]);

    // Trigger the event (isSelfRequest = false)
    $event = new LicenseAttributedCreatedEvent([$licenseAttributed1, $licenseAttributed2], false);
    $listener = new CreateLicenseAttributedDocumentListener;
    $listener->handle($event);

    // Verify document was created for the entity (not the individuals)
    $document = Document::where('owner_type', 'entity')
        ->where('owner_id', $entity->id)
        ->first();

    expect($document)
        ->not->toBeNull()
        ->and($document->owner_type)->toBe('entity')
        ->and($document->owner_id)->toBe((string) $entity->id)
        ->and($document->details()->count())->toBe(2); // Two licenses in one document

    // Verify no documents were created for the individuals
    $individualDocuments = Document::where('owner_type', 'individual')
        ->whereIn('owner_id', [$individual1->id, $individual2->id])
        ->count();

    expect($individualDocuments)->toBe(0);
});

it('uses correct pricing in documents based on who is purchasing', function () {
    $this->actingAs($this->user);
    $entity = Entity::factory()->create();
    $entity->federations()->attach($this->federation);

    $individual = Individual::factory()->create();
    $entity->individuals()->attach($individual);

    // Create license with different prices
    $license = License::factory()->create([
        'committee_id' => $this->committee->id,
        'requester_model' => 'All',
        'allow_entity_group_request' => true,
        'unit_value_individual' => 100,
        'unit_value_entity' => 75, // Entity gets better price
        'active' => true,
    ]);

    // Case 1: Individual purchases for themselves
    $individualLicenseAttributed = LicenseAttributed::create([
        'status_class' => PendingLicenseAttributedState::class,
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'license_name' => $license->name,
        'holder_name' => $individual->name,
        'federation_name' => $this->federation->legal_name,
        'total_value' => 100, // Individual price
        'requester_model_type' => 'individual',
        'request_type' => 'direct',
    ]);

    $event1 = new LicenseAttributedCreatedEvent([$individualLicenseAttributed], true);
    $listener = new CreateLicenseAttributedDocumentListener;
    $listener->handle($event1);

    $individualDocument = Document::where('owner_type', 'individual')
        ->where('owner_id', $individual->id)
        ->first();

    expect($individualDocument)->not->toBeNull();
    expect((float) $individualDocument->details()->first()->total_value)->toBe(100.0);

    // Case 2: Entity purchases for the same individual
    $entityLicenseAttributed = LicenseAttributed::create([
        'status_class' => PendingLicenseAttributedState::class,
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'license_name' => $license->name,
        'holder_name' => $individual->name,
        'federation_name' => $this->federation->legal_name,
        'total_value' => 75, // Entity price
        'requester_model_type' => 'entity',
        'requested_by_id' => $entity->id,
        'request_type' => 'entity_group',
    ]);

    $event2 = new LicenseAttributedCreatedEvent([$entityLicenseAttributed], false);
    $listener->handle($event2);

    $entityDocument = Document::where('owner_type', 'entity')
        ->where('owner_id', $entity->id)
        ->first();

    expect($entityDocument)->not->toBeNull();
    expect((float) $entityDocument->details()->first()->total_value)->toBe(75.0);
});

it('handles morph alias in requester_model_type correctly', function () {
    $this->actingAs($this->user);
    $entity = Entity::factory()->create();
    $entity->federations()->attach($this->federation);

    $license = License::factory()->create([
        'committee_id' => $this->committee->id,
        'requester_model' => Entity::class,
        'unit_value' => 30.00,
        'unit_value_entity' => 50.00,
        'active' => true,
    ]);

    // Create license attributed with morph alias 'entity' instead of full class name
    $licenseAttributed = LicenseAttributed::create([
        'status_class' => PendingLicenseAttributedState::class,
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'entity',
        'model_id' => $entity->id,
        'license_name' => $license->name,
        'holder_name' => $entity->name,
        'federation_name' => $this->federation->legal_name,
        'total_value' => 50.00,
        'requester_model_type' => 'entity', // Using morph alias
        'request_type' => 'direct',
    ]);

    // Trigger the event
    $event = new LicenseAttributedCreatedEvent([$licenseAttributed], true);
    $listener = new CreateLicenseAttributedDocumentListener;
    $listener->handle($event);

    // Verify document was created with correct values
    $document = Document::where('owner_type', 'entity')
        ->where('owner_id', $entity->id)
        ->first();

    expect($document)->not->toBeNull()
        ->and($document->total_value)->toBe('50.00')
        ->and($document->net_value)->toBe('50.00');

    $detail = $document->details()->first();
    expect($detail)->not->toBeNull()
        ->and($detail->unit_value)->toBe('50.00');
});

it('uses fallback total_value when license unit values are null', function () {
    $this->actingAs($this->user);
    $entity = Entity::factory()->create();
    $entity->federations()->attach($this->federation);

    // Create license with no unit values set
    $license = License::factory()->create([
        'committee_id' => $this->committee->id,
        'requester_model' => Entity::class,
        'unit_value' => null,
        'unit_value_entity' => null,
        'unit_value_individual' => null,
        'active' => true,
    ]);

    // Create license attributed with total_value that should be used as fallback
    $licenseAttributed = LicenseAttributed::create([
        'status_class' => PendingLicenseAttributedState::class,
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'entity',
        'model_id' => $entity->id,
        'license_name' => $license->name,
        'holder_name' => $entity->name,
        'federation_name' => $this->federation->legal_name,
        'total_value' => 75.00, // This should be used as fallback
        'requester_model_type' => 'entity',
        'request_type' => 'direct',
    ]);

    // Trigger the event
    $event = new LicenseAttributedCreatedEvent([$licenseAttributed], true);
    $listener = new CreateLicenseAttributedDocumentListener;
    $listener->handle($event);

    // Verify document was created with fallback value
    $document = Document::where('owner_type', 'entity')
        ->where('owner_id', $entity->id)
        ->first();

    expect($document)->not->toBeNull()
        ->and($document->total_value)->toBe('75.00');

    $detail = $document->details()->first();
    expect($detail)->not->toBeNull()
        ->and($detail->unit_value)->toBe('75.00');
});

it('handles individual morph alias correctly', function () {
    $individual = Individual::factory()->create();
    $individual->federations()->attach($this->federation);

    $license = License::factory()->create([
        'committee_id' => $this->committee->id,
        'requester_model' => Individual::class,
        'unit_value' => 30.00,
        'unit_value_individual' => 40.00,
        'active' => true,
    ]);

    // Create license attributed with morph alias 'individual'
    $licenseAttributed = LicenseAttributed::create([
        'status_class' => PendingLicenseAttributedState::class,
        'license_id' => $license->id,
        'federation_id' => $this->federation->id,
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'license_name' => $license->name,
        'holder_name' => $individual->name,
        'federation_name' => $this->federation->legal_name,
        'total_value' => 40.00,
        'requester_model_type' => 'individual', // Using morph alias
        'request_type' => 'direct',
    ]);

    // Trigger the event
    $event = new LicenseAttributedCreatedEvent([$licenseAttributed], true);
    $listener = new CreateLicenseAttributedDocumentListener;
    $listener->handle($event);

    // Verify document was created with correct individual pricing
    $document = Document::where('owner_type', 'individual')
        ->where('owner_id', $individual->id)
        ->first();

    expect($document)->not->toBeNull()
        ->and($document->total_value)->toBe('40.00');

    $detail = $document->details()->first();
    expect($detail)->not->toBeNull()
        ->and($detail->unit_value)->toBe('40.00');
});
