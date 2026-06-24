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

    // Create committee
    $this->committee = Committee::factory()->create(['code' => 'DIVING', 'name' => 'Diving']);

    // Create professional role with INSTRUCTOR role type
    $this->instructorRole = ProfessionalRole::factory()->create([
        'role' => 'INSTRUCTOR',
        'code' => 'DIVING_INSTRUCTOR',
        'name' => 'Diving Instructor',
        'committee_id' => $this->committee->id,
    ]);

    // Create license for the instructor role
    $this->instructorLicense = License::factory()->create([
        'professional_role_id' => $this->instructorRole->id,
        'committee_id' => $this->committee->id,
    ]);

    // Create certification for the instructor role
    $this->instructorCertification = Certification::factory()->create([
        'professional_role_id' => $this->instructorRole->id,
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

    // Create individual who should appear in the invite modal
    // Note: member_code is limited to 7 characters in the database
    $this->qualifiedIndividual = Individual::factory()->create([
        'member_code' => 'QUAL123',
    ]);

    // Attach individual to entity (active member)
    $this->qualifiedIndividual->entities()->attach($this->entity->id, [
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    // Attach individual to same federation as entity (with active status)
    $this->qualifiedIndividual->federations()->attach($this->federation->id, [
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    // Create ACTIVE license for the individual (instructor role)
    // NOTE: Must use 'individual' (morph map alias), not Individual::class
    LicenseAttributed::factory()->create([
        'license_id' => $this->instructorLicense->id,
        'model_id' => $this->qualifiedIndividual->id,
        'model_type' => 'individual', // Use morph map alias
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    // Create ACTIVE certification for the individual (instructor role)
    CertificationAttributed::factory()->create([
        'certification_id' => $this->instructorCertification->id,
        'individual_id' => $this->qualifiedIndividual->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);
});

it('shows qualified instructor in invite modal table', function () {
    Livewire::actingAs($this->entityUser)
        ->test(ManageEntityInstructors::class, [
            'professionalRoles' => ProfessionalRole::whereIn('id', [$this->instructorRole->id])->get(),
            'showInviteSection' => true,
        ])
        ->assertSee('QUAL123');
});

it('does not show instructor without active license', function () {
    // Create individual without active license
    // Note: member_code is limited to 7 characters
    $noLicenseIndividual = Individual::factory()->create([
        'member_code' => 'NOLIC56',
    ]);

    // Attach to entity
    $noLicenseIndividual->entities()->attach($this->entity->id, [
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    // Attach to federation
    $noLicenseIndividual->federations()->attach($this->federation->id, [
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    // Create PENDING license (not active)
    LicenseAttributed::factory()->create([
        'license_id' => $this->instructorLicense->id,
        'model_id' => $noLicenseIndividual->id,
        'model_type' => 'individual', // Use morph map alias
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
        ->assertDontSee('NOLIC56');
});

it('does not show instructor without active certification', function () {
    // Create individual without active certification
    // Note: member_code is limited to 7 characters
    $noCertIndividual = Individual::factory()->create([
        'member_code' => 'NOCRT89',
    ]);

    // Attach to entity
    $noCertIndividual->entities()->attach($this->entity->id, [
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    // Attach to federation
    $noCertIndividual->federations()->attach($this->federation->id, [
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    // Create active license
    LicenseAttributed::factory()->create([
        'license_id' => $this->instructorLicense->id,
        'model_id' => $noCertIndividual->id,
        'model_type' => 'individual', // Use morph map alias
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    // No certification created

    Livewire::actingAs($this->entityUser)
        ->test(ManageEntityInstructors::class, [
            'professionalRoles' => ProfessionalRole::whereIn('id', [$this->instructorRole->id])->get(),
            'showInviteSection' => true,
        ])
        ->assertDontSee('NOCRT89');
});

it('does not show instructor from different federation', function () {
    // Create a different federation
    $otherFederation = Federation::factory()->create();

    // Create individual in different federation
    // Note: member_code is limited to 7 characters
    $otherFedIndividual = Individual::factory()->create([
        'member_code' => 'OTHFED1',
    ]);

    // Attach to OTHER federation (not entity's federation)
    $otherFedIndividual->federations()->attach($otherFederation->id, [
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    // Create active license
    LicenseAttributed::factory()->create([
        'license_id' => $this->instructorLicense->id,
        'model_id' => $otherFedIndividual->id,
        'model_type' => 'individual', // Use morph map alias
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $otherFederation->id,
    ]);

    // Create active certification
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
        ->assertDontSee('OTHFED1');
});

it('does not show instructor who is not a member of the entity', function () {
    // Create individual with all qualifications but NOT attached to this entity
    $nonMemberIndividual = Individual::factory()->create([
        'member_code' => 'NONMEM1',
    ]);

    // Attach to federation (same as entity)
    $nonMemberIndividual->federations()->attach($this->federation->id, [
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    // Create active license
    LicenseAttributed::factory()->create([
        'license_id' => $this->instructorLicense->id,
        'model_id' => $nonMemberIndividual->id,
        'model_type' => 'individual',
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    // Create active certification
    CertificationAttributed::factory()->create([
        'certification_id' => $this->instructorCertification->id,
        'individual_id' => $nonMemberIndividual->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    // NOT attached to entity - should not appear
    Livewire::actingAs($this->entityUser)
        ->test(ManageEntityInstructors::class, [
            'professionalRoles' => ProfessionalRole::whereIn('id', [$this->instructorRole->id])->get(),
            'showInviteSection' => true,
        ])
        ->assertDontSee('NONMEM1');
});

it('verifies license query matches active status class exactly', function () {
    // This test directly verifies the query logic that checks for active licenses
    $individualId = $this->qualifiedIndividual->id;
    $relevantRoleIds = [$this->instructorRole->id];

    // Verify the morphMany relationship works correctly with morph map alias
    $individual = Individual::with('licenses.license')->find($individualId);
    expect($individual->licenses)->toHaveCount(1);

    // Check using the same query logic as ManageEntityInstructors
    $hasActiveLicense = Individual::where('id', $individualId)
        ->whereHas('licenses', function ($licenseQuery) use ($relevantRoleIds) {
            $licenseQuery->where('status_class', ActiveLicenseAttributedState::class)
                ->whereHas('license', function ($licenseSubQuery) use ($relevantRoleIds) {
                    $licenseSubQuery->whereIn('professional_role_id', $relevantRoleIds);
                });
        })->exists();

    expect($hasActiveLicense)->toBeTrue();

    // Also verify the license data directly (use morph map alias)
    $license = LicenseAttributed::where('model_id', $individualId)
        ->where('model_type', 'individual')
        ->first();

    expect($license)->not->toBeNull();
    expect($license->status_class)->toBe(ActiveLicenseAttributedState::class);
    expect($license->license->professional_role_id)->toBe($this->instructorRole->id);
});
