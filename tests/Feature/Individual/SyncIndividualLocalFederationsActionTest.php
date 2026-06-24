<?php

use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Entities\States\PendingEntityFederationState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Actions\SyncIndividualLocalFederationsAction;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\IndividualFederation;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Individuals\States\PendingIndividualFederationState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('syncs individual to entity local federations', function () {
    // Create main federation and local federation
    $mainFederation = Federation::factory()->create(['is_local' => false, 'parent_id' => null]);
    $localFederation = Federation::factory()->create([
        'is_local' => true,
        'parent_id' => $mainFederation->id,
    ]);

    // Create entity with active local federation membership
    $entity = Entity::factory()->create();
    $entity->federations()->attach($localFederation->id, [
        'status_class' => ActiveEntityFederationState::class,
    ]);

    // Create individual without local federation membership
    $individual = Individual::factory()->create();

    // Execute action
    $action = new SyncIndividualLocalFederationsAction;
    $syncedFederations = $action->execute($individual, $entity);

    // Assert
    expect($syncedFederations)->toHaveCount(1)
        ->and($syncedFederations->first()->id)->toBe($localFederation->id);

    $individualFederation = IndividualFederation::where('individual_id', $individual->id)
        ->where('federation_id', $localFederation->id)
        ->first();

    expect($individualFederation)->not->toBeNull()
        ->and($individualFederation->status_class)->toBe(ActiveIndividualFederationState::class)
        ->and((int) $individualFederation->active)->toBe(1);
});

it('does not duplicate existing active federation membership', function () {
    // Create local federation
    $localFederation = Federation::factory()->create(['is_local' => true]);

    // Create entity with active local federation membership
    $entity = Entity::factory()->create();
    $entity->federations()->attach($localFederation->id, [
        'status_class' => ActiveEntityFederationState::class,
    ]);

    // Create individual already in local federation
    $individual = Individual::factory()->create();
    IndividualFederation::create([
        'individual_id' => $individual->id,
        'federation_id' => $localFederation->id,
        'status_class' => ActiveIndividualFederationState::class,
        'active' => true,
    ]);

    // Execute action
    $action = new SyncIndividualLocalFederationsAction;
    $syncedFederations = $action->execute($individual, $entity);

    // Assert no new federations synced
    expect($syncedFederations)->toHaveCount(0);

    // Assert only one record exists
    expect(IndividualFederation::where('individual_id', $individual->id)
        ->where('federation_id', $localFederation->id)
        ->count())->toBe(1);
});

it('activates pending individual federation membership', function () {
    // Create local federation
    $localFederation = Federation::factory()->create(['is_local' => true]);

    // Create entity with active local federation membership
    $entity = Entity::factory()->create();
    $entity->federations()->attach($localFederation->id, [
        'status_class' => ActiveEntityFederationState::class,
    ]);

    // Create individual with pending federation membership
    $individual = Individual::factory()->create();
    IndividualFederation::create([
        'individual_id' => $individual->id,
        'federation_id' => $localFederation->id,
        'status_class' => PendingIndividualFederationState::class,
        'active' => false,
    ]);

    // Execute action
    $action = new SyncIndividualLocalFederationsAction;
    $syncedFederations = $action->execute($individual, $entity);

    // Assert federation was activated
    expect($syncedFederations)->toHaveCount(1);

    $individualFederation = IndividualFederation::where('individual_id', $individual->id)
        ->where('federation_id', $localFederation->id)
        ->first();

    expect($individualFederation->status_class)->toBe(ActiveIndividualFederationState::class)
        ->and((int) $individualFederation->active)->toBe(1);
});

