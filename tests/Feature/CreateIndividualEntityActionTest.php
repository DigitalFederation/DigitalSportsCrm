<?php

use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Actions\CreateIndividualEntityAction;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\IndividualEntity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

it('adds an individual to an entity', function () {

    $group = Group::factory()->create(['code' => 'INDIVIDUAL']);

    // Create a federation
    $federation = Federation::factory()->create();

    // Create an entity and associate it with the federation
    $entity = Entity::factory()->create();
    $entity->federations()->attach($federation->id, [
        'active' => true,
        'status_class' => \Domain\Entities\States\ActiveEntityFederationState::class,
    ]);

    // Create an individual and associate it with the federation
    $individual = Individual::factory()->create(['member_code' => '123456']);
    $federation->individuals()->attach($individual->id, [
        'active' => true,
        'status_class' => \Domain\Individuals\States\ActiveIndividualFederationState::class,
    ]);

    // Act as a user who belongs to the entity
    $user = User::factory()->create(['group_id' => $group->id]);
    $user->entities()->attach($entity->id);
    Auth::login($user);

    // Execute the action
    $action = new CreateIndividualEntityAction;
    $individualEntity = $action->execute('123456', $entity->id);

    // Assertions
    expect($individualEntity)->toBeInstanceOf(IndividualEntity::class)
        ->and($individualEntity->entity_id)->toEqual($entity->id)
        ->and($individualEntity->individual_id)->toEqual($individual->id);
});
