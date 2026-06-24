<?php

use App\Models\Committee;
use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityProfessionalRole;
use Domain\Entities\Models\EntityProfessionalRoleInvitation;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\ExpiredLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Ensure INDIVIDUAL Group exists
    $individualGroup = Group::firstOrCreate(['code' => 'INDIVIDUAL'], ['name' => 'Individual']);

    // SCIENTIFIC Committee
    $this->committeeScientific = Committee::factory()->create(['code' => 'SCIENTIFIC', 'name' => 'Scientific']);

    // DIVING Committee (for cross-committee tests)
    $this->committeeDiving = Committee::factory()->create(['code' => 'DIVING', 'name' => 'Diving']);

    // Professional Roles for Scientific Committee
    $this->roleScientificInstructor = ProfessionalRole::factory()
        ->for($this->committeeScientific)
        ->create(['role' => 'INSTRUCTOR', 'name' => 'Scientific Instructor']);
    $this->licenseScientificInstructor = License::factory()
        ->for($this->roleScientificInstructor)
        ->create();

    $this->roleScientificLeader = ProfessionalRole::factory()
        ->for($this->committeeScientific)
        ->create(['role' => 'LEADER', 'name' => 'Scientific Leader']);
    $this->licenseScientificLeader = License::factory()
        ->for($this->roleScientificLeader)
        ->create();

    // Diving role (for cross-committee validation)
    $this->roleDivingInstructor = ProfessionalRole::factory()
        ->for($this->committeeDiving)
        ->create(['role' => 'INSTRUCTOR', 'name' => 'Diving Instructor']);
    $this->licenseDivingInstructor = License::factory()
        ->for($this->roleDivingInstructor)
        ->create();

    // User & Individual
    $user = User::factory()
        ->for($individualGroup, 'group')
        ->has(Individual::factory(), 'individual')
        ->create();
    $this->user = $user;
    $this->individual = $this->user->individual;

    // Entity
    $this->entity = Entity::factory()->create();

    // Active Scientific Instructor license
    // NOTE: Must use 'individual' (morph map alias), not Individual::class
    LicenseAttributed::factory()
        ->for($this->licenseScientificInstructor, 'license')
        ->create([
            'model_id' => $this->individual->id,
            'model_type' => 'individual',
            'status_class' => ActiveLicenseAttributedState::class,
        ]);

    // Expired Scientific Leader license
    LicenseAttributed::factory()
        ->for($this->licenseScientificLeader, 'license')
        ->create([
            'model_id' => $this->individual->id,
            'model_type' => 'individual',
            'status_class' => ExpiredLicenseAttributedState::class,
        ]);

    // Active Diving license (should NOT be activated for Scientific invitation)
    LicenseAttributed::factory()
        ->for($this->licenseDivingInstructor, 'license')
        ->create([
            'model_id' => $this->individual->id,
            'model_type' => 'individual',
            'status_class' => ActiveLicenseAttributedState::class,
        ]);

    actingAs($this->user);
});

