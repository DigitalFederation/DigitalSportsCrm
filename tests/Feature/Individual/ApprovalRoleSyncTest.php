<?php

use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\IndividualEntity;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\PendingFromEntityIndividualEntityState;
use Domain\Individuals\States\PendingIndividualEntityState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    artisan('db:seed --class=RoleAndPermissionSeeder');
    artisan('db:seed --class=CountrySeeder');

    Notification::fake();
    Storage::fake('secure-media');

    // Insert the federation_roles mapping for individual-approved
    $roleId = DB::table('roles')
        ->where('name', 'individual-approved')
        ->where('guard_name', 'web')
        ->value('id');

    DB::table('federation_roles')->insertOrIgnore([
        'federation_id' => null,
        'role_id' => $roleId,
        'requires_active_membership' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Set up a local federation with an entity that has active status
    $this->federation = Federation::factory()->create(['is_local' => true]);
    $this->entity = Entity::factory()->create();
    $this->entity->federations()->attach($this->federation->id, [
        'status_class' => ActiveEntityFederationState::class,
        'active' => true,
    ]);
});

it('assigns individual-approved role when entity approves a pending individual', function () {
    $entityGroup = Group::factory()->create(['code' => 'ENTITY']);
    $individualGroup = Group::factory()->create(['code' => 'INDIVIDUAL']);

    // Entity user who will perform the approval
    $entityUser = User::factory()->create(['group_id' => $entityGroup->id]);
    $this->entity->users()->attach($entityUser);

    // Individual waiting for approval
    $individualUser = User::factory()->create(['group_id' => $individualGroup->id]);
    $individual = Individual::factory()->create(['user_id' => $individualUser->id]);
    $individual->federations()->attach($this->federation->id, ['active' => true]);

    IndividualEntity::create([
        'individual_id' => $individual->id,
        'entity_id' => $this->entity->id,
        'status_class' => PendingIndividualEntityState::class,
    ]);

    // Confirm the individual user does NOT have the role yet
    expect($individualUser->hasRole('individual-approved'))->toBeFalse();

    // Entity user approves the individual
    $this->actingAs($entityUser);
    $response = $this->post(route('entity.individual-approve.store'), ['id' => $individual->id]);

    $response->assertRedirect(route('entity.individual.index'));
    $response->assertSessionHas('success');

    // The individual-entity record should now be active
    expect(IndividualEntity::where('individual_id', $individual->id)
        ->where('entity_id', $this->entity->id)
        ->first()
        ->status_class
    )->toBe(ActiveIndividualEntityState::class);

    // Reload the user from DB to pick up fresh role assignments
    $individualUser->refresh();

    expect($individualUser->hasRole('individual-approved'))->toBeTrue();
});

it('assigns individual-approved role when entity approves a PendingFromEntity individual', function () {
    $entityGroup = Group::factory()->create(['code' => 'ENTITY']);
    $individualGroup = Group::factory()->create(['code' => 'INDIVIDUAL']);

    $entityUser = User::factory()->create(['group_id' => $entityGroup->id]);
    $this->entity->users()->attach($entityUser);

    $individualUser = User::factory()->create(['group_id' => $individualGroup->id]);
    $individual = Individual::factory()->create(['user_id' => $individualUser->id]);
    $individual->federations()->attach($this->federation->id, ['active' => true]);

    IndividualEntity::create([
        'individual_id' => $individual->id,
        'entity_id' => $this->entity->id,
        'status_class' => PendingFromEntityIndividualEntityState::class,
    ]);

    expect($individualUser->hasRole('individual-approved'))->toBeFalse();

    $this->actingAs($entityUser);
    $response = $this->post(route('entity.individual-approve.store'), ['id' => $individual->id]);

    $response->assertRedirect(route('entity.individual.index'));

    $individualUser->refresh();
    expect($individualUser->hasRole('individual-approved'))->toBeTrue();
});

it('assigns individual-approved role when individual approves an entity join request', function () {
    $individualGroup = Group::factory()->create(['code' => 'INDIVIDUAL']);

    $individualUser = User::factory()->create(['group_id' => $individualGroup->id]);
    $individual = Individual::factory()->create(['user_id' => $individualUser->id]);
    $individual->addMedia(UploadedFile::fake()->image('photo.jpg'))
        ->toMediaCollection('profile', 'secure-media');
    $individual->federations()->attach($this->federation->id, ['active' => true]);

    IndividualEntity::create([
        'individual_id' => $individual->id,
        'entity_id' => $this->entity->id,
        'status_class' => PendingIndividualEntityState::class,
    ]);

    expect($individualUser->hasRole('individual-approved'))->toBeFalse();

    // Individual approves the entity join request
    $this->actingAs($individualUser);
    $response = $this->post(route('individual.entity.approve'), ['id' => $this->entity->id]);

    $response->assertRedirect(route('individual.entity.index'));
    $response->assertSessionHas('success');

    expect(IndividualEntity::where('individual_id', $individual->id)
        ->where('entity_id', $this->entity->id)
        ->first()
        ->status_class
    )->toBe(ActiveIndividualEntityState::class);

    $individualUser->refresh();
    expect($individualUser->hasRole('individual-approved'))->toBeTrue();
});

it('does not assign individual-approved role without federation_roles mapping', function () {
    // Remove the federation_roles mapping so the sync action has nothing to assign
    DB::table('federation_roles')
        ->where('role_id', DB::table('roles')->where('name', 'individual-approved')->value('id'))
        ->delete();

    $individualGroup = Group::factory()->create(['code' => 'INDIVIDUAL']);

    $individualUser = User::factory()->create(['group_id' => $individualGroup->id]);
    $individual = Individual::factory()->create(['user_id' => $individualUser->id]);
    $individual->addMedia(UploadedFile::fake()->image('photo.jpg'))
        ->toMediaCollection('profile', 'secure-media');
    $individual->federations()->attach($this->federation->id, ['active' => true]);

    IndividualEntity::create([
        'individual_id' => $individual->id,
        'entity_id' => $this->entity->id,
        'status_class' => PendingIndividualEntityState::class,
    ]);

    $this->actingAs($individualUser);
    $this->post(route('individual.entity.approve'), ['id' => $this->entity->id]);

    $individualUser->refresh();
    expect($individualUser->hasRole('individual-approved'))->toBeFalse();
});
