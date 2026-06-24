<?php

use App\Models\Committee;
use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityProfessionalRole;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\CanceledEntityProfessionalRoleState;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create groups
    $this->entityGroup = Group::factory()->create(['code' => 'ENTITY']);
    $this->individualGroup = Group::factory()->create(['code' => 'INDIVIDUAL']);

    // Create DIVING committee
    $this->committeeDiving = Committee::factory()->create([
        'code' => 'DIVING',
        'name' => 'Diving',
        'is_international' => true,
    ]);

    // Create DIVINGSERVICES committee (required by controller query)
    $this->committeeDivingServices = Committee::factory()->create([
        'code' => 'DIVINGSERVICES',
        'name' => 'Diving Services',
        'is_international' => false,
    ]);

    // Create DIVINGPROFESSIONAL role (distinct from INSTRUCTOR)
    $this->divingProfessionalRole = ProfessionalRole::factory()->create([
        'role' => 'DIVINGPROFESSIONAL',
        'code' => 'DIVING_PROFESSIONAL',
        'name' => 'Diving Professional',
        'committee_id' => $this->committeeDiving->id,
    ]);

    // Create diving license for entity (required by middleware) - uses DIVING committee
    $this->divingLicense = License::factory()->create([
        'professional_role_id' => $this->divingProfessionalRole->id,
        'committee_id' => $this->committeeDiving->id,
    ]);

    // Create a DIVINGSERVICES license (required by controller query)
    License::factory()->create([
        'professional_role_id' => $this->divingProfessionalRole->id,
        'committee_id' => $this->committeeDivingServices->id,
    ]);

    // Create federation
    $this->federation = Federation::factory()->create();

    // Create entity user
    $this->entityUser = User::factory()->create(['group_id' => $this->entityGroup->id]);

    // Create entity
    $this->entity = Entity::factory()->create();
    $this->entity->users()->attach($this->entityUser);
    $this->entity->federations()->attach($this->federation->id);

    // Create entity license (required by middleware)
    LicenseAttributed::factory()->create([
        'license_id' => $this->divingLicense->id,
        'model_id' => $this->entity->id,
        'model_type' => 'entity',
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    // Create individual with user
    $this->individualUser = User::factory()
        ->for($this->individualGroup, 'group')
        ->has(Individual::factory(), 'individual')
        ->create();
    $this->individual = $this->individualUser->individual;
    // Set member_number explicitly (numeric value as per database schema)
    $this->individual->update(['member_number' => 123456]);
});

// ============================================================================
// Index Page Tests
// ============================================================================

test('entity can view diving professionals index page', function () {
    actingAs($this->entityUser);

    $response = get(route('entity.diving_professionals.index'));

    $response->assertStatus(200);
    $response->assertViewIs('web.entity.diving_professionals.index');
    $response->assertViewHas('instructors');
    $response->assertViewHas('pendingInvitations');
});

test('entity diving professionals index shows active professionals', function () {
    // Create active professional relationship
    EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
        'entity_name' => $this->entity->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    actingAs($this->entityUser);

    $response = get(route('entity.diving_professionals.index'));

    $response->assertStatus(200);
});

test('entity diving professionals index shows pending invitations', function () {
    // Create pending invitation
    EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => PendingEntityProfessionalRoleState::class,
        'entity_name' => $this->entity->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    actingAs($this->entityUser);

    $response = get(route('entity.diving_professionals.index'));

    $response->assertStatus(200);
    $response->assertViewHas('pendingInvitations', function ($invitations) {
        return $invitations->count() > 0;
    });
});

// ============================================================================
// Cancel Invitation Tests
// ============================================================================

test('entity can cancel pending invitation', function () {
    $pendingInvitation = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => PendingEntityProfessionalRoleState::class,
        'entity_name' => $this->entity->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    actingAs($this->entityUser);

    $response = delete(route('entity.diving_professionals.cancel_invitation', $pendingInvitation->id));

    $response->assertRedirect(route('entity.diving_professionals.index'));
    $response->assertSessionHas('success');

    assertDatabaseHas('entity_professional_role', [
        'id' => $pendingInvitation->id,
        'status_class' => CanceledEntityProfessionalRoleState::class,
    ]);
});

test('entity cannot cancel non-pending invitation', function () {
    $activeRelationship = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
        'entity_name' => $this->entity->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    actingAs($this->entityUser);

    $response = delete(route('entity.diving_professionals.cancel_invitation', $activeRelationship->id));

    $response->assertSessionHas('error');

    // Status should remain unchanged
    assertDatabaseHas('entity_professional_role', [
        'id' => $activeRelationship->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);
});

test('entity cannot cancel another entitys invitation', function () {
    // Create another entity
    $otherEntityUser = User::factory()->create(['group_id' => $this->entityGroup->id]);
    $otherEntity = Entity::factory()->create();
    $otherEntity->users()->attach($otherEntityUser);
    $otherEntity->federations()->attach($this->federation->id);

    // Give other entity the required license
    LicenseAttributed::factory()->create([
        'license_id' => $this->divingLicense->id,
        'model_id' => $otherEntity->id,
        'model_type' => 'entity',
        'status_class' => ActiveLicenseAttributedState::class,
        'federation_id' => $this->federation->id,
    ]);

    // Create invitation for the original entity
    $pendingInvitation = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => PendingEntityProfessionalRoleState::class,
        'entity_name' => $this->entity->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    // Try to cancel from other entity
    actingAs($otherEntityUser);

    $response = delete(route('entity.diving_professionals.cancel_invitation', $pendingInvitation->id));

    $response->assertStatus(403);
});

// ============================================================================
// Remove Professional Tests
// ============================================================================

test('entity can deactivate professional relationship', function () {
    $relationship = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
        'entity_name' => $this->entity->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    actingAs($this->entityUser);

    $response = delete(route('entity.diving_professionals.remove', $relationship->id), [
        'action' => 'deactivate',
        'reason' => 'No longer needed',
    ]);

    $response->assertRedirect(route('entity.diving_professionals.index'));
    $response->assertSessionHas('success');
});

test('entity can delete professional relationship', function () {
    $relationship = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
        'entity_name' => $this->entity->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    actingAs($this->entityUser);

    $response = delete(route('entity.diving_professionals.remove', $relationship->id), [
        'action' => 'delete',
    ]);

    $response->assertRedirect(route('entity.diving_professionals.index'));
    $response->assertSessionHas('success');

    assertDatabaseMissing('entity_professional_role', [
        'id' => $relationship->id,
    ]);
});

// ============================================================================
// Authorization Tests
// ============================================================================

test('user without entity cannot access diving professionals pages', function () {
    $userWithoutEntity = User::factory()->create(['group_id' => $this->entityGroup->id]);
    actingAs($userWithoutEntity);

    $response = get(route('entity.diving_professionals.index'));
    $response->assertStatus(403);
});
