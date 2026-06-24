<?php

use App\Livewire\Entity\IndividualRequest;
use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Actions\CreateIndividualEntityAction;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\PendingFromIndividualEntityState;
use Domain\Individuals\States\PendingIndividualEntityState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {

    $group_entity = Group::factory()->create(['code' => 'ENTITY']);
    $this->federation = Federation::factory()->create();
    $this->entity = Entity::factory()->create();
    $this->individual = Individual::factory()->create(['member_code' => '123456']);

    // Attach entity to federation with proper status
    $this->federation->entities()->attach($this->entity->id, [
        'status_class' => \Domain\Entities\States\ActiveEntityFederationState::class,
        'active' => true,
    ]);

    // Attach individual to federation with active status
    $this->federation->individuals()->attach($this->individual->id, [
        'status_class' => \Domain\Individuals\States\ActiveIndividualFederationState::class,
        'active' => true,
    ]);

    // Create user and attach to entity
    $this->user = User::factory()->create([
        'group_id' => $group_entity->first()->id,
    ]);
    $this->user->entities()->attach($this->entity->id);
});

it('action directly creates relationship', function () {
    // Setup
    $action = new CreateIndividualEntityAction;

    // Authenticate the user
    $this->actingAs($this->user);

    // Verify prerequisites
    expect($this->federation->entities)->toHaveCount(1)
        ->and($this->federation->individuals)->toHaveCount(1)
        ->and($this->individual->member_code)->toBe('123456');

    // Execute
    $result = $action->execute('123456', $this->entity->id);

    // Verify
    expect($result)->not->toBeNull()
        ->and($result->individual_id)->toBe($this->individual->id)
        ->and($result->entity_id)->toBe($this->entity->id)
        ->and($result->status_class)->toBe(PendingFromIndividualEntityState::class);
});

it('creates an invitation for an eligible individual', function () {
    // Authenticate the user
    $this->actingAs($this->user);
    // 1. Verify federation relationship exists
    $federationEntity = $this->federation->entities()
        ->where('entity_id', $this->entity->id)
        ->first();

    expect($federationEntity)->not->toBeNull()
        ->and($this->federation->id)->not->toBeNull()
        ->and($this->entity->id)->not->toBeNull();

    // 2. Verify individual-federation relationship
    $individualFederation = $this->federation->individuals()
        ->where('individual_id', $this->individual->id)
        ->where('active', true)
        ->first();

    expect($individualFederation)->not->toBeNull();

    // 3. Verify user-entity relationship
    expect($this->user->entities)->toHaveCount(1)
        ->and($this->user->entities->first()->id)->toBe($this->entity->id);

    // 4. Execute the test
    $component = Livewire::actingAs($this->user)
        ->test(IndividualRequest::class)
        ->set('member_code', '123456');

    $component->call('submit')
        ->assertHasNoErrors(['member_code']);

    // 5. Verify the result
    $this->assertDatabaseHas('individual_entity', [
        'individual_id' => $this->individual->id,
        'entity_id' => $this->entity->id,
        'status_class' => PendingFromIndividualEntityState::class,
    ]);
});

it('does not create an invitation for an individual already associated with the entity', function () {
    // Pre-associate the individual with the entity
    $this->individual->entities()->attach($this->entity->id, [
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    Livewire::actingAs($this->user)
        ->test(IndividualRequest::class)
        ->set('member_code', '123456')
        ->call('submit')
        ->assertHasNoErrors(['member_code']);

    $this->assertDatabaseMissing('individual_entity', [
        'individual_id' => $this->individual->id,
        'entity_id' => $this->entity->id,
        'status_class' => PendingIndividualEntityState::class,
    ]);
});

it('shows an error for invalid CMAS code', function () {
    Livewire::actingAs($this->user)
        ->test(IndividualRequest::class)
        ->set('member_code', '999999')
        ->call('submit')
        ->assertHasErrors(['member_code' => 'exists']);
});

it('requires at least one identifier to be filled', function () {
    Livewire::actingAs($this->user)
        ->test(IndividualRequest::class)
        ->set('member_code', '')
        ->set('member_number', '')
        ->call('submit')
        ->assertHasErrors(['member_code' => 'required_without', 'member_number' => 'required_without']);
});

it('creates an invitation using member number', function () {
    $this->individual->update(['member_number' => 'MN-9999']);

    Livewire::actingAs($this->user)
        ->test(IndividualRequest::class)
        ->set('member_number', 'MN-9999')
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('individual_entity', [
        'individual_id' => $this->individual->id,
        'entity_id' => $this->entity->id,
        'status_class' => PendingFromIndividualEntityState::class,
    ]);
});

it('action directly creates relationship using member number', function () {
    $this->individual->update(['member_number' => 'MN-8888']);
    $this->actingAs($this->user);

    $action = new CreateIndividualEntityAction;
    $result = $action->execute(null, $this->entity->id, 'MN-8888');

    expect($result)->not->toBeNull()
        ->and($result->individual_id)->toBe($this->individual->id)
        ->and($result->entity_id)->toBe($this->entity->id)
        ->and($result->status_class)->toBe(PendingFromIndividualEntityState::class);
});

it('shows an error for invalid member number', function () {
    Livewire::actingAs($this->user)
        ->test(IndividualRequest::class)
        ->set('member_number', 'INVALID-000')
        ->call('submit')
        ->assertHasErrors(['member_number' => 'exists']);
});
