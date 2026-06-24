<?php

use App\Models\Group;
use App\Models\Sport;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityAthlete;
use Domain\Entities\Models\EntityProfessionalRoleInvitation;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Entities\States\RejectedEntityProfessionalRoleState;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

beforeEach(function () {
    $individualGroup = Group::firstOrCreate(['code' => 'INDIVIDUAL'], ['name' => 'Individual']);

    $user = User::factory()
        ->for($individualGroup, 'group')
        ->has(Individual::factory(), 'individual')
        ->create();

    $this->user = $user;
    $this->individual = $user->individual;
    $this->entity = Entity::factory()->create();
    $this->sport = Sport::factory()->create();

    $this->athleteRole = ProfessionalRole::firstOrCreate(
        ['role' => 'ATHLETE'],
        ['name' => 'Athlete', 'code' => 'ATHLETE']
    );

    actingAs($this->user);
});

test('accepting athlete invitation updates EntityAthlete and removes invitation record', function () {
    // Arrange: Create pending EntityAthlete record
    $entityAthlete = EntityAthlete::create([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'sport_id' => $this->sport->id,
        'entity_name' => $this->entity->legal_name,
        'individual_name' => $this->individual->full_name,
        'sport_name' => $this->sport->name,
        'status_class' => PendingEntityProfessionalRoleState::class,
    ]);

    // Arrange: Create pending EntityProfessionalRoleInvitation record
    EntityProfessionalRoleInvitation::create([
        'entity_id' => $this->entity->id,
        'inviting_entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'invited_user_id' => $this->user->id,
        'professional_role_id' => $this->athleteRole->id,
        'status_class' => PendingEntityProfessionalRoleState::class,
        'status' => 'pending',
        'message' => 'Test invitation',
        'expires_at' => now()->addDays(7),
    ]);

    // Act: Accept the invitation
    $response = $this->put(route('individual.athlete.response', $entityAthlete->id), [
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);

    // Assert: Response is successful
    $response->assertRedirect();
    $response->assertSessionHas('success');

    // Assert: EntityAthlete is now Active
    assertDatabaseHas('entity_athletes', [
        'id' => $entityAthlete->id,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);

    // Assert: EntityProfessionalRoleInvitation is deleted (invitation served its purpose)
    assertDatabaseMissing('entity_professional_role_invitations', [
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'status_class' => PendingEntityProfessionalRoleState::class,
    ]);
});

test('rejecting athlete invitation updates EntityAthlete and removes invitation record', function () {
    // Arrange: Create pending EntityAthlete record
    $entityAthlete = EntityAthlete::create([
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'sport_id' => $this->sport->id,
        'entity_name' => $this->entity->legal_name,
        'individual_name' => $this->individual->full_name,
        'sport_name' => $this->sport->name,
        'status_class' => PendingEntityProfessionalRoleState::class,
    ]);

    // Arrange: Create pending EntityProfessionalRoleInvitation record
    EntityProfessionalRoleInvitation::create([
        'entity_id' => $this->entity->id,
        'inviting_entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'invited_user_id' => $this->user->id,
        'professional_role_id' => $this->athleteRole->id,
        'status_class' => PendingEntityProfessionalRoleState::class,
        'status' => 'pending',
        'message' => 'Test invitation',
        'expires_at' => now()->addDays(7),
    ]);

    // Act: Reject the invitation
    $response = $this->put(route('individual.athlete.response', $entityAthlete->id), [
        'status_class' => RejectedEntityProfessionalRoleState::class,
    ]);

    // Assert: Response is successful
    $response->assertRedirect();
    $response->assertSessionHas('success');

    // Assert: EntityAthlete is now Rejected
    assertDatabaseHas('entity_athletes', [
        'id' => $entityAthlete->id,
        'status_class' => RejectedEntityProfessionalRoleState::class,
    ]);

    // Assert: EntityProfessionalRoleInvitation is deleted (invitation served its purpose)
    assertDatabaseMissing('entity_professional_role_invitations', [
        'entity_id' => $this->entity->id,
        'individual_id' => $this->individual->id,
        'status_class' => PendingEntityProfessionalRoleState::class,
    ]);
});
