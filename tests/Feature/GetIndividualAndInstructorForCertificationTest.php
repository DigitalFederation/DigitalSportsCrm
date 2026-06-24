<?php

use App\Livewire\GetIndividualAndInstructorForCertification;
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
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Livewire\Livewire;

use function Pest\Laravel\artisan;

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');

    $this->diving_committee = Committee::factory()->create(['code' => 'DIVING', 'name' => 'Diving Committee']);
    $this->group_federation = Group::factory()->create(['code' => 'FEDERATION']);
    $this->group_entity = Group::factory()->create(['code' => 'ENTITY']);
    $this->group_individual = Group::factory()->create(['code' => 'INDIVIDUAL']);
    $this->professionalRoleInstructor = ProfessionalRole::factory()->create(['role' => 'INSTRUCTOR']);

    // Create a License of Instructor
    $this->licenseInstructor = License::factory()->create([
        'name' => 'Three Start License',
        'committee_id' => $this->diving_committee->id,
        'professional_role_id' => $this->professionalRoleInstructor->id,
    ]);

    // Certification of Instructor
    $this->certificationInstructor = Certification::factory()->create([
        'name' => 'Three Start Instructor',
        'committee_id' => $this->diving_committee->id,
        'license_id' => $this->licenseInstructor->id,
    ]);

    // Users
    $this->user_federation = User::factory()->create([
        'group_id' => $this->group_federation->id,
    ]);
    $this->user_entity = User::factory()->create([
        'group_id' => $this->group_entity->id,
    ]);
    $this->user_individual = User::factory()->create([
        'group_id' => $this->group_individual->id,
    ]);

    // Federation
    $this->federation = Federation::factory()->create(['is_local' => false]);
    $this->federation->users()->attach($this->user_federation);

    // Entity
    $this->entity = Entity::factory()->create();
    $this->entity->users()->attach($this->user_entity);
    $this->federation->entities()->attach($this->entity);

    // Individual Course Director
    $this->individual_course_director = Individual::factory()->create([
        'user_id' => $this->user_individual,
        'name' => 'Course Director',
        'member_code' => '1234',
    ]);

    // Attach the CertificationAttributed to the Course Director
    $this->certification_attributed_instructor = CertificationAttributed::factory()->create([
        'individual_id' => $this->individual_course_director->id,
        'federation_id' => $this->federation->id,
        'certification_id' => $this->certificationInstructor->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'committee_id' => $this->diving_committee->id,
        'current_term_ends_at' => now()->addYear(),
    ]);

    // Attach a LicenseAttributed to the Course Director
    $this->licenseAttributed = LicenseAttributed::factory()->create([
        'federation_id' => $this->federation->id,
        'license_id' => $this->licenseInstructor->id,
        'model_id' => $this->individual_course_director->id,
        'model_type' => 'individual',
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    // Add the Instructor relation with the entity
    /*
    EntityProfessionalRole::factory()->create([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual_course_director->id,
        'professional_role_id' => $this->professionalRoleInstructor->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);
    */

    // Attach the Individual to the Entity
    $this->federation->individuals()->attach($this->individual_course_director->id);
    $this->entity->individuals()->attach($this->individual_course_director, ['status_class' => ActiveIndividualEntityState::class]);

});
it('prevents adding a director not related to the entity as an instructor', function () {
    $this->actingAs($this->user_entity);

    // Create an Individual who is a course director but not attached to the entity
    $individual_not_in_entity = Individual::factory()->create([
        'name' => 'Unrelated Course Director',
        'member_code' => '5678',
    ]);

    // Attach the CertificationAttributed to this Individual
    CertificationAttributed::factory()->create([
        'individual_id' => $individual_not_in_entity->id,
        'federation_id' => $this->federation->id,
        'certification_id' => $this->certificationInstructor->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'committee_id' => $this->diving_committee->id,
        'current_term_ends_at' => now()->addYear(),
    ]);

    // Attach a LicenseAttributed to this Individual
    LicenseAttributed::factory()->create([
        'federation_id' => $this->federation->id,
        'license_id' => $this->licenseInstructor->id,
        'model_id' => $individual_not_in_entity->id,
        'model_type' => 'individual',
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    Livewire::test(GetIndividualAndInstructorForCertification::class)
        ->set('selectedFederation', $this->federation->id)
        ->set('codeInstructor', $individual_not_in_entity->member_code)
        ->call('searchInstructor')
        ->assertSee('The individual member associated with this international code does not hold an instructor certification, does not hold an active instructor licence or does not belong to a Diving entity.');
});
