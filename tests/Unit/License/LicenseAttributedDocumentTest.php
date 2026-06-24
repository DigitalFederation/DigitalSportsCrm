<?php

use App\Events\LicenseAttributedCreatedEvent;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentType;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;

beforeEach(function () {
    // Seed the document types
    $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
});
it('creates a document when a licenseAttributed is created', function () {

    DocumentType::factory()->create(['code' => 'ORD']);
    $federation = \Domain\Federations\Models\Federation::factory()->create();
    $individual = Individual::factory()->create();
    $license = License::factory()->create(['unit_value' => 100.00]);
    $licenseAttributed = LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'federation_id' => $federation->id,
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'total_value' => $license->unit_value,
    ]);

    // Load the license relationship before dispatching the event
    $licenseAttributed->load('license');

    // LicenseAttributedCreatedEvent::dispatch([$licenseAttributed]);
    event(new LicenseAttributedCreatedEvent([$licenseAttributed], true));

    // Retrieve the potentially created document
    $document = Document::where('owner_id', $individual->id)
        ->where('owner_type', 'individual') // Use morph alias instead of class name
        ->first();

    // Assert a document was created
    $this->assertNotNull($document, 'Document was not created.');
});

it('creates a document for entity when purchasing licenses for members', function () {
    DocumentType::factory()->create(['code' => 'ORD']);

    $federation = \Domain\Federations\Models\Federation::factory()->create();
    $entity = \Domain\Entities\Models\Entity::factory()->create();
    $individual = Individual::factory()->create();
    $license = License::factory()->create(['unit_value' => 100.00]);

    // Create license attributed with entity as requester
    $licenseAttributed = LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'federation_id' => $federation->id,
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'total_value' => $license->unit_value,
        'requester_model_type' => 'entity',
        'requested_by_id' => $entity->id,
        'request_type' => 'entity_group',
    ]);

    // Load the license relationship before dispatching the event
    $licenseAttributed->load('license');

    // Fire event with isSelfRequest = false (entity purchasing for members)
    event(new LicenseAttributedCreatedEvent([$licenseAttributed], false));

    // Document should be created for the entity, not the individual
    $document = Document::where('owner_id', $entity->id)
        ->where('owner_type', 'entity') // Use morph alias instead of class name
        ->first();

    // Assert document was created for the entity
    $this->assertNotNull($document, 'Document was not created for the entity.');

    // Assert no document was created for the individual
    $individualDocument = Document::where('owner_id', $individual->id)
        ->where('owner_type', 'individual') // Use morph alias instead of class name
        ->first();
    $this->assertNull($individualDocument, 'Document should not be created for the individual.');
});
