<?php

use App\Models\Committee;
use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityProfessionalRole;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Entities\States\RejectedEntityProfessionalRoleState;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseType;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->individualGroup = Group::firstOrCreate(['code' => 'INDIVIDUAL'], ['name' => 'Individual']);

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

    // Create DIVINGPROFESSIONAL role
    $this->divingProfessionalRole = ProfessionalRole::factory()->create([
        'role' => 'DIVINGPROFESSIONAL',
        'code' => 'DIVING_PROFESSIONAL',
        'name' => 'Diving Professional',
        'committee_id' => $this->committeeDiving->id,
    ]);

    // Create a license linking the professional role to DIVINGSERVICES committee
    // (required by controller query: professionalRole.licenses.committee.code = DIVINGSERVICES)
    $licenseType = LicenseType::factory()->create();
    License::factory()->create([
        'professional_role_id' => $this->divingProfessionalRole->id,
        'committee_id' => $this->committeeDivingServices->id,
        'type_id' => $licenseType->id,
        'active' => true,
    ]);

    // Create user with individual
    $this->user = User::factory()
        ->for($this->individualGroup, 'group')
        ->has(Individual::factory()->state(['member_number' => 'MEMBER123']), 'individual')
        ->create();
    $this->individual = $this->user->individual;

    // Create entities
    $this->entity1 = Entity::factory()->create(['name' => 'Test Entity 1']);
    $this->entity2 = Entity::factory()->create(['name' => 'Test Entity 2']);

    actingAs($this->user);
});

// ============================================================================
// Index Page Tests
// ============================================================================

test('individual can view diving professionals index page', function () {
    $response = get(route('individual.diving_professionals.index'));

    $response->assertStatus(200);
    $response->assertViewIs('web.individual.diving_professionals.index');
    $response->assertViewHas('professionalRoles');
    $response->assertViewHas('pendingInvitations');
    $response->assertViewHas('activeRelationships');
    $response->assertViewHas('rejectedInvitations');
});

test('individual diving professionals index shows pending invitations', function () {
    EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => PendingEntityProfessionalRoleState::class,
        'entity_name' => $this->entity1->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    $response = get(route('individual.diving_professionals.index'));

    $response->assertStatus(200);
    $response->assertSee($this->entity1->name);
});

test('individual diving professionals index shows active relationships', function () {
    EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
        'entity_name' => $this->entity1->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    $response = get(route('individual.diving_professionals.index'));

    $response->assertStatus(200);
    $response->assertSee($this->entity1->name);
});

test('individual diving professionals index shows rejected invitations', function () {
    EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => RejectedEntityProfessionalRoleState::class,
        'entity_name' => $this->entity1->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    $response = get(route('individual.diving_professionals.index'));

    $response->assertStatus(200);
});

test('individual diving professionals index shows multiple invitations from different entities', function () {
    EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => PendingEntityProfessionalRoleState::class,
        'entity_name' => $this->entity1->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity2->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => PendingEntityProfessionalRoleState::class,
        'entity_name' => $this->entity2->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    $response = get(route('individual.diving_professionals.index'));

    $response->assertStatus(200);
    $response->assertSee($this->entity1->name);
    $response->assertSee($this->entity2->name);
});

// ============================================================================
// Accept Invitation Tests
// ============================================================================

