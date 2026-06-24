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
use Domain\Licenses\States\ExpiredLicenseAttributedState; // Assuming an inactive state exists
use Illuminate\Foundation\Testing\RefreshDatabase; // Import RefreshDatabase
use Illuminate\Support\Facades\URL;
use Spatie\Activitylog\Models\Activity;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\get; // Import Activity model

// Use RefreshDatabase trait
uses(RefreshDatabase::class);

// Helper to enable activity logging if needed (adjust based on your project's setup)
// beforeEach(fn() => \Spatie\Activitylog\ActivitylogServiceProvider::enable());

beforeEach(function () {
    // --- Ensure INDIVIDUAL Group exists ---
    $individualGroup = Group::firstOrCreate(['code' => 'INDIVIDUAL'], ['name' => 'Individual']);

    // --- Committees ---
    $this->committeeDiving = Committee::factory()->create(['code' => 'DIVING', 'name' => 'Diving']);
    $this->committeeScientific = Committee::factory()->create(['code' => 'SCIENTIFIC', 'name' => 'Scientific']);

    // --- Professional Roles & Corresponding Licenses ---
    $this->roleDivingInstructor = ProfessionalRole::factory()->for($this->committeeDiving)->create(['role' => 'INSTRUCTOR', 'name' => 'Diving Instructor M1']);
    $this->licenseDivingInstructor = License::factory()->for($this->roleDivingInstructor)->create();

    $this->roleDivingLeader = ProfessionalRole::factory()->for($this->committeeDiving)->create(['role' => 'LEADER', 'name' => 'Diving Leader']);
    $this->licenseDivingLeader = License::factory()->for($this->roleDivingLeader)->create();

    $this->roleScientificInstructor = ProfessionalRole::factory()->for($this->committeeScientific)->create(['role' => 'INSTRUCTOR', 'name' => 'Scientific Instructor']);
    $this->licenseScientificInstructor = License::factory()->for($this->roleScientificInstructor)->create();

    $this->roleIrrelevant = ProfessionalRole::factory()->for($this->committeeDiving)->create(['role' => 'OTHER', 'name' => 'Irrelevant Diving Role']); // Not INSTRUCTOR/LEADER
    $this->licenseIrrelevant = License::factory()->for($this->roleIrrelevant)->create();

    // --- User & Individual (MODIFIED) ---
    // Explicitly assign the user to the 'INDIVIDUAL' group
    $user = User::factory()
        ->for($individualGroup, 'group') // Assign the specific group
        ->has(Individual::factory(), 'individual')
        ->create();
    $this->user = $user;
    $this->individual = $this->user->individual;

    // --- Entity ---
    $this->entity = Entity::factory()->create();

    // --- License Attributions (Corrected polymorphic relation) ---
    // 1. Active license for a relevant role (Diving Instructor M1)
    LicenseAttributed::factory()
        ->for($this->licenseDivingInstructor, 'license')
        ->create([
            'model_id' => $this->individual->id,
            'model_type' => 'individual', // Use morph map alias
            'status_class' => ActiveLicenseAttributedState::class,
        ]);

    // 2. *Inactive* license for another relevant role (Diving Leader)
    LicenseAttributed::factory()
        ->for($this->licenseDivingLeader, 'license')
        ->create([
            'model_id' => $this->individual->id,
            'model_type' => 'individual', // Use morph map alias
            'status_class' => ExpiredLicenseAttributedState::class,
        ]);

    // 3. Active license for an *irrelevant* role (Other Diving Role)
    LicenseAttributed::factory()
        ->for($this->licenseIrrelevant, 'license')
        ->create([
            'model_id' => $this->individual->id,
            'model_type' => 'individual', // Use morph map alias
            'status_class' => ActiveLicenseAttributedState::class,
        ]);

    // 4. Active license for a role in a *different* committee (Scientific)
    LicenseAttributed::factory()
        ->for($this->licenseScientificInstructor, 'license')
        ->create([
            'model_id' => $this->individual->id,
            'model_type' => 'individual', // Use morph map alias
            'status_class' => ActiveLicenseAttributedState::class,
        ]);

    // --- Log in as the user ---
    $loggedInUser = User::find($this->user->id); // Fetch the model instance
    actingAs($loggedInUser);
});

