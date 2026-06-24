<?php

use App\Livewire\ManageEntityDivingProfessionals;
use App\Models\Committee;
use App\Models\Group;
use App\Models\User;
use Domain\Diving\Models\DivingProfessionalCertification;
use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityProfessionalRole;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\CanceledEntityProfessionalRoleState;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Entities\States\RejectedEntityProfessionalRoleState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create ENTITY group
    $this->entityGroup = Group::factory()->create(['code' => 'ENTITY']);

    // Create DIVINGSERVICES committee (is_international = false by default)
    $this->committee = Committee::factory()->create([
        'code' => 'DIVINGSERVICES',
        'name' => 'Diving Services',
        'is_international' => false,
    ]);

    // Create professional role with DIVINGPROFESSIONAL role type
    $this->professionalRole = ProfessionalRole::factory()->create([
        'role' => 'DIVINGPROFESSIONAL',
        'code' => 'DIVING_PROFESSIONAL',
        'name' => 'Diving Professional',
        'committee_id' => $this->committee->id,
    ]);

    // Create license for the committee
    $this->divingLicense = License::factory()->create([
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

    // Create a qualified individual with a recognizable member_number
    $this->qualifiedIndividual = Individual::factory()->create([
        'member_number' => '99001',
    ]);

    // Attach individual to same federation as entity (with active status)
    $this->qualifiedIndividual->federations()->attach($this->federation->id, [
        'status_class' => ActiveIndividualFederationState::class,
    ]);

    // Create ACTIVE diving professional certification
    DivingProfessionalCertification::factory()
        ->active()
        ->instructor()
        ->create([
            'individual_id' => $this->qualifiedIndividual->id,
        ]);

    // Create ACTIVE license from DIVINGSERVICES committee
    LicenseAttributed::factory()->create([
        'license_id' => $this->divingLicense->id,
        'model_id' => $this->qualifiedIndividual->id,
        'model_type' => 'individual',
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);
});

it('shows qualified diving professional in invite modal table', function () {
    Livewire::actingAs($this->entityUser)
        ->test(ManageEntityDivingProfessionals::class, [
            'professionalRoles' => ProfessionalRole::whereIn('id', [$this->professionalRole->id])->get(),
            'showInviteSection' => true,
        ])
        ->assertSee('99001');
});

it('shows canceled diving professional in invite modal for re-invitation', function () {
    // Create a CANCELED EntityProfessionalRole for the individual
    EntityProfessionalRole::create([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->qualifiedIndividual->id,
        'professional_role_id' => $this->professionalRole->id,
        'entity_name' => $this->entity->name,
        'individual_name' => $this->qualifiedIndividual->full_name,
        'role_name' => $this->professionalRole->name,
        'status_class' => CanceledEntityProfessionalRoleState::class,
    ]);

    Livewire::actingAs($this->entityUser)
        ->test(ManageEntityDivingProfessionals::class, [
            'professionalRoles' => ProfessionalRole::whereIn('id', [$this->professionalRole->id])->get(),
            'showInviteSection' => true,
        ])
        ->assertSee('99001');
});

it('shows rejected diving professional in invite modal for re-invitation', function () {
    // Create a REJECTED EntityProfessionalRole for the individual
    EntityProfessionalRole::create([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->qualifiedIndividual->id,
        'professional_role_id' => $this->professionalRole->id,
        'entity_name' => $this->entity->name,
        'individual_name' => $this->qualifiedIndividual->full_name,
        'role_name' => $this->professionalRole->name,
        'status_class' => RejectedEntityProfessionalRoleState::class,
    ]);

    Livewire::actingAs($this->entityUser)
        ->test(ManageEntityDivingProfessionals::class, [
            'professionalRoles' => ProfessionalRole::whereIn('id', [$this->professionalRole->id])->get(),
            'showInviteSection' => true,
        ])
        ->assertSee('99001');
});

it('does not show diving professional with active role in invite modal', function () {
    // Create an ACTIVE EntityProfessionalRole for the individual
    EntityProfessionalRole::create([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->qualifiedIndividual->id,
        'professional_role_id' => $this->professionalRole->id,
        'entity_name' => $this->entity->name,
        'individual_name' => $this->qualifiedIndividual->full_name,
        'role_name' => $this->professionalRole->name,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);

    Livewire::actingAs($this->entityUser)
        ->test(ManageEntityDivingProfessionals::class, [
            'professionalRoles' => ProfessionalRole::whereIn('id', [$this->professionalRole->id])->get(),
            'showInviteSection' => true,
        ])
        ->assertDontSee('99001');
});

it('does not show diving professional with pending role in invite modal', function () {
    // Create a PENDING EntityProfessionalRole for the individual
    EntityProfessionalRole::create([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->qualifiedIndividual->id,
        'professional_role_id' => $this->professionalRole->id,
        'entity_name' => $this->entity->name,
        'individual_name' => $this->qualifiedIndividual->full_name,
        'role_name' => $this->professionalRole->name,
        'status_class' => PendingEntityProfessionalRoleState::class,
    ]);

    Livewire::actingAs($this->entityUser)
        ->test(ManageEntityDivingProfessionals::class, [
            'professionalRoles' => ProfessionalRole::whereIn('id', [$this->professionalRole->id])->get(),
            'showInviteSection' => true,
        ])
        ->assertDontSee('99001');
});