test('individual can accept pending invitation', function () {
    $pendingInvitation = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => PendingEntityProfessionalRoleState::class,
        'entity_name' => $this->entity1->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    $response = post(route('individual.diving_professionals.accept', $pendingInvitation->id));

    $response->assertRedirect(route('individual.diving_professionals.index'));
    $response->assertSessionHas('success');

    assertDatabaseHas('entity_professional_role', [
        'id' => $pendingInvitation->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);
});

test('individual cannot accept non-pending invitation', function () {
    $activeRelationship = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
        'entity_name' => $this->entity1->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    $response = post(route('individual.diving_professionals.accept', $activeRelationship->id));

    $response->assertRedirect(route('individual.diving_professionals.index'));
    $response->assertSessionHas('error');
});

test('individual cannot accept another individuals invitation', function () {
    // Create another individual
    $otherUser = User::factory()
        ->for($this->individualGroup, 'group')
        ->has(Individual::factory(), 'individual')
        ->create();

    $pendingInvitation = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity1->id,
        'individual_id' => $otherUser->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => PendingEntityProfessionalRoleState::class,
        'entity_name' => $this->entity1->name,
        'individual_name' => $otherUser->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    $response = post(route('individual.diving_professionals.accept', $pendingInvitation->id));

    $response->assertStatus(403);
});

test('accepting invitation creates activity log', function () {
    $pendingInvitation = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => PendingEntityProfessionalRoleState::class,
        'entity_name' => $this->entity1->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    post(route('individual.diving_professionals.accept', $pendingInvitation->id));

    assertDatabaseHas('activity_log', [
        'log_name' => 'diving_professional',
        'description' => 'Diving professional invitation accepted',
        'causer_type' => User::class,
        'causer_id' => $this->user->id,
    ]);
});

// ============================================================================
// Reject Invitation Tests
// ============================================================================

test('individual can reject pending invitation', function () {
    $pendingInvitation = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => PendingEntityProfessionalRoleState::class,
        'entity_name' => $this->entity1->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    $response = post(route('individual.diving_professionals.reject', $pendingInvitation->id), [
        'reason' => 'Not interested',
    ]);

    $response->assertRedirect(route('individual.diving_professionals.index'));
    $response->assertSessionHas('success');

    assertDatabaseHas('entity_professional_role', [
        'id' => $pendingInvitation->id,
        'status_class' => RejectedEntityProfessionalRoleState::class,
    ]);
});

test('individual can reject invitation without reason', function () {
    $pendingInvitation = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => PendingEntityProfessionalRoleState::class,
        'entity_name' => $this->entity1->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    $response = post(route('individual.diving_professionals.reject', $pendingInvitation->id));

    $response->assertRedirect(route('individual.diving_professionals.index'));
    $response->assertSessionHas('success');

    assertDatabaseHas('entity_professional_role', [
        'id' => $pendingInvitation->id,
        'status_class' => RejectedEntityProfessionalRoleState::class,
    ]);
});

test('individual cannot reject non-pending invitation', function () {
    $activeRelationship = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
        'entity_name' => $this->entity1->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    $response = post(route('individual.diving_professionals.reject', $activeRelationship->id));

    $response->assertRedirect(route('individual.diving_professionals.index'));
    $response->assertSessionHas('error');
});

test('individual cannot reject another individuals invitation', function () {
    // Create another individual
    $otherUser = User::factory()
        ->for($this->individualGroup, 'group')
        ->has(Individual::factory(), 'individual')
        ->create();

    $pendingInvitation = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity1->id,
        'individual_id' => $otherUser->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => PendingEntityProfessionalRoleState::class,
        'entity_name' => $this->entity1->name,
        'individual_name' => $otherUser->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    $response = post(route('individual.diving_professionals.reject', $pendingInvitation->id));

    $response->assertStatus(403);
});

test('rejecting invitation creates activity log', function () {
    $pendingInvitation = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => PendingEntityProfessionalRoleState::class,
        'entity_name' => $this->entity1->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    post(route('individual.diving_professionals.reject', $pendingInvitation->id), [
        'reason' => 'Not available',
    ]);

    assertDatabaseHas('activity_log', [
        'log_name' => 'diving_professional',
        'description' => 'Diving professional invitation rejected',
        'causer_type' => User::class,
        'causer_id' => $this->user->id,
    ]);
});

// ============================================================================
// Destroy (End Relationship) Tests
// ============================================================================

test('individual can end active professional relationship', function () {
    $activeRelationship = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
        'entity_name' => $this->entity1->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    $response = delete(route('individual.diving_professionals.destroy', $activeRelationship->id), [
        'reason' => 'Moving to another entity',
    ]);

    $response->assertRedirect(route('individual.diving_professionals.index'));
    $response->assertSessionHas('success');

    assertDatabaseHas('entity_professional_role', [
        'id' => $activeRelationship->id,
        'status_class' => RejectedEntityProfessionalRoleState::class,
    ]);
});

test('individual cannot end relationship without reason', function () {
    $activeRelationship = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
        'entity_name' => $this->entity1->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    $response = delete(route('individual.diving_professionals.destroy', $activeRelationship->id));

    $response->assertSessionHasErrors('reason');
});

test('individual cannot end non-active relationship', function () {
    $pendingInvitation = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => PendingEntityProfessionalRoleState::class,
        'entity_name' => $this->entity1->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    $response = delete(route('individual.diving_professionals.destroy', $pendingInvitation->id), [
        'reason' => 'Testing',
    ]);

    $response->assertRedirect(route('individual.diving_professionals.index'));
    $response->assertSessionHas('error');
});

test('individual cannot end another individuals relationship', function () {
    // Create another individual
    $otherUser = User::factory()
        ->for($this->individualGroup, 'group')
        ->has(Individual::factory(), 'individual')
        ->create();

    $activeRelationship = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity1->id,
        'individual_id' => $otherUser->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
        'entity_name' => $this->entity1->name,
        'individual_name' => $otherUser->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    $response = delete(route('individual.diving_professionals.destroy', $activeRelationship->id), [
        'reason' => 'Testing',
    ]);

    $response->assertStatus(403);
});

test('ending relationship creates activity log', function () {
    $activeRelationship = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->divingProfessionalRole->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
        'entity_name' => $this->entity1->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->divingProfessionalRole->name,
    ]);

    delete(route('individual.diving_professionals.destroy', $activeRelationship->id), [
        'reason' => 'Moving to another entity',
    ]);

    assertDatabaseHas('activity_log', [
        'log_name' => 'diving_professional',
        'description' => 'Diving professional relationship deactivated by individual',
        'causer_type' => User::class,
        'causer_id' => $this->user->id,
    ]);
});

// ============================================================================
// Authorization Tests
// ============================================================================

test('user without individual profile cannot access page', function () {
    $userWithoutIndividual = User::factory()
        ->for($this->individualGroup, 'group')
        ->create();
    actingAs($userWithoutIndividual);

    $response = get(route('individual.diving_professionals.index'));

    $response->assertStatus(403);
});
