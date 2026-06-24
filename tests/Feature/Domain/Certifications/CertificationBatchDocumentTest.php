<?php

use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\PendingCertificationAttributedState;
use Domain\Documents\Actions\AddCertificationDetailToDocumentAction;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentType;
use Domain\Documents\States\CanceledDocumentState;
use Domain\Documents\States\PaidDocumentState;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->mainFederation = Federation::factory()->create(['is_default_federation' => true]);

    DocumentType::factory()->create([
        'name' => 'Order',
        'code' => 'ORD',
        'prefix' => 'ORD',
    ]);

    $this->certification = Certification::factory()->create([
        'name' => 'Test Cert',
    ]);

    $this->entity = Entity::factory()->create();
    $this->action = app(AddCertificationDetailToDocumentAction::class);
});

describe('CertificationBatchDocument', function () {
    test('creates new document when no batch_id is set', function () {
        $individual = Individual::factory()->create();

        $certAttr = CertificationAttributed::factory()->create([
            'individual_id' => $individual->id,
            'certification_id' => $this->certification->id,
            'federation_id' => $this->mainFederation->id,
            'entity_id' => $this->entity->id,
            'status_class' => PendingCertificationAttributedState::class,
            'price_paid' => 25.00,
            'batch_id' => null,
        ]);

        ($this->action)($certAttr, 25.00);

        expect(Document::count())->toBe(1);
        expect(Document::first()->details()->count())->toBe(1);
    });

    test('creates new document for first approval in a batch', function () {
        $individual = Individual::factory()->create();
        $batchId = Str::uuid()->toString();

        $certAttr = CertificationAttributed::factory()->create([
            'individual_id' => $individual->id,
            'certification_id' => $this->certification->id,
            'federation_id' => $this->mainFederation->id,
            'entity_id' => $this->entity->id,
            'status_class' => PendingCertificationAttributedState::class,
            'price_paid' => 25.00,
            'batch_id' => $batchId,
        ]);

        ($this->action)($certAttr, 25.00);

        expect(Document::count())->toBe(1);

        $document = Document::first();
        expect($document->details()->count())->toBe(1)
            ->and((float) $document->total_value)->toBe(25.00);
    });

    test('adds detail to existing document for second approval in same batch', function () {
        $batchId = Str::uuid()->toString();

        $certAttr1 = CertificationAttributed::factory()->create([
            'individual_id' => Individual::factory()->create()->id,
            'certification_id' => $this->certification->id,
            'federation_id' => $this->mainFederation->id,
            'entity_id' => $this->entity->id,
            'status_class' => PendingCertificationAttributedState::class,
            'price_paid' => 25.00,
            'batch_id' => $batchId,
        ]);

        $certAttr2 = CertificationAttributed::factory()->create([
            'individual_id' => Individual::factory()->create()->id,
            'certification_id' => $this->certification->id,
            'federation_id' => $this->mainFederation->id,
            'entity_id' => $this->entity->id,
            'status_class' => PendingCertificationAttributedState::class,
            'price_paid' => 25.00,
            'batch_id' => $batchId,
        ]);

        ($this->action)($certAttr1, 25.00);
        ($this->action)($certAttr2, 25.00);

        expect(Document::count())->toBe(1);

        $document = Document::first();
        expect($document->details()->count())->toBe(2)
            ->and((float) $document->total_value)->toBe(50.00);
    });

    test('groups 4 approvals into single document with correct total', function () {
        $batchId = Str::uuid()->toString();
        $price = 30.00;

        for ($i = 0; $i < 4; $i++) {
            $certAttr = CertificationAttributed::factory()->create([
                'individual_id' => Individual::factory()->create()->id,
                'certification_id' => $this->certification->id,
                'federation_id' => $this->mainFederation->id,
                'entity_id' => $this->entity->id,
                'status_class' => PendingCertificationAttributedState::class,
                'price_paid' => $price,
                'batch_id' => $batchId,
            ]);

            ($this->action)($certAttr, $price);
        }

        expect(Document::count())->toBe(1);

        $document = Document::first();
        expect($document->details()->count())->toBe(4)
            ->and((float) $document->total_value)->toBe(120.00);
    });

    test('creates new document when existing batch document is already paid', function () {
        $batchId = Str::uuid()->toString();

        $certAttr1 = CertificationAttributed::factory()->create([
            'individual_id' => Individual::factory()->create()->id,
            'certification_id' => $this->certification->id,
            'federation_id' => $this->mainFederation->id,
            'entity_id' => $this->entity->id,
            'status_class' => PendingCertificationAttributedState::class,
            'price_paid' => 25.00,
            'batch_id' => $batchId,
        ]);

        ($this->action)($certAttr1, 25.00);

        // Mark document as paid
        $document = Document::first();
        $document->update(['status_class' => PaidDocumentState::class]);

        $certAttr2 = CertificationAttributed::factory()->create([
            'individual_id' => Individual::factory()->create()->id,
            'certification_id' => $this->certification->id,
            'federation_id' => $this->mainFederation->id,
            'entity_id' => $this->entity->id,
            'status_class' => PendingCertificationAttributedState::class,
            'price_paid' => 25.00,
            'batch_id' => $batchId,
        ]);

        ($this->action)($certAttr2, 25.00);

        expect(Document::count())->toBe(2);
    });

    test('creates new document when existing batch document is cancelled', function () {
        $batchId = Str::uuid()->toString();

        $certAttr1 = CertificationAttributed::factory()->create([
            'individual_id' => Individual::factory()->create()->id,
            'certification_id' => $this->certification->id,
            'federation_id' => $this->mainFederation->id,
            'entity_id' => $this->entity->id,
            'status_class' => PendingCertificationAttributedState::class,
            'price_paid' => 25.00,
            'batch_id' => $batchId,
        ]);

        ($this->action)($certAttr1, 25.00);

        // Mark document as cancelled
        $document = Document::first();
        $document->update(['status_class' => CanceledDocumentState::class]);

        $certAttr2 = CertificationAttributed::factory()->create([
            'individual_id' => Individual::factory()->create()->id,
            'certification_id' => $this->certification->id,
            'federation_id' => $this->mainFederation->id,
            'entity_id' => $this->entity->id,
            'status_class' => PendingCertificationAttributedState::class,
            'price_paid' => 25.00,
            'batch_id' => $batchId,
        ]);

        ($this->action)($certAttr2, 25.00);

        expect(Document::count())->toBe(2);
    });

    test('creates separate documents for individual submissions even with batch_id', function () {
        $batchId = Str::uuid()->toString();
        $individual = Individual::factory()->create();

        $certAttr1 = CertificationAttributed::factory()->create([
            'individual_id' => $individual->id,
            'certification_id' => $this->certification->id,
            'federation_id' => $this->mainFederation->id,
            'entity_id' => null,
            'status_class' => PendingCertificationAttributedState::class,
            'price_paid' => 25.00,
            'batch_id' => $batchId,
        ]);

        $certAttr2 = CertificationAttributed::factory()->create([
            'individual_id' => Individual::factory()->create()->id,
            'certification_id' => $this->certification->id,
            'federation_id' => $this->mainFederation->id,
            'entity_id' => null,
            'status_class' => PendingCertificationAttributedState::class,
            'price_paid' => 25.00,
            'batch_id' => $batchId,
        ]);

        ($this->action)($certAttr1, 25.00);
        ($this->action)($certAttr2, 25.00);

        expect(Document::count())->toBe(2);
    });

    test('creates separate documents for different batch_ids', function () {
        $batchId1 = Str::uuid()->toString();
        $batchId2 = Str::uuid()->toString();

        $certAttr1 = CertificationAttributed::factory()->create([
            'individual_id' => Individual::factory()->create()->id,
            'certification_id' => $this->certification->id,
            'federation_id' => $this->mainFederation->id,
            'entity_id' => $this->entity->id,
            'status_class' => PendingCertificationAttributedState::class,
            'price_paid' => 25.00,
            'batch_id' => $batchId1,
        ]);

        $certAttr2 = CertificationAttributed::factory()->create([
            'individual_id' => Individual::factory()->create()->id,
            'certification_id' => $this->certification->id,
            'federation_id' => $this->mainFederation->id,
            'entity_id' => $this->entity->id,
            'status_class' => PendingCertificationAttributedState::class,
            'price_paid' => 25.00,
            'batch_id' => $batchId2,
        ]);

        ($this->action)($certAttr1, 25.00);
        ($this->action)($certAttr2, 25.00);

        expect(Document::count())->toBe(2);
        expect(Document::first()->details()->count())->toBe(1);
        expect(Document::latest('id')->first()->details()->count())->toBe(1);
    });
});
