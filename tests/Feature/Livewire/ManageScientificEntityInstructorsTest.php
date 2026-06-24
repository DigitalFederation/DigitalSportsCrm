<?php

use App\Livewire\ManageEntityInstructors;
use App\Models\Committee;
use App\Models\Group;
use App\Models\User;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create ENTITY group
    $this->entityGroup = Group::factory()->create(['code' => 'ENTITY']);

    // Create SCIENTIFIC committee
    $this->committee = Committee::factory()->create([
        'code' => 'SCIENTIFIC',
        'name' => 'Scientific',
        'is_international' => true,
    ]);

    // Create DIVING committee (for cross-committee tests)
    $this->divingCommittee = Committee::factory()->create([
        'code' => 'DIVING',
        'name' => 'Diving',
        'is_international' => true,
    ]);

    // Create professional role with INSTRUCTOR role type for Scientific
    $this->instructorRole = ProfessionalRole::factory()->create([
        'role' => 'INSTRUCTOR',
        'code' => 'SCIENTIFIC_INSTRUCTOR',
        'name' => 'Scientific Instructor',
        'committee_id' => $this->committee->id,
    ]);

    // Create professional role with LEADER role type for Scientific
    $this->leaderRole = ProfessionalRole::factory()->create([
        'role' => 'LEADER',
        'code' => 'SCIENTIFIC_LEADER',
        'name' => 'Scientific Leader',
        'committee_id' => $this->committee->id,
    ]);

    // Create Diving instructor role (for cross-committee tests)
    $this->divingInstructorRole = ProfessionalRole::factory()->create([
        'role' => 'INSTRUCTOR',
        'code' => 'DIVING_INSTRUCTOR',
        'name' => 'Diving Instructor',
        'committee_id' => $this->divingCommittee->id,
    ]);

    // Create licenses for the instructor and leader roles
    $this->instructorLicense = License::factory()->create([
        'professional_role_id' => $this->instructorRole->id,
        'committee_id' => $this->committee->id,
    ]);

    $this->leaderLicense = License::factory()->create([
        'professional_role_id' => $this->leaderRole->id,
        'committee_id' => $this->committee->id,
    ]);

    $this->divingLicense = License::factory()->create([
        'professional_role_id' => $this->divingInstructorRole->id,
        'committee_id' => $this->divingCommittee->id,
    ]);

    // Create certifications
    $this->instructorCertification = Certification::factory()->create([
        'professional_role_id' => $this->instructorRole->id,
        'committee_id' => $this->committee->id,
    ]);

    $this->leaderCertification = Certification::factory()->create([
        'professional_role_id' => $this->leaderRole->id,
        'committee_id' => $this->committee->id,
    ]);

    // Create federation
    $this->federation = Federation::factory()->create();

    // Create entity user
    $this->entityUser = User::factory()->create(['group_id' => $this->entityGroup->id]);

    // Create entity and attach user and federation
    $this->entity = Entity::factory()->create();
    $this->entity->users()->attach($this->entityUser);
    $this->entity->federations()->attach($this->federation->id);

    // Create qualified individual for Scientific
    $this->qualifiedIndividual = Individual::factory()->create([
        'member_code' => 'SCIN123',
    ]);

    // Attach individual to entity (active member)
    $this->qualifiedIndividual->entities()->attach($this->entity->id, [
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    // Attach individual to same federation as entity
    $this->qualifiedIndividual->federations()->attach($this->federation->id, [
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    // Create ACTIVE license for the individual (scientific instructor)
    LicenseAttributed::factory()->create([
        'license_id' => $this->instructorLicense->id,
        'model_id' => $this->qualifiedIndividual->id,
        'model_type' => 'individual',
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    // Create ACTIVE certification for the individual
    CertificationAttributed::factory()->create([
        'certification_id' => $this->instructorCertification->id,
        'individual_id' => $this->qualifiedIndividual->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);
});

it('shows qualified scientific instructor in invite modal table', function () {
    Livewire::actingAs($this->entityUser)
        ->test(ManageEntityInstructors::class, [
            'professionalRoles' => ProfessionalRole::whereIn('id', [$this->instructorRole->id])->get(),
            'showInviteSection' => true,
        ])
        ->assertSee('SCIN123');
});

it('shows qualified scientific leader in invite modal table', function () {
    // Create qualified leader individual
    $leaderIndividual = Individual::factory()->create(['member_code' => 'SCLEAD1']);
    $leaderIndividual->entities()->attach($this->entity->id, [
        'status_class' => ActiveIndividualEntityState::class,
    ]);
    $leaderIndividual->federations()->attach($this->federation->id, [
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    LicenseAttributed::factory()->create([
        'license_id' => $this->leaderLicense->id,
        'model_id' => $leaderIndividual->id,
        'model_type' => 'individual',
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    CertificationAttributed::factory()->create([
        'certification_id' => $this->leaderCertification->id,
        'individual_id' => $leaderIndividual->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    Livewire::actingAs($this->entityUser)
        ->test(ManageEntityInstructors::class, [
            'professionalRoles' => ProfessionalRole::whereIn('id', [$this->leaderRole->id])->get(),
            'showInviteSection' => true,
        ])
        ->assertSee('SCLEAD1');
});

it('does not show scientific instructor without active license', function () {
    $noLicenseIndividual = Individual::factory()->create(['member_code' => 'NOSCLI1']);
    $noLicenseIndividual->federations()->attach($this->federation->id, [
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    // Create PENDING license (not active)
    LicenseAttributed::factory()->create([
        'license_id' => $this->instructorLicense->id,
        'model_id' => $noLicenseIndividual->id,
        'model_type' => 'individual',
        'status_class' => PendingLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    // Create active certification
    CertificationAttributed::factory()->create([
        'certification_id' => $this->instructorCertification->id,
        'individual_id' => $noLicenseIndividual->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    Livewire::actingAs($this->entityUser)
        ->test(ManageEntityInstructors::class, [
            'professionalRoles' => ProfessionalRole::whereIn('id', [$this->instructorRole->id])->get(),
            'showInviteSection' => true,
        ])
        ->assertDontSee('NOSCLI1');
});

it('does not show scientific instructor without active certification', function () {
    $noCertIndividual = Individual::factory()->create(['member_code' => 'NOSCRT1']);
    $noCertIndividual->federations()->attach($this->federation->id, [
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    // Create active license
    LicenseAttributed::factory()->create([
        'license_id' => $this->instructorLicense->id,
        'model_id' => $noCertIndividual->id,
        'model_type' => 'individual',
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    // No certification

    Livewire::actingAs($this->entityUser)
        ->test(ManageEntityInstructors::class, [
            'professionalRoles' => ProfessionalRole::whereIn('id', [$this->instructorRole->id])->get(),
            'showInviteSection' => true,
        ])
        ->assertDontSee('NOSCRT1');
});

it('does not show scientific instructor from different federation', function () {
    $otherFederation = Federation::factory()->create();
    $otherFedIndividual = Individual::factory()->create(['member_code' => 'OTHSCI1']);

    // Attach to OTHER federation
    $otherFedIndividual->federations()->attach($otherFederation->id, [
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    LicenseAttributed::factory()->create([
        'license_id' => $this->instructorLicense->id,
        'model_id' => $otherFedIndividual->id,
        'model_type' => 'individual',
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $otherFederation->id,
    ]);

    CertificationAttributed::factory()->create([
        'certification_id' => $this->instructorCertification->id,
        'individual_id' => $otherFedIndividual->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'federation_id' => $otherFederation->id,
    ]);

    Livewire::actingAs($this->entityUser)
        ->test(ManageEntityInstructors::class, [
            'professionalRoles' => ProfessionalRole::whereIn('id', [$this->instructorRole->id])->get(),
            'showInviteSection' => true,
        ])
        ->assertDontSee('OTHSCI1');
});

it('does not show diving instructor when filtering for scientific', function () {
    // Create qualified diving instructor
    $divingIndividual = Individual::factory()->create(['member_code' => 'DIVING1']);
    $divingIndividual->federations()->attach($this->federation->id, [
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    // Create diving certification
    $divingCertification = Certification::factory()->create([
        'professional_role_id' => $this->divingInstructorRole->id,
        'committee_id' => $this->divingCommittee->id,
    ]);

    LicenseAttributed::factory()->create([
        'license_id' => $this->divingLicense->id,
        'model_id' => $divingIndividual->id,
        'model_type' => 'individual',
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    CertificationAttributed::factory()->create([
        'certification_id' => $divingCertification->id,
        'individual_id' => $divingIndividual->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    // Test with Scientific roles - should NOT see diving instructor
    Livewire::actingAs($this->entityUser)
        ->test(ManageEntityInstructors::class, [
            'professionalRoles' => ProfessionalRole::whereIn('id', [$this->instructorRole->id])->get(),
            'showInviteSection' => true,
        ])
        ->assertDontSee('DIVING1');
});

it('shows both instructor and leader roles when both are qualified', function () {
    // Individual qualifies for both roles
    $dualRoleIndividual = Individual::factory()->create(['member_code' => 'DUAL123']);
    $dualRoleIndividual->entities()->attach($this->entity->id, [
        'status_class' => ActiveIndividualEntityState::class,
    ]);
    $dualRoleIndividual->federations()->attach($this->federation->id, [
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    // Active instructor license
    LicenseAttributed::factory()->create([
        'license_id' => $this->instructorLicense->id,
        'model_id' => $dualRoleIndividual->id,
        'model_type' => 'individual',
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    // Active leader license
    LicenseAttributed::factory()->create([
        'license_id' => $this->leaderLicense->id,
        'model_id' => $dualRoleIndividual->id,
        'model_type' => 'individual',
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    // Active instructor certification
    CertificationAttributed::factory()->create([
        'certification_id' => $this->instructorCertification->id,
        'individual_id' => $dualRoleIndividual->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    // Active leader certification
    CertificationAttributed::factory()->create([
        'certification_id' => $this->leaderCertification->id,
        'individual_id' => $dualRoleIndividual->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    Livewire::actingAs($this->entityUser)
        ->test(ManageEntityInstructors::class, [
            'professionalRoles' => ProfessionalRole::whereIn('id', [$this->instructorRole->id, $this->leaderRole->id])->get(),
            'showInviteSection' => true,
        ])
        ->assertSee('DUAL123');
});
