<?php

use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityProfessionalRole;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\RejectedEntityProfessionalRoleState;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a diving committee
    $committee = \App\Models\Committee::create([
        'code' => 'DIVING',
        'name' => 'Diving Committee',
        'is_international' => true,
    ]);

    // Create a professional role
    $this->professionalRole = ProfessionalRole::firstOrCreate([
        'committee_id' => $committee->id,
        'code' => 'INSTRUCTOR',
        'name' => 'Diving Instructor',
        'role' => 'INSTRUCTOR',
    ]);

    // Create user groups
    $entityGroup = \App\Models\Group::firstOrCreate(['code' => 'ENTITY'], ['name' => 'Entity']);
    $individualGroup = \App\Models\Group::firstOrCreate(['code' => 'INDIVIDUAL'], ['name' => 'Individual']);

    // Create entity with user (must have ENTITY group)
    $this->entityUser = User::factory()->create(['group_id' => $entityGroup->id]);
    $this->entity = Entity::factory()->create();
    $this->entity->users()->attach($this->entityUser);

    // Create a federation for licenses
    $federation = \Domain\Federations\Models\Federation::factory()->create();
    $this->entity->federations()->attach($federation, [
        'status_class' => \Domain\Entities\States\ActiveEntityFederationState::class,
    ]);

    // Create license type and license
    $licenseType = \Domain\Licenses\Models\LicenseType::factory()->create([
        'name' => 'entity',
        'is_individual' => false,
    ]);

    $license = \Domain\Licenses\Models\License::factory()->create([
        'committee_id' => $committee->id,
        'type_id' => $licenseType->id,
        'requester_model' => Entity::class,
    ]);

    // Assign active diving license to entity (required by check_entity_can_invite:diving middleware)
    \Domain\Licenses\Models\LicenseAttributed::create([
        'license_id' => $license->id,
        'federation_id' => $federation->id,
        'model_type' => 'entity',
        'model_id' => $this->entity->id,
        'status_class' => \Domain\Licenses\States\ActiveLicenseAttributedState::class,
        'created_by' => $this->entityUser->id,
        'updated_by' => $this->entityUser->id,
    ]);

    // Create individual with user (must have INDIVIDUAL group)
    $this->individualUser = User::factory()->create(['group_id' => $individualGroup->id]);
    $this->individual = Individual::factory()->create([
        'user_id' => $this->individualUser->id,
    ]);

    // Create active professional relationship
    $this->professionalRelationship = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->professionalRole->id,
        'entity_name' => $this->entity->name,
        'individual_name' => $this->individual->full_name,
        'role_name' => $this->professionalRole->name,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);
});

test('entity can deactivate professional relationship with reason', function () {
    $this->actingAs($this->entityUser);

    $response = $this->delete(route('entity.diving_professionals.remove', $this->professionalRelationship->id), [
        'action' => 'deactivate',
        'reason' => 'No longer required for our operations',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->professionalRelationship->refresh();

    expect($this->professionalRelationship->status_class)->toBe(RejectedEntityProfessionalRoleState::class);
    expect($this->professionalRelationship->deactivated_at)->not->toBeNull();
    expect($this->professionalRelationship->deactivation_reason)->toBe('No longer required for our operations');
    expect($this->professionalRelationship->deactivated_by)->toBe('entity');
});

test('entity can permanently delete professional relationship', function () {
    $this->actingAs($this->entityUser);

    $response = $this->delete(route('entity.diving_professionals.remove', $this->professionalRelationship->id), [
        'action' => 'delete',
        'reason' => 'Not needed',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(EntityProfessionalRole::find($this->professionalRelationship->id))->toBeNull();
});

test('individual can deactivate professional relationship', function () {
    $this->actingAs($this->individualUser);

    $response = $this->delete(route('individual.instructor.delete', $this->professionalRelationship->id), [
        'action' => 'deactivate',
        'reason' => 'Changing to another entity',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->professionalRelationship->refresh();

    expect($this->professionalRelationship->status_class)->toBe(RejectedEntityProfessionalRoleState::class);
    expect($this->professionalRelationship->deactivated_by)->toBe('individual');
    expect($this->professionalRelationship->deactivation_reason)->toBe('Changing to another entity');
});

test('deactivation requires reason when action is deactivate', function () {
    $this->actingAs($this->entityUser);

    $response = $this->delete(route('entity.diving_professionals.remove', $this->professionalRelationship->id), [
        'action' => 'deactivate',
        'reason' => '',
    ]);

    $response->assertSessionHasErrors(['reason']);
});

test('cannot deactivate already deactivated relationship', function () {
    // First deactivate the relationship
    $this->professionalRelationship->deactivate('Initial reason', 'entity');

    $this->actingAs($this->entityUser);

    // Try to deactivate again
    $response = $this->delete(route('entity.diving_professionals.remove', $this->professionalRelationship->id), [
        'action' => 'deactivate',
        'reason' => 'Another reason',
    ]);

    $response->assertRedirect();

    $this->professionalRelationship->refresh();

    // Should still have the initial deactivation details
    expect($this->professionalRelationship->deactivation_reason)->toBe('Initial reason');
});

test('deactivated relationship can be reactivated', function () {
    // First deactivate
    $this->professionalRelationship->deactivate('Test reason', 'entity');

    expect($this->professionalRelationship->status_class)->toBe(RejectedEntityProfessionalRoleState::class);
    expect($this->professionalRelationship->deactivated_at)->not->toBeNull();

    // Then reactivate
    $result = $this->professionalRelationship->reactivate();

    expect($result)->toBeTrue();
    expect($this->professionalRelationship->status_class)->toBe(ActiveEntityProfessionalRoleState::class);
    expect($this->professionalRelationship->deactivated_at)->toBeNull();
    expect($this->professionalRelationship->deactivation_reason)->toBeNull();
    expect($this->professionalRelationship->deactivated_by)->toBeNull();
});

test('only authorized users can deactivate relationships', function () {
    $otherUser = User::factory()->create();
    $this->actingAs($otherUser);

    $response = $this->delete(route('entity.diving_professionals.remove', $this->professionalRelationship->id), [
        'action' => 'deactivate',
        'reason' => 'Unauthorized attempt',
    ]);

    $response->assertForbidden(); // User doesn't have ENTITY group or access to this entity

    $this->professionalRelationship->refresh();
    expect($this->professionalRelationship->status_class)->toBe(ActiveEntityProfessionalRoleState::class);
});