it('only syncs local federations not main federations', function () {
    // Create main federation and local federation
    $mainFederation = Federation::factory()->create(['is_local' => false, 'parent_id' => null]);
    $localFederation = Federation::factory()->create([
        'is_local' => true,
        'parent_id' => $mainFederation->id,
    ]);

    // Create entity with both main and local federation
    $entity = Entity::factory()->create();
    $entity->federations()->attach([
        $mainFederation->id => ['status_class' => ActiveEntityFederationState::class],
        $localFederation->id => ['status_class' => ActiveEntityFederationState::class],
    ]);

    // Create individual
    $individual = Individual::factory()->create();

    // Execute action
    $action = new SyncIndividualLocalFederationsAction;
    $syncedFederations = $action->execute($individual, $entity);

    // Assert only local federation was synced
    expect($syncedFederations)->toHaveCount(1)
        ->and($syncedFederations->first()->id)->toBe($localFederation->id);

    // Assert no main federation membership
    expect(IndividualFederation::where('individual_id', $individual->id)
        ->where('federation_id', $mainFederation->id)
        ->exists())->toBeFalse();
});

it('does not sync inactive entity federation memberships', function () {
    // Create local federation
    $localFederation = Federation::factory()->create(['is_local' => true]);

    // Create entity with pending (not active) federation membership
    $entity = Entity::factory()->create();
    $entity->federations()->attach($localFederation->id, [
        'status_class' => PendingEntityFederationState::class,
    ]);

    // Create individual
    $individual = Individual::factory()->create();

    // Execute action
    $action = new SyncIndividualLocalFederationsAction;
    $syncedFederations = $action->execute($individual, $entity);

    // Assert nothing synced
    expect($syncedFederations)->toHaveCount(0);
    expect(IndividualFederation::where('individual_id', $individual->id)->exists())->toBeFalse();
});

it('removes local federation membership on deactivation', function () {
    // Create local federation
    $localFederation = Federation::factory()->create(['is_local' => true]);

    // Create entity with local federation membership
    $entity = Entity::factory()->create();
    $entity->federations()->attach($localFederation->id, [
        'status_class' => ActiveEntityFederationState::class,
    ]);

    // Create individual with local federation membership
    $individual = Individual::factory()->create();
    IndividualFederation::create([
        'individual_id' => $individual->id,
        'federation_id' => $localFederation->id,
        'status_class' => ActiveIndividualFederationState::class,
        'active' => true,
    ]);

    // Execute removal action
    $action = new SyncIndividualLocalFederationsAction;
    $removedFederations = $action->removeOnDeactivation($individual, $entity);

    // Assert federation was removed
    expect($removedFederations)->toHaveCount(1);
    expect(IndividualFederation::where('individual_id', $individual->id)
        ->where('federation_id', $localFederation->id)
        ->exists())->toBeFalse();
});

it('does not remove federation if individual has other active entity in that federation', function () {
    // Create local federation
    $localFederation = Federation::factory()->create(['is_local' => true]);

    // Create two entities with local federation membership
    $entity1 = Entity::factory()->create();
    $entity1->federations()->attach($localFederation->id, [
        'status_class' => ActiveEntityFederationState::class,
    ]);

    $entity2 = Entity::factory()->create();
    $entity2->federations()->attach($localFederation->id, [
        'status_class' => ActiveEntityFederationState::class,
    ]);

    // Create individual with local federation membership and active in both entities
    $individual = Individual::factory()->create();
    IndividualFederation::create([
        'individual_id' => $individual->id,
        'federation_id' => $localFederation->id,
        'status_class' => ActiveIndividualFederationState::class,
        'active' => true,
    ]);

    // Add individual to both entities
    $individual->entities()->attach($entity1->id, [
        'status_class' => ActiveIndividualEntityState::class,
    ]);
    $individual->entities()->attach($entity2->id, [
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    // Execute removal for entity1 only
    $action = new SyncIndividualLocalFederationsAction;
    $removedFederations = $action->removeOnDeactivation($individual, $entity1);

    // Assert federation was NOT removed (individual still in entity2)
    expect($removedFederations)->toHaveCount(0);
    expect(IndividualFederation::where('individual_id', $individual->id)
        ->where('federation_id', $localFederation->id)
        ->exists())->toBeTrue();
});
