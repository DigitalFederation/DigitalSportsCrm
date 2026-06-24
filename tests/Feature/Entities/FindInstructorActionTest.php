<?php

use App\Models\Group;
use App\Models\User;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Entities\Actions\FindInstructorAction;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\IndividualEntity;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Licenses\Models\License;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed necessary data
    artisan('db:seed --class=RoleAndPermissionSeeder');

    // Create necessary data
    $this->group = Group::factory()->create(['code' => 'ENTITY']);
    $this->user = User::factory()->create(['group_id' => $this->group->id]);
    $this->entity = Entity::factory()->create();
    $this->entity->users()->attach($this->user);

    $this->federation = Federation::factory()->create();
    $this->entity->federations()->attach($this->federation->id);

    $this->individual = Individual::factory()->create([
        'member_code' => '12345',
    ]);

    $this->individual->federations()->attach($this->federation->id, ['status_class' => ActiveIndividualFederationState::class]);

    // Create the active Individual-Entity relationship
    IndividualEntity::create([
        'individual_id' => $this->individual->id,
        'entity_id' => $this->entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    // Create a professional role
    $this->professionalRole = ProfessionalRole::factory()->create([
        'role' => 'INSTRUCTOR',
    ]);

    // Create a license with the professional role
    $this->license = License::factory()->create([
        'professional_role_id' => $this->professionalRole->id,
    ]);

    // Attach the license to the individual
    $this->individual->licenses()->create([
        'status_class' => ActiveLicenseAttributedState::class,
        'license_id' => $this->license->id,
        'federation_id' => $this->federation->id,
        'model_id' => $this->individual->id,
        'model_type' => 'individual',
    ]);

    $this->certificationAttributed = CertificationAttributed::factory()->create([
        'status_class' => ActiveCertificationAttributedState::class,
        'federation_id' => $this->federation->id,
        'individual_id' => $this->individual->id,
    ]);
});

it('can find instructor by code', function () {
    $this->actingAs($this->user);
    $action = new FindInstructorAction;
    $instructor = $action->execute('12345', $this->professionalRole->id, $this->federation->id);

    expect($instructor)->not->toBeNull();
    expect($instructor->member_code)->toBe('12345');
});

it('shows error if instructor not found', function () {
    $this->actingAs($this->user);
    $action = new FindInstructorAction;
    $instructor = $action->execute('67890', $this->professionalRole->id, $this->federation->id);

    expect($instructor)->toBeNull();
});
