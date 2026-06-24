<?php

use App\Models\Committee;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\PendingCertificationAttributedState;
use Domain\Documents\Actions\BuildCertificationDocumentDetailAction;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;
use Domain\Documents\Models\DocumentType;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->action = new BuildCertificationDocumentDetailAction;
    $this->federation = Federation::factory()->create();
    $this->committee = Committee::factory()->create();
    $this->entity = Entity::factory()->create();
    $this->individual = Individual::factory()->create();

    // Create document type
    DocumentType::factory()->create(['code' => 'ORD']);
});

it('creates document detail with certification data', function () {
    $certification = Certification::factory()->create([
        'committee_id' => $this->committee->id,
        'name' => 'CMAS Open Water Diver',
        'acronym' => 'OWD',
        'digital_price' => 75.00,
    ]);

    $certificationAttributed = CertificationAttributed::factory()->create([
        'certification_id' => $certification->id,
        'federation_id' => $this->federation->id,
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'holder_name' => 'John Doe',
        'status_class' => PendingCertificationAttributedState::class,
    ]);

    // Create a document for the test
    $document = Document::factory()->create([
        'owner_type' => Individual::class,
        'owner_id' => $this->individual->id,
    ]);

    $result = ($this->action)($certificationAttributed, $document, 75.00);

    expect($result)
        ->toBeInstanceOf(DocumentDetail::class)
        ->and($result->document_id)->toBe($document->id)
        ->and($result->quantity)->toBe(1)
        ->and($result->unit_value)->toBe(75.00)
        ->and($result->total_value)->toBe(75.00)
        ->and($result->owner_type)->toBe(CertificationAttributed::class)
        ->and($result->owner_id)->toBe($certificationAttributed->id)
        ->and($result->description)->toContain('CMAS Open Water Diver')
        ->and($result->description)->toContain('OWD')
        ->and($result->description)->toContain('John Doe');
});

it('includes moloni_reference in document detail when certification has one', function () {
    $certification = Certification::factory()->create([
        'committee_id' => $this->committee->id,
        'name' => 'Advanced Diver',
        'acronym' => 'AD',
        'digital_price' => 100.00,
        'moloni_reference' => 'CERT-AD-001',
    ]);

    $certificationAttributed = CertificationAttributed::factory()->create([
        'certification_id' => $certification->id,
        'federation_id' => $this->federation->id,
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'holder_name' => 'Jane Smith',
        'status_class' => PendingCertificationAttributedState::class,
    ]);

    $document = Document::factory()->create([
        'owner_type' => Individual::class,
        'owner_id' => $this->individual->id,
    ]);

    $result = ($this->action)($certificationAttributed, $document, 100.00);

    expect($result)
        ->toBeInstanceOf(DocumentDetail::class)
        ->and($result->reference)->toBe('CERT-AD-001');
});

it('returns null reference when certification has no moloni_reference', function () {
    $certification = Certification::factory()->create([
        'committee_id' => $this->committee->id,
        'name' => 'Basic Diver',
        'acronym' => 'BD',
        'digital_price' => 50.00,
        'moloni_reference' => null,
    ]);

    $certificationAttributed = CertificationAttributed::factory()->create([
        'certification_id' => $certification->id,
        'federation_id' => $this->federation->id,
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'status_class' => PendingCertificationAttributedState::class,
    ]);

    $document = Document::factory()->create([
        'owner_type' => Individual::class,
        'owner_id' => $this->individual->id,
    ]);

    $result = ($this->action)($certificationAttributed, $document, 50.00);

    expect($result)
        ->toBeInstanceOf(DocumentDetail::class)
        ->and($result->reference)->toBeNull();
});

it('handles certification without acronym', function () {
    $certification = Certification::factory()->create([
        'committee_id' => $this->committee->id,
        'name' => 'Special Certification',
        'acronym' => null,
        'digital_price' => 80.00,
        'moloni_reference' => 'CERT-SPECIAL',
    ]);

    $certificationAttributed = CertificationAttributed::factory()->create([
        'certification_id' => $certification->id,
        'federation_id' => $this->federation->id,
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'holder_name' => null,
        'status_class' => PendingCertificationAttributedState::class,
    ]);

    $document = Document::factory()->create([
        'owner_type' => Individual::class,
        'owner_id' => $this->individual->id,
    ]);

    $result = ($this->action)($certificationAttributed, $document, 80.00);

    expect($result)
        ->toBeInstanceOf(DocumentDetail::class)
        ->and($result->description)->toContain('Special Certification')
        ->and($result->description)->toContain('N/A')
        ->and($result->reference)->toBe('CERT-SPECIAL');
});
