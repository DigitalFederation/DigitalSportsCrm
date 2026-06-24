<?php

use App\Models\Committee;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Actions\GetEligibleInstructorsQueryAction;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function setupDivingInstructor(Individual $individual, Federation $mainFederation, Entity $entity, Committee $committee, ProfessionalRole $instructorRole): void
{
    // Active instructor professional role at entity
    $individual->professionalRoleEntities()->create([
        'entity_id' => $entity->id,
        'professional_role_id' => $instructorRole->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);

    // Active certification attributed for DIVING committee with INSTRUCTOR role
    $certification = \Domain\Certifications\Models\Certification::factory()->create([
        'committee_id' => $committee->id,
        'professional_role_id' => $instructorRole->id,
    ]);

    CertificationAttributed::factory()->create([
        'certification_id' => $certification->id,
        'federation_id' => $mainFederation->id,
        'entity_id' => $entity->id,
        'individual_id' => $individual->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    // Active license attributed for DIVING committee with INSTRUCTOR role
    $license = License::factory()->create([
        'committee_id' => $committee->id,
        'professional_role_id' => $instructorRole->id,
    ]);

    LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'federation_id' => $mainFederation->id,
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);
}

beforeEach(function () {
    $this->mainFederation = Federation::factory()->create([
        'is_default_federation' => true,
        'is_local' => false,
    ]);

    $this->committee = Committee::factory()->create(['code' => 'DIVING']);
    $this->instructorRole = ProfessionalRole::factory()->create(['role' => 'INSTRUCTOR']);
    $this->entity = Entity::factory()->create();
    $this->entity->federations()->attach($this->mainFederation->id);
});

test('eligible instructor with all conditions met appears in DIVING query', function () {
    $individual = Individual::factory()->create();
    $individual->entities()->attach($this->entity->id);

    setupDivingInstructor($individual, $this->mainFederation, $this->entity, $this->committee, $this->instructorRole);

    $action = new GetEligibleInstructorsQueryAction;
    $results = $action(
        schoolId: $this->entity->id,
        committeeCode: 'diving',
    )->get();

    expect($results->pluck('id'))->toContain($individual->id);
});

test('individual without active instructor role at entity is excluded from DIVING query', function () {
    $individual = Individual::factory()->create();
    $individual->entities()->attach($this->entity->id);

    // No professional role at entity (only association)

    // Active certification and license
    $certification = \Domain\Certifications\Models\Certification::factory()->create([
        'committee_id' => $this->committee->id,
        'professional_role_id' => $this->instructorRole->id,
    ]);

    CertificationAttributed::factory()->create([
        'certification_id' => $certification->id,
        'federation_id' => $this->mainFederation->id,
        'entity_id' => $this->entity->id,
        'individual_id' => $individual->id,
        'status_class' => ActiveCertificationAttributedState::class,
    ]);

    $license = License::factory()->create([
        'committee_id' => $this->committee->id,
        'professional_role_id' => $this->instructorRole->id,
    ]);

    LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'federation_id' => $this->mainFederation->id,
        'model_type' => 'individual',
        'model_id' => $individual->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    $action = new GetEligibleInstructorsQueryAction;
    $results = $action(
        schoolId: $this->entity->id,
        committeeCode: 'diving',
    )->get();

    expect($results->pluck('id'))->not->toContain($individual->id);
});