test('scientific invitation: correctly accepts and associates qualified roles', function () {
    // Arrange: Create pending invitation for SCIENTIFIC committee
    $pendingInvitation = EntityProfessionalRoleInvitation::create([
        'inviting_entity_id' => $this->entity->id,
        'invited_user_id' => $this->user->id,
        'committee_code' => $this->committeeScientific->code,
        'status' => 'pending',
        'expires_at' => now()->addDays(7),
    ]);

    $acceptUrl = URL::temporarySignedRoute(
        'instructor-invitations.accept',
        now()->addMinutes(5),
        [
            'entityId' => $this->entity->id,
            'userId' => $this->user->id,
            'committeeCode' => $this->committeeScientific->code,
        ]
    );

    // Act
    $response = get($acceptUrl);

    // Assertions
    $response->assertRedirect();
    $response->assertSessionHas('success');

    // Should create EntityProfessionalRole for Scientific Instructor (active license)
    assertDatabaseHas('entity_professional_role', [
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->roleScientificInstructor->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);

    // Should NOT create for Scientific Leader (expired license)
    assertDatabaseMissing('entity_professional_role', [
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->roleScientificLeader->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);

    // Should NOT create for Diving Instructor (wrong committee)
    assertDatabaseMissing('entity_professional_role', [
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->roleDivingInstructor->id,
    ]);

    // Invitation should be marked as accepted
    assertDatabaseHas('entity_professional_role_invitations', [
        'id' => $pendingInvitation->id,
        'status' => 'accepted',
    ]);
});

test('scientific invitation: correctly rejects invitation', function () {
    $pendingInvitation = EntityProfessionalRoleInvitation::create([
        'inviting_entity_id' => $this->entity->id,
        'invited_user_id' => $this->user->id,
        'committee_code' => $this->committeeScientific->code,
        'status' => 'pending',
        'expires_at' => now()->addDays(7),
    ]);

    $rejectUrl = URL::temporarySignedRoute(
        'instructor-invitations.reject',
        now()->addMinutes(5),
        [
            'entityId' => $this->entity->id,
            'userId' => $this->user->id,
            'committeeCode' => $this->committeeScientific->code,
        ]
    );

    // Act
    $response = get($rejectUrl);

    // Assertions
    $response->assertRedirect();
    $response->assertSessionHas('info');

    // No EntityProfessionalRole should be created
    assertDatabaseMissing('entity_professional_role', [
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
    ]);

    // Invitation should be marked as rejected
    assertDatabaseHas('entity_professional_role_invitations', [
        'id' => $pendingInvitation->id,
        'status' => 'rejected',
    ]);
});

test('scientific invitation: activates multiple roles when individual has multiple active licenses', function () {
    // Update Scientific Leader license to active
    LicenseAttributed::where('model_id', $this->individual->id)
        ->where('model_type', 'individual')
        ->where('license_id', $this->licenseScientificLeader->id)
        ->update(['status_class' => ActiveLicenseAttributedState::class]);

    $pendingInvitation = EntityProfessionalRoleInvitation::create([
        'inviting_entity_id' => $this->entity->id,
        'invited_user_id' => $this->user->id,
        'committee_code' => $this->committeeScientific->code,
        'status' => 'pending',
        'expires_at' => now()->addDays(7),
    ]);

    $acceptUrl = URL::temporarySignedRoute(
        'instructor-invitations.accept',
        now()->addMinutes(5),
        [
            'entityId' => $this->entity->id,
            'userId' => $this->user->id,
            'committeeCode' => $this->committeeScientific->code,
        ]
    );

    // Act
    $response = get($acceptUrl);

    // Assertions - should activate 2 roles
    $response->assertRedirect();
    $response->assertSessionHas('success');

    // Both roles should be created
    assertDatabaseHas('entity_professional_role', [
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->roleScientificInstructor->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);

    assertDatabaseHas('entity_professional_role', [
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->roleScientificLeader->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);
});

test('scientific invitation: warns when no active qualifying licenses found', function () {
    // Remove the Scientific active license
    LicenseAttributed::where('model_id', $this->individual->id)
        ->where('model_type', 'individual')
        ->where('license_id', $this->licenseScientificInstructor->id)
        ->delete();

    $pendingInvitation = EntityProfessionalRoleInvitation::create([
        'inviting_entity_id' => $this->entity->id,
        'invited_user_id' => $this->user->id,
        'committee_code' => $this->committeeScientific->code,
        'status' => 'pending',
        'expires_at' => now()->addDays(7),
    ]);

    $acceptUrl = URL::temporarySignedRoute(
        'instructor-invitations.accept',
        now()->addMinutes(5),
        [
            'entityId' => $this->entity->id,
            'userId' => $this->user->id,
            'committeeCode' => $this->committeeScientific->code,
        ]
    );

    // Act
    $response = get($acceptUrl);

    // Assertions
    $response->assertRedirect();
    $response->assertSessionHas('warning');

    // No EntityProfessionalRole should be created
    assertDatabaseMissing('entity_professional_role', [
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->roleScientificInstructor->id,
    ]);

    // Invitation should still be marked as accepted
    assertDatabaseHas('entity_professional_role_invitations', [
        'id' => $pendingInvitation->id,
        'status' => 'accepted',
    ]);
});

test('scientific invitation: does not allow accepting already accepted invitation', function () {
    // Create already accepted invitation
    EntityProfessionalRoleInvitation::create([
        'inviting_entity_id' => $this->entity->id,
        'invited_user_id' => $this->user->id,
        'committee_code' => $this->committeeScientific->code,
        'status' => 'accepted',
        'expires_at' => now()->addDays(7),
    ]);

    $acceptUrl = URL::temporarySignedRoute(
        'instructor-invitations.accept',
        now()->addMinutes(5),
        [
            'entityId' => $this->entity->id,
            'userId' => $this->user->id,
            'committeeCode' => $this->committeeScientific->code,
        ]
    );

    // Act
    $response = get($acceptUrl);

    // Assertions
    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('scientific invitation: activity log is created on acceptance', function () {
    $pendingInvitation = EntityProfessionalRoleInvitation::create([
        'inviting_entity_id' => $this->entity->id,
        'invited_user_id' => $this->user->id,
        'committee_code' => $this->committeeScientific->code,
        'status' => 'pending',
        'expires_at' => now()->addDays(7),
    ]);

    $acceptUrl = URL::temporarySignedRoute(
        'instructor-invitations.accept',
        now()->addMinutes(5),
        [
            'entityId' => $this->entity->id,
            'userId' => $this->user->id,
            'committeeCode' => $this->committeeScientific->code,
        ]
    );

    get($acceptUrl);

    // Verify activity log (uses morph map alias 'individual' not full class name)
    assertDatabaseHas('activity_log', [
        'log_name' => 'default',
        'description' => 'Accepted generic invitation from entity ' . $this->entity->name . ' for committee ' . $this->committeeScientific->code . '. Activated 1 role(s).',
        'subject_type' => 'individual',
        'subject_id' => $this->individual->id,
        'causer_type' => User::class,
        'causer_id' => $this->user->id,
    ]);
});

test('scientific invitation: activity log is created on rejection', function () {
    $pendingInvitation = EntityProfessionalRoleInvitation::create([
        'inviting_entity_id' => $this->entity->id,
        'invited_user_id' => $this->user->id,
        'committee_code' => $this->committeeScientific->code,
        'status' => 'pending',
        'expires_at' => now()->addDays(7),
    ]);

    $rejectUrl = URL::temporarySignedRoute(
        'instructor-invitations.reject',
        now()->addMinutes(5),
        [
            'entityId' => $this->entity->id,
            'userId' => $this->user->id,
            'committeeCode' => $this->committeeScientific->code,
        ]
    );

    get($rejectUrl);

    // Verify activity log (uses morph map alias 'individual' not full class name)
    assertDatabaseHas('activity_log', [
        'description' => 'Rejected generic invitation from entity ' . $this->entity->name . ' for committee ' . $this->committeeScientific->code,
        'subject_type' => 'individual',
        'subject_id' => $this->individual->id,
        'causer_type' => User::class,
        'causer_id' => $this->user->id,
    ]);
});
