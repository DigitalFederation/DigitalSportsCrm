<?php

use App\Models\Committee;
use App\Models\Group;
use App\Models\User;
use Domain\Certifications\Models\Certification;
use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityProfessionalRole;
use Domain\Entities\Models\EntityProfessionalRoleInvitation;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\CanceledEntityProfessionalRoleState;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create groups
    $this->entityGroup = Group::factory()->create(['code' => 'ENTITY']);
    $this->individualGroup = Group::factory()->create(['code' => 'INDIVIDUAL']);

    // Create committees
    $this->committeeDiving = Committee::factory()->create([
        'code' => 'DIVING',
        'name' => 'Diving',
        'is_international' => true,
    ]);
    $this->committeeScientific = Committee::factory()->create([
        'code' => 'SCIENTIFIC',
        'name' => 'Scientific',
        'is_international' => true,
    ]);

    // Create professional roles
    $this->divingInstructorRole = ProfessionalRole::factory()->create([
        'role' => 'INSTRUCTOR',
        'code' => 'DIVING_INSTRUCTOR',
        'name' => 'Diving Instructor',
        'committee_id' => $this->committeeDiving->id,
    ]);
    $this->scientificInstructorRole = ProfessionalRole::factory()->create([
        'role' => 'INSTRUCTOR',
        'code' => 'SCIENTIFIC_INSTRUCTOR',
        'name' => 'Scientific Instructor',
        'committee_id' => $this->committeeScientific->id,
    ]);

    // Create licenses
    $this->divingInstructorLicense = License::factory()->create([
        'professional_role_id' => $this->divingInstructorRole->id,
        'committee_id' => $this->committeeDiving->id,
    ]);
    $this->scientificInstructorLicense = License::factory()->create([
        'professional_role_id' => $this->scientificInstructorRole->id,
        'committee_id' => $this->committeeScientific->id,
    ]);

    // Create certifications
    $this->divingInstructorCertification = Certification::factory()->create([
        'professional_role_id' => $this->divingInstructorRole->id,
        'committee_id' => $this->committeeDiving->id,
    ]);
    $this->scientificInstructorCertification = Certification::factory()->create([
        'professional_role_id' => $this->scientificInstructorRole->id,
        'committee_id' => $this->committeeScientific->id,
    ]);

    // Create federation
    $this->federation = Federation::factory()->create();

    // Create entity user
    $this->entityUser = User::factory()->create(['group_id' => $this->entityGroup->id]);

    // Create entity
    $this->entity = Entity::factory()->create();
    $this->entity->users()->attach($this->entityUser);
    $this->entity->federations()->attach($this->federation->id);

    // Create entity licenses (required by middleware)
    LicenseAttributed::factory()->create([
        'license_id' => $this->divingInstructorLicense->id,
        'model_id' => $this->entity->id,
        'model_type' => 'entity',
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);
    LicenseAttributed::factory()->create([
        'license_id' => $this->scientificInstructorLicense->id,
        'model_id' => $this->entity->id,
        'model_type' => 'entity',
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    // Create individual with user
    $this->individualUser = User::factory()
        ->for($this->individualGroup, 'group')
        ->has(Individual::factory()->state(['member_code' => 'TEST123']), 'individual')
        ->create();
    $this->individual = $this->individualUser->individual;
    $this->individual->federations()->attach($this->federation->id, [
        'status_class' => ActiveIndividualFederationState::class,
    ]);
});

// ============================================================================
// International Diving Instructor Controller Tests
// ============================================================================

test('entity can view International diving instructors index page', function () {
    actingAs($this->entityUser);

    $response = get(route('entity.international-diving-instructor.index'));

    $response->assertStatus(200);
    $response->assertViewIs('web.entity.international_diving_instructor.index');
    $response->assertViewHas('entity');
    $response->assertViewHas('instructors');
    $response->assertViewHas('professionalRoles');
    $response->assertViewHas('pendingInvitations');
});

test('entity International diving index shows associated instructors', function () {
    // Create an associated instructor
    EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingInstructorRole->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
        'entity_name' => $this->entity->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingInstructorRole->name,
    ]);

    actingAs($this->entityUser);

    $response = get(route('entity.international-diving-instructor.index'));

    $response->assertStatus(200);
    $response->assertSee($this->individual->member_code);
});

test('entity International diving index shows pending invitations', function () {
    // Create pending invitation
    EntityProfessionalRoleInvitation::create([
        'entity_id' => $this->entity->id,
        'inviting_entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'invited_user_id' => $this->individualUser->id,
        'professional_role_id' => $this->divingInstructorRole->id,
        'status_class' => PendingEntityProfessionalRoleState::class,
        'committee_code' => 'DIVING',
        'status' => 'pending',
        'expires_at' => now()->addDays(7),
    ]);

    actingAs($this->entityUser);

    $response = get(route('entity.international-diving-instructor.index'));

    $response->assertStatus(200);
    $response->assertViewHas('pendingInvitations', function ($invitations) {
        return $invitations->count() > 0;
    });
});

test('entity can cancel pending International diving invitation', function () {
    // Create pending association (not invitation)
    $pendingAssociation = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingInstructorRole->id,
        'status_class' => PendingEntityProfessionalRoleState::class,
        'entity_name' => $this->entity->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingInstructorRole->name,
    ]);

    actingAs($this->entityUser);

    $response = delete(route('entity.international-diving-instructor.cancel_invitation', $pendingAssociation->id));

    $response->assertRedirect(route('entity.international-diving-instructor.index'));
    $response->assertSessionHas('success');

    assertDatabaseHas('entity_professional_role', [
        'id' => $pendingAssociation->id,
        'status_class' => CanceledEntityProfessionalRoleState::class,
    ]);
});

