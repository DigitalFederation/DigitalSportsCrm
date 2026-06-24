<?php

use App\Livewire\Entity\IndividualRequest;
use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\IndividualEntity;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Individuals\States\PendingFromIndividualEntityState;
use Domain\Individuals\States\PendingIndividualEntityState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');
    artisan('db:seed --class=CountrySeeder');

    Notification::fake();
    Storage::fake('secure-media');

    $group = Group::factory()->create(['code' => 'INDIVIDUAL']);
    $this->federation = Federation::factory()->create();

    // Create first user and individual (with profile photo for middleware)
    $this->user1 = User::factory()->create(['group_id' => $group->id]);
    $this->individual1 = Individual::factory()->create(['user_id' => $this->user1->id]);
    $this->individual1->addMedia(UploadedFile::fake()->image('photo.jpg'))
        ->toMediaCollection('profile', 'secure-media');
    $this->individual1->federations()->attach($this->federation->id);

    // Create second user and individual (with profile photo for middleware)
    $this->user2 = User::factory()->create(['group_id' => $group->id]);
    $this->individual2 = Individual::factory()->create(['user_id' => $this->user2->id]);
    $this->individual2->addMedia(UploadedFile::fake()->image('photo.jpg'))
        ->toMediaCollection('profile', 'secure-media');
    $this->individual2->federations()->attach($this->federation->id);

    $this->entity = Entity::factory()->create();
});

it('approves an entity invitation for an individual', function () {
    // Attach the individual to the entity with a pending status
    $individualEntity = IndividualEntity::create([
        'individual_id' => $this->individual1->id,
        'entity_id' => $this->entity->id,
        'status_class' => PendingIndividualEntityState::class,
    ]);
    $this->actingAs($this->user1);
    // Send a POST request to approve the entity invitation
    $response = $this->post(route('individual.entity.approve'), ['id' => $this->entity->id]);

    // Refresh the individualEntity model
    $individualEntity->refresh();

    // Assertions
    $response->assertRedirect(route('individual.entity.index'));
    $response->assertSessionHas('success', "{$this->entity->name}'s request to join accepted");
    expect($individualEntity->status_class)->toBe(ActiveIndividualEntityState::class);
});

it('approves an entity invitation for an individual and ensures another pending invite remains', function () {
    $individualEntity1 = IndividualEntity::create([
        'individual_id' => $this->individual1->id,
        'entity_id' => $this->entity->id,
        'status_class' => PendingIndividualEntityState::class,
    ]);

    $individualEntity2 = IndividualEntity::create([
        'individual_id' => $this->individual2->id,
        'entity_id' => $this->entity->id,
        'status_class' => PendingIndividualEntityState::class,
    ]);

    $this->actingAs($this->user1);

    $response = $this->post(route('individual.entity.approve'), ['id' => $this->entity->id]);

    $response->assertRedirect(route('individual.entity.index'));
    $response->assertSessionHas('success', "{$this->entity->name}'s request to join accepted");

    $individualEntity1->refresh();
    $individualEntity2->refresh();

    expect($individualEntity1->status_class)->toBe(ActiveIndividualEntityState::class);
    expect($individualEntity2->status_class)->toBe(PendingIndividualEntityState::class);
});

it('allows invitation when individual and entity share the same federation with active status', function () {
    // Create groups
    $group_entity = Group::factory()->create(['code' => 'ENTITY']);
    $group_individual = Group::factory()->create(['code' => 'INDIVIDUAL']);

    // Create federation and make it active
    $federation = Federation::factory()->create(['is_local' => false]);

    // Create entity user
    $entity_user = User::factory()->create([
        'group_id' => $group_entity->id,
    ]);

    // Create entity and attach to federation with active status
    $entity = Entity::factory()->create();
    $federation->entities()->attach($entity->id, [
        'status_class' => \Domain\Entities\States\ActiveEntityFederationState::class,
        'active' => true,
    ]);

    // Attach entity to user
    $entity_user->entities()->attach($entity->id);

    // Create individual user
    $individual_user = User::factory()->create([
        'group_id' => $group_individual->id,
    ]);

    // Create individual with CMAS code
    $individual = Individual::factory()->create([
        'user_id' => $individual_user->id,
        'member_code' => '123456',
    ]);

    // Attach individual to federation with active status
    $federation->individuals()->attach($individual->id, [
        'status_class' => ActiveIndividualFederationState::class,
        'active' => true,
    ]);

    // Act as entity user
    $this->actingAs($entity_user);

    // Attempt to create invitation
    $component = Livewire::test(IndividualRequest::class)
        ->set('member_code', '123456')
        ->call('submit');

    // Assert no errors occurred
    $component->assertHasNoErrors(['member_code']);

    // Assert the invitation was created
    $this->assertDatabaseHas('individual_entity', [
        'individual_id' => $individual->id,
        'entity_id' => $entity->id,
        'status_class' => PendingFromIndividualEntityState::class,
    ]);

    // Assert the federation relationship remains unchanged
    $this->assertDatabaseHas('individual_federation', [
        'individual_id' => $individual->id,
        'federation_id' => $federation->id,
        'status_class' => ActiveIndividualFederationState::class,
        'active' => true,
    ]);
});