test('it correctly accepts invitation and associates qualified roles', function () {
    // Arrange: Create the pending invitation record first
    $pendingInvitation = EntityProfessionalRoleInvitation::create([
        'inviting_entity_id' => $this->entity->id,
        'invited_user_id' => $this->user->id,
        'committee_code' => $this->committeeDiving->code,
        'status' => 'pending',
        'expires_at' => now()->addDays(7),
    ]);

    // Generate signed URL for the DIVING committee context
    $acceptUrl = URL::temporarySignedRoute(
        'instructor-invitations.accept',
        now()->addMinutes(5),
        [
            'entityId' => $this->entity->id,
            'userId' => $this->user->id,
            'committeeCode' => $this->committeeDiving->code,
        ]
    );

    // Act
    $response = get($acceptUrl);

    // Assertions
    $response->assertRedirect();
    $response->assertSessionHas('success');

    // Database Assertions (EntityProfessionalRole - qualified roles)
    assertDatabaseHas('entity_professional_role', [
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->roleDivingInstructor->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);
    assertDatabaseMissing('entity_professional_role', [
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->roleDivingLeader->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);
    assertDatabaseMissing('entity_professional_role', [
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->roleIrrelevant->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);
    assertDatabaseMissing('entity_professional_role', [
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->roleScientificInstructor->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);

    // Database Assertion (EntityProfessionalRoleInvitation - status updated)
    assertDatabaseHas('entity_professional_role_invitations', [
        'id' => $pendingInvitation->id,
        'status' => 'accepted',
    ]);

    // Activity Log Assertion (uses morph map alias)
    assertDatabaseHas('activity_log', [
        'log_name' => 'default',
        'description' => 'Accepted generic invitation from entity ' . $this->entity->name . ' for committee ' . $this->committeeDiving->code . '. Activated 1 role(s).',
        'subject_type' => 'individual',
        'subject_id' => $this->individual->id,
        'causer_type' => User::class,
        'causer_id' => $this->user->id,
    ]);
});

test('it redirects with warning if no active qualifying licenses found', function () {
    // Arrange: Create the pending invitation record first
    $pendingInvitation = EntityProfessionalRoleInvitation::create([
        'inviting_entity_id' => $this->entity->id,
        'invited_user_id' => $this->user->id,
        'committee_code' => $this->committeeDiving->code,
        'status' => 'pending',
        'expires_at' => now()->addDays(7),
    ]);

    // Arrange: Remove the only active qualifying license attribution
    LicenseAttributed::where('model_id', $this->individual->id)
        ->where('model_type', 'individual')
        ->where('license_id', $this->licenseDivingInstructor->id)
        ->delete();

    // Generate signed URL
    $acceptUrl = URL::temporarySignedRoute(
        'instructor-invitations.accept',
        now()->addMinutes(5),
        [
            'entityId' => $this->entity->id,
            'userId' => $this->user->id,
            'committeeCode' => $this->committeeDiving->code,
        ]
    );

    // Act
    $response = get($acceptUrl);

    // Assertions
    $response->assertRedirect();
    $response->assertSessionHas('warning');

    // Database Assertions (EntityProfessionalRole - remain the same)
    assertDatabaseMissing('entity_professional_role', [
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->roleDivingInstructor->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);
    assertDatabaseMissing('entity_professional_role', [
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->roleDivingLeader->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);

    // Database Assertion (EntityProfessionalRoleInvitation - status updated)
    assertDatabaseHas('entity_professional_role_invitations', [
        'id' => $pendingInvitation->id,
        'status' => 'accepted', // Still marked accepted even if no roles qualified
    ]);

    // Activity Log Assertion (uses morph map alias)
    assertDatabaseHas('activity_log', [
        'description' => 'Accepted generic invitation from entity ' . $this->entity->name . ' for committee ' . $this->committeeDiving->code . ', but no active qualifying licenses found.',
        'subject_type' => 'individual',
        'subject_id' => $this->individual->id,
        'causer_type' => User::class,
        'causer_id' => $this->user->id,
    ]);
});

test('it correctly rejects invitation', function () {
    // Arrange: Create the pending invitation record first
    $pendingInvitation = EntityProfessionalRoleInvitation::create([
        'inviting_entity_id' => $this->entity->id,
        'invited_user_id' => $this->user->id,
        'committee_code' => $this->committeeDiving->code,
        'status' => 'pending',
        'expires_at' => now()->addDays(7),
    ]);

    // Generate signed URL for rejection
    $rejectUrl = URL::temporarySignedRoute(
        'instructor-invitations.reject',
        now()->addMinutes(5),
        [
            'entityId' => $this->entity->id,
            'userId' => $this->user->id,
            'committeeCode' => $this->committeeDiving->code,
        ]
    );

    // Act
    $response = get($rejectUrl);

    // Assertions
    $response->assertRedirect();
    $response->assertSessionHas('info');

    // Database Assertion (EntityProfessionalRole - remain the same)
    assertDatabaseMissing('entity_professional_role', [
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
    ]);

    // Database Assertion (EntityProfessionalRoleInvitation - status updated)
    assertDatabaseHas('entity_professional_role_invitations', [
        'id' => $pendingInvitation->id,
        'status' => 'rejected',
    ]);

    // Activity Log Assertion (uses morph map alias)
    assertDatabaseHas('activity_log', [
        'description' => 'Rejected generic invitation from entity ' . $this->entity->name . ' for committee ' . $this->committeeDiving->code,
        'subject_type' => 'individual',
        'subject_id' => $this->individual->id,
        'causer_type' => User::class,
        'causer_id' => $this->user->id,
    ]);
});

test('it prevents access for mismatched user', function () {
    // Ensure the INDIVIDUAL group exists
    $individualGroup = Group::firstWhere('code', 'INDIVIDUAL');

    // Create another user in the correct group
    $anotherUser = User::factory()->for($individualGroup, 'group')->create();

    $acceptUrl = URL::temporarySignedRoute(
        'instructor-invitations.accept',
        now()->addMinutes(5),
        [
            'entityId' => $this->entity->id,
            'userId' => $this->user->id, // URL signed for original user
            'committeeCode' => $this->committeeDiving->code,
        ]
    );

    // Act as the *wrong* user
    $wrongUser = User::find($anotherUser->id); // Fetch the model instance
    actingAs($wrongUser);
    $response = get($acceptUrl);

    // ASSERT 403: Expect Forbidden because the signed URL context likely doesn't match the logged-in user
    $response->assertStatus(403);
});

test('it prevents access with invalid signature', function () {
    $acceptUrl = URL::temporarySignedRoute(
        'instructor-invitations.accept',
        now()->addMinutes(5),
        [
            'entityId' => $this->entity->id, // Use ID
            'userId' => $this->user->id,     // Use ID
            'committeeCode' => $this->committeeDiving->code,
        ]
    );

    // Tamper with the URL
    $invalidUrl = $acceptUrl . 'invalid';

    $response = get($invalidUrl);

    $response->assertStatus(403);
});

test('individual can remove their role association and it cleans up the invitation', function () {
    // Arrange: Create an accepted invitation and the resulting role association
    $acceptedInvitation = EntityProfessionalRoleInvitation::create([
        'inviting_entity_id' => $this->entity->id,
        'invited_user_id' => $this->user->id,
        'committee_code' => $this->committeeDiving->code,
        'status' => 'accepted',
        'expires_at' => now()->addDays(7), // Expiry doesn't matter much here
    ]);

    $entityRole = EntityProfessionalRole::firstOrCreate([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'professional_role_id' => $this->roleDivingInstructor->id, // The role created by accepting
        'status_class' => ActiveEntityProfessionalRoleState::class,
        'entity_name' => $this->entity->name,
        'individual_name' => $this->individual->name,
        'role_name' => $this->roleDivingInstructor->name,
    ]);

    // Ensure the user is logged in (already done in beforeEach)
    $loggedInUser = User::find($this->user->id);
    actingAs($loggedInUser);

    // Define the expected route name (adjust if different in your web.php)
    $destroyRoute = route('individual.instructor.delete', ['id' => $entityRole->id]);

    // Act: Simulate the DELETE request with required action parameter
    $response = $this->delete($destroyRoute, ['action' => 'delete']);

    // Assertions
    $response->assertRedirect(); // Or assertRedirectToRoute if applicable
    $response->assertSessionHas('success');

    // Assert the role association is deleted
    assertDatabaseMissing('entity_professional_role', [
        'id' => $entityRole->id,
    ]);

    // Assert the corresponding invitation is also deleted
    assertDatabaseMissing('entity_professional_role_invitations', [
        'id' => $acceptedInvitation->id, // Check the specific invitation ID
        // Optionally add more constraints if needed, though ID should suffice
        // 'inviting_entity_id' => $this->entity->id,
        // 'invited_user_id' => $this->user->id,
        // 'committee_code' => $this->committeeDiving->code,
    ]);

    // Optional: Assert Activity Log if applicable
    // assertDatabaseHas('activity_log', [...]);
});

// Add test for expired signature if needed using Time::travel()
