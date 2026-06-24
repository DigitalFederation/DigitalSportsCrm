<?php

use App\Enums\OfficialDocumentTypeEnum;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Actions\ValidateLicenseDocumentRequirementsAction;
use Domain\Licenses\Models\License;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Domain\OfficialDocuments\States\PendingOfficialDocumentState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function licenseRequiringDocument(string $type): License
{
    return License::factory()->create([
        'requires_official_documents' => true,
        'required_document_types' => [$type],
    ]);
}

test('an active document stored only on individual_id satisfies a license requirement', function () {
    $individual = Individual::factory()->create();
    $license = licenseRequiringDocument(OfficialDocumentTypeEnum::MedicalStatement->value);

    // Real uploads only populate the legacy individual_id column (owner_type/owner_id
    // stay null). Create the document the same way to guard against the polymorphic
    // owner_type/owner_id query regression that reported every document as missing.
    OfficialDocument::factory()->active()->create([
        'individual_id' => $individual->id,
        'type' => OfficialDocumentTypeEnum::MedicalStatement,
        'expiry_date' => now()->addYear(),
    ]);

    $result = (new ValidateLicenseDocumentRequirementsAction)($license, $individual);

    expect($result['is_valid'])->toBeTrue()
        ->and($result['missing_documents'])->toBeEmpty();
});

test('an individual without the required document is reported missing', function () {
    $individual = Individual::factory()->create();
    $license = licenseRequiringDocument(OfficialDocumentTypeEnum::MedicalStatement->value);

    $result = (new ValidateLicenseDocumentRequirementsAction)($license, $individual);

    expect($result['is_valid'])->toBeFalse()
        ->and($result['missing_documents'])->toContain(OfficialDocumentTypeEnum::MedicalStatement->value);
});

test('a pending (non-active) document does not satisfy the requirement', function () {
    $individual = Individual::factory()->create();
    $license = licenseRequiringDocument(OfficialDocumentTypeEnum::MedicalStatement->value);

    OfficialDocument::factory()->create([
        'individual_id' => $individual->id,
        'type' => OfficialDocumentTypeEnum::MedicalStatement,
        'status_class' => PendingOfficialDocumentState::class,
        'expiry_date' => now()->addYear(),
    ]);

    $result = (new ValidateLicenseDocumentRequirementsAction)($license, $individual);

    expect($result['is_valid'])->toBeFalse();
});

test('a license that requires no documents is valid for any individual', function () {
    $individual = Individual::factory()->create();
    $license = License::factory()->create([
        'requires_official_documents' => false,
        'required_document_types' => [],
    ]);

    $result = (new ValidateLicenseDocumentRequirementsAction)($license, $individual);

    expect($result['is_valid'])->toBeTrue();
});