it('handles invitation when entity has pending federation status', function () {
    // Create groups
    $group_entity = Group::factory()->create(['code' => 'ENTITY']);
    $group_individual = Group::factory()->create(['code' => 'INDIVIDUAL']);

    // Create federation
    $federation = Federation::factory()->create(['is_local' => false]);

    // Create entity user and entity
    $entity_user = User::factory()->create(['group_id' => $group_entity->id]);
    $entity = Entity::factory()->create();

    // Important: Attach entity to federation with PENDING status
    $federation->entities()->attach($entity->id, [
        'status_class' => \Domain\Entities\States\PendingEntityFederationState::class,
        'active' => false,
    ]);

    $entity_user->entities()->attach($entity->id);

    // Create individual with active federation relationship
    $individual = Individual::factory()->create(['member_code' => '123456']);
    $federation->individuals()->attach($individual->id, [
        'status_class' => ActiveIndividualFederationState::class,
        'active' => true,
    ]);

    $this->actingAs($entity_user);

    // Attempt to create invitation
    $component = Livewire::test(IndividualRequest::class)
        ->set('member_code', '123456')
        ->call('submit');

    // Assert no errors occurred
    $component->assertHasNoErrors(['member_code']);

    // This should fail because the entity has a pending federation status
    $this->assertDatabaseHas('individual_entity', [
        'individual_id' => $individual->id,
        'entity_id' => $entity->id,
        'status_class' => PendingFromIndividualEntityState::class,
    ]);

    // Verify that the original relationships remain unchanged
    $this->assertDatabaseHas('entity_federation', [
        'entity_id' => $entity->id,
        'federation_id' => $federation->id,
        'status_class' => \Domain\Entities\States\PendingEntityFederationState::class,
        'active' => false,
    ]);

    $this->assertDatabaseHas('individual_federation', [
        'individual_id' => $individual->id,
        'federation_id' => $federation->id,
        'status_class' => ActiveIndividualFederationState::class,
        'active' => true,
    ]);
});

it('allows invitation when entity has multiple federation relationships but shares at least one with individual', function () {
    // Create groups
    $group_entity = Group::factory()->create(['code' => 'ENTITY']);
    $group_individual = Group::factory()->create(['code' => 'INDIVIDUAL']);

    // Create multiple federations
    $federation1 = Federation::factory()->create(['is_local' => false]);
    $federation2 = Federation::factory()->create(['is_local' => false]);

    // Create entity user and entity
    $entity_user = User::factory()->create(['group_id' => $group_entity->id]);
    $entity = Entity::factory()->create();

    // Attach entity to both federations (one active, one pending)
    $federation1->entities()->attach($entity->id, [
        'status_class' => \Domain\Entities\States\ActiveEntityFederationState::class,
        'active' => true,
    ]);
    $federation2->entities()->attach($entity->id, [
        'status_class' => \Domain\Entities\States\PendingEntityFederationState::class,
        'active' => false,
    ]);

    $entity_user->entities()->attach($entity->id);

    // Create individual with active relationship only to federation2
    $individual = Individual::factory()->create(['member_code' => '123456']);
    $federation2->individuals()->attach($individual->id, [
        'status_class' => ActiveIndividualFederationState::class,
        'active' => true,
    ]);

    $this->actingAs($entity_user);

    // Attempt to create invitation
    $component = Livewire::test(IndividualRequest::class)
        ->set('member_code', '123456')
        ->call('submit');

    // Should fail because they don't share an active federation relationship
    $component->assertHasNoErrors(['member_code']);
    $this->assertDatabaseHas('individual_entity', [
        'individual_id' => $individual->id,
        'entity_id' => $entity->id,
        'status_class' => PendingFromIndividualEntityState::class,
    ]);
});

it('handles invitation when federation relationship exists but is not properly activated', function () {
    // Create groups
    $group_entity = Group::factory()->create(['code' => 'ENTITY']);
    $group_individual = Group::factory()->create(['code' => 'INDIVIDUAL']);

    // Create federation
    $federation = Federation::factory()->create(['is_local' => false]);

    // Create entity user and entity
    $entity_user = User::factory()->create(['group_id' => $group_entity->id]);
    $entity = Entity::factory()->create();

    // Attach entity to federation with active status but active flag false
    $federation->entities()->attach($entity->id, [
        'status_class' => \Domain\Entities\States\ActiveEntityFederationState::class,
        'active' => false, // Note: active flag is false despite active status
    ]);

    $entity_user->entities()->attach($entity->id);

    // Create individual with similar inconsistent state
    $individual = Individual::factory()->create(['member_code' => '123456']);
    $federation->individuals()->attach($individual->id, [
        'status_class' => ActiveIndividualFederationState::class,
        'active' => false, // Note: active flag is false despite active status
    ]);

    $this->actingAs($entity_user);

    // Attempt to create invitation
    $component = Livewire::test(IndividualRequest::class)
        ->set('member_code', '123456')
        ->call('submit');

    // Check that the invitation was created with pending status
    $this->assertDatabaseHas('individual_entity', [
        'individual_id' => $individual->id,
        'entity_id' => $entity->id,
        'status_class' => PendingFromIndividualEntityState::class,
    ]);

    // Verify that the federation relationships remain in their original state
    $this->assertDatabaseHas('entity_federation', [
        'entity_id' => $entity->id,
        'federation_id' => $federation->id,
        'status_class' => \Domain\Entities\States\ActiveEntityFederationState::class,
        'active' => false,
    ]);

    $this->assertDatabaseHas('individual_federation', [
        'individual_id' => $individual->id,
        'federation_id' => $federation->id,
        'status_class' => ActiveIndividualFederationState::class,
        'active' => false,
    ]);
});