test('entity cannot cancel non-pending International diving invitation', function () {
    // Create active association
    $activeAssociation = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingInstructorRole->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
        'entity_name' => $this->entity->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingInstructorRole->name,
    ]);

    actingAs($this->entityUser);

    $response = delete(route('entity.international-diving-instructor.cancel_invitation', $activeAssociation->id));

    $response->assertSessionHas('error');

    // Status should remain unchanged
    assertDatabaseHas('entity_professional_role', [
        'id' => $activeAssociation->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);
});

test('entity can deactivate International diving instructor relationship', function () {
    $association = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingInstructorRole->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
        'entity_name' => $this->entity->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingInstructorRole->name,
    ]);

    actingAs($this->entityUser);

    $response = delete(route('entity.international-diving-instructor.remove', $association->id), [
        'action' => 'deactivate',
        'reason' => 'No longer needed',
    ]);

    $response->assertRedirect(route('entity.international-diving-instructor.index'));
    $response->assertSessionHas('success');
});

test('entity can delete International diving instructor relationship', function () {
    $association = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingInstructorRole->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
        'entity_name' => $this->entity->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingInstructorRole->name,
    ]);

    actingAs($this->entityUser);

    $response = delete(route('entity.international-diving-instructor.remove', $association->id), [
        'action' => 'delete',
    ]);

    $response->assertRedirect(route('entity.international-diving-instructor.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('entity_professional_role', [
        'id' => $association->id,
    ]);
});

// ============================================================================
// Scientific Instructor Controller Tests
// ============================================================================

test('entity can view Scientific instructors index page', function () {
    actingAs($this->entityUser);

    $response = get(route('entity.scientific-instructor.index'));

    $response->assertStatus(200);
    $response->assertViewIs('web.entity.scientific_instructor.index');
    $response->assertViewHas('entity');
    $response->assertViewHas('instructors');
    $response->assertViewHas('professionalRoles');
    $response->assertViewHas('pendingInvitations');
});

test('entity Scientific index shows associated instructors', function () {
    EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->scientificInstructorRole->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
        'entity_name' => $this->entity->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->scientificInstructorRole->name,
    ]);

    actingAs($this->entityUser);

    $response = get(route('entity.scientific-instructor.index'));

    $response->assertStatus(200);
    $response->assertSee($this->individual->member_code);
});

test('entity can cancel pending Scientific invitation', function () {
    $pendingAssociation = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->scientificInstructorRole->id,
        'status_class' => PendingEntityProfessionalRoleState::class,
        'entity_name' => $this->entity->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->scientificInstructorRole->name,
    ]);

    actingAs($this->entityUser);

    $response = delete(route('entity.scientific-instructor.cancel_invitation', $pendingAssociation->id));

    $response->assertRedirect(route('entity.scientific-instructor.index'));
    $response->assertSessionHas('success');

    assertDatabaseHas('entity_professional_role', [
        'id' => $pendingAssociation->id,
        'status_class' => CanceledEntityProfessionalRoleState::class,
    ]);
});

test('entity can deactivate Scientific instructor relationship', function () {
    $association = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->scientificInstructorRole->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
        'entity_name' => $this->entity->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->scientificInstructorRole->name,
    ]);

    actingAs($this->entityUser);

    $response = delete(route('entity.scientific-instructor.remove', $association->id), [
        'action' => 'deactivate',
        'reason' => 'No longer needed',
    ]);

    $response->assertRedirect(route('entity.scientific-instructor.index'));
    $response->assertSessionHas('success');
});

test('entity can delete Scientific instructor relationship', function () {
    $association = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->scientificInstructorRole->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
        'entity_name' => $this->entity->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->scientificInstructorRole->name,
    ]);

    actingAs($this->entityUser);

    $response = delete(route('entity.scientific-instructor.remove', $association->id), [
        'action' => 'delete',
    ]);

    $response->assertRedirect(route('entity.scientific-instructor.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('entity_professional_role', [
        'id' => $association->id,
    ]);
});

// ============================================================================
// Authorization Tests
// ============================================================================

test('entity cannot cancel another entitys invitation', function () {
    // Create another entity with required license
    $otherEntityUser = User::factory()->create(['group_id' => $this->entityGroup->id]);
    $otherEntity = Entity::factory()->create();
    $otherEntity->users()->attach($otherEntityUser);
    $otherEntity->federations()->attach($this->federation->id);

    // Give other entity the required license
    LicenseAttributed::factory()->create([
        'license_id' => $this->divingInstructorLicense->id,
        'model_id' => $otherEntity->id,
        'model_type' => 'entity',
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    // Create invitation for the original entity
    $pendingAssociation = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingInstructorRole->id,
        'status_class' => PendingEntityProfessionalRoleState::class,
        'entity_name' => $this->entity->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingInstructorRole->name,
    ]);

    // Try to cancel from other entity
    actingAs($otherEntityUser);

    $response = delete(route('entity.international-diving-instructor.cancel_invitation', $pendingAssociation->id));

    $response->assertStatus(403);
});

test('user without entity cannot access instructor pages', function () {
    $userWithoutEntity = User::factory()->create(['group_id' => $this->entityGroup->id]);
    actingAs($userWithoutEntity);

    $response = get(route('entity.international-diving-instructor.index'));
    $response->assertStatus(403);

    $response = get(route('entity.scientific-instructor.index'));
    $response->assertStatus(403);
});
