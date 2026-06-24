<?php

use App\Enums\MembershipTargetType;
use App\Enums\OfficialDocumentTypeEnum;
use Domain\Individuals\Models\Individual;
use Domain\Insurance\Models\InsurancePlan;
use Domain\Memberships\Models\MembershipPackage;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Domain\OfficialDocuments\States\ActiveOfficialDocumentState;
use Domain\OfficialDocuments\States\PendingOfficialDocumentState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('insurance plan checks if individual has required document', function () {
    $individual = Individual::factory()->create();

    $insurancePlan = InsurancePlan::factory()->create([
        'name' => 'Test Insurance Plan',
        'requires_official_document' => true,
        'required_document_type' => OfficialDocumentTypeEnum::ProfessionalLiabilityInsurance->value,
    ]);

    expect($insurancePlan->individualHasRequiredDocument($individual))->toBeFalse();

    OfficialDocument::factory()->create([
        'individual_id' => $individual->id,
        'type' => OfficialDocumentTypeEnum::ProfessionalLiabilityInsurance,
        'status_class' => ActiveOfficialDocumentState::class,
        'expiry_date' => now()->addYear(),
    ]);

    expect($insurancePlan->individualHasRequiredDocument($individual))->toBeTrue();
});

test('insurance plan rejects non-active documents', function () {
    $individual = Individual::factory()->create();

    $insurancePlan = InsurancePlan::factory()->create([
        'requires_official_document' => true,
        'required_document_type' => OfficialDocumentTypeEnum::ProfessionalLiabilityInsurance->value,
    ]);

    OfficialDocument::factory()->create([
        'individual_id' => $individual->id,
        'type' => OfficialDocumentTypeEnum::ProfessionalLiabilityInsurance,
        'status_class' => PendingOfficialDocumentState::class,
        'expiry_date' => now()->addYear(),
    ]);

    expect($insurancePlan->individualHasRequiredDocument($individual))->toBeFalse();
});

test('membership package checks document requirements', function () {
    $individual = Individual::factory()->create();

    $insurancePlan = InsurancePlan::factory()->create([
        'requires_official_document' => true,
        'required_document_type' => OfficialDocumentTypeEnum::InsuranceAthlete->value,
    ]);

    $membershipPackage = MembershipPackage::factory()->create([
        'target_type' => MembershipTargetType::INDIVIDUAL,
    ]);

    $membershipPackage->insurancePlans()->attach($insurancePlan);

    expect($membershipPackage->individualMeetsDocumentRequirements($individual))->toBeFalse();

    OfficialDocument::factory()->create([
        'individual_id' => $individual->id,
        'type' => OfficialDocumentTypeEnum::InsuranceAthlete,
        'status_class' => ActiveOfficialDocumentState::class,
        'expiry_date' => now()->addYear(),
    ]);

    expect($membershipPackage->individualMeetsDocumentRequirements($individual))->toBeTrue();
});

test('membership package returns missing requirements', function () {
    $individual = Individual::factory()->create();

    $insurancePlan = InsurancePlan::factory()->create([
        'name' => 'Athlete Insurance',
        'requires_official_document' => true,
        'required_document_type' => OfficialDocumentTypeEnum::InsuranceAthlete->value,
    ]);

    $membershipPackage = MembershipPackage::factory()->create([
        'target_type' => MembershipTargetType::INDIVIDUAL,
    ]);

    $membershipPackage->insurancePlans()->attach($insurancePlan);

    $missingRequirements = $membershipPackage->getMissingDocumentRequirements($individual);

    expect($missingRequirements)->toHaveCount(1)
        ->and($missingRequirements[0]['insurance_plan'])->toBe('Athlete Insurance')
        ->and($missingRequirements[0]['required_document_type'])->toBe(OfficialDocumentTypeEnum::InsuranceAthlete->value);
});
