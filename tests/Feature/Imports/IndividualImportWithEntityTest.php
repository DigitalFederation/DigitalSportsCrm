<?php

use App\Jobs\GenerateModelQrCode;
use App\Models\Country;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Federations\Models\Federation;
use Domain\Imports\Actions\BulkInsertIndividualsAction;
use Domain\Imports\Actions\ValidateBulkDataAction;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\IndividualFederation;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    \App\Models\Group::firstOrCreate(['code' => 'INDIVIDUAL'], ['name' => 'Individual']);

    Queue::fake([GenerateModelQrCode::class]);

    $this->country = Country::factory()->create();

    // Create a default federation for the CreateIndividualAction
    $this->defaultFederation = Federation::factory()->create([
        'is_default_federation' => true,
        'name' => 'Default Federation',
    ]);

    // Create a local federation
    $this->localFederation = Federation::factory()->create([
        'is_local' => true,
        'name' => 'Local Federation Norte',
    ]);

    // Create entity with numeric member_number
    $this->entity = Entity::factory()->create([
        'member_number' => 100001,
        'name' => 'Test Club',
    ]);

    // Associate entity with local federation
    $this->entity->entityFederations()->create([
        'federation_id' => $this->localFederation->id,
        'status_class' => ActiveEntityFederationState::class,
    ]);

    $this->validateAction = new ValidateBulkDataAction;
    $this->bulkInsertAction = new BulkInsertIndividualsAction;
});

test('valid entity_member_number resolves to entity_id', function () {
    $individuals = [
        [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.entity.test@example.com',
            'birthdate' => '1990-01-01',
            'country_id' => $this->country->id,
            'entity_member_number' => '100001',
        ],
    ];

    $result = $this->validateAction->execute($individuals);

    expect($result['errors'])->toBeEmpty()
        ->and($result['valid'])->toHaveCount(1)
        ->and($result['valid'][0]['entity_id'])->toBe($this->entity->id)
        ->and(isset($result['valid'][0]['entity_member_number']))->toBeFalse();
});

test('invalid entity_member_number produces error', function () {
    $individuals = [
        [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.invalid@example.com',
            'birthdate' => '1990-01-01',
            'country_id' => $this->country->id,
            'entity_member_number' => '999999',
        ],
    ];

    $result = $this->validateAction->execute($individuals);

    expect($result['valid'])->toBeEmpty()
        ->and($result['errors'])->toHaveCount(1)
        ->and($result['errors'][0][0])->toContain('999999');
});

test('empty entity_member_number creates individual without entity', function () {
    $individuals = [
        [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.noentity@example.com',
            'birthdate' => '1990-01-01',
            'country_id' => $this->country->id,
        ],
    ];

    $result = $this->bulkInsertAction->execute($individuals);

    expect($result['individuals'])->toBe(1);

    $individual = Individual::where('email', 'john.noentity@example.com')->first();
    expect($individual)->not->toBeNull()
        ->and($individual->individualEntities)->toHaveCount(0);
});

test('individual-entity relationship created with correct state', function () {
    $individuals = [
        [
            'name' => 'Jane',
            'surname' => 'Smith',
            'email' => 'jane.entity@example.com',
            'birthdate' => '1992-05-15',
            'country_id' => $this->country->id,
            'entity_id' => $this->entity->id,
        ],
    ];

    $result = $this->bulkInsertAction->execute($individuals);

    expect($result['individuals'])->toBe(1);

    $individual = Individual::where('email', 'jane.entity@example.com')->first();
    expect($individual)->not->toBeNull();

    // Use individualEntities() to access the pivot model
    $entityRelation = $individual->individualEntities()->where('entity_id', $this->entity->id)->first();
    expect($entityRelation)->not->toBeNull()
        ->and($entityRelation->status_class)->toBe(ActiveIndividualEntityState::class);
});

test('local federations synced when entity assigned', function () {
    $individuals = [
        [
            'name' => 'Bob',
            'surname' => 'Johnson',
            'email' => 'bob.localfed@example.com',
            'birthdate' => '1985-03-20',
            'country_id' => $this->country->id,
            'entity_id' => $this->entity->id,
        ],
    ];

    $result = $this->bulkInsertAction->execute($individuals);

    expect($result['individuals'])->toBe(1);

    $individual = Individual::where('email', 'bob.localfed@example.com')->first();
    expect($individual)->not->toBeNull();

    // Check that individual is synced to local federation using IndividualFederation model
    $localFederationMembership = IndividualFederation::where('individual_id', $individual->id)
        ->where('federation_id', $this->localFederation->id)
        ->first();

    expect($localFederationMembership)->not->toBeNull()
        ->and($localFederationMembership->status_class)->toBe(ActiveIndividualFederationState::class);
});

test('validation caches entity lookups', function () {
    // Create multiple individuals with the same entity_member_number
    $individuals = [
        [
            'name' => 'User1',
            'surname' => 'Test',
            'email' => 'user1.cache@example.com',
            'birthdate' => '1990-01-01',
            'country_id' => $this->country->id,
            'entity_member_number' => '100001',
        ],
        [
            'name' => 'User2',
            'surname' => 'Test',
            'email' => 'user2.cache@example.com',
            'birthdate' => '1991-01-01',
            'country_id' => $this->country->id,
            'entity_member_number' => '100001',
        ],
        [
            'name' => 'User3',
            'surname' => 'Test',
            'email' => 'user3.cache@example.com',
            'birthdate' => '1992-01-01',
            'country_id' => $this->country->id,
            'entity_member_number' => '100001',
        ],
    ];

    $result = $this->validateAction->execute($individuals);

    expect($result['errors'])->toBeEmpty()
        ->and($result['valid'])->toHaveCount(3);

    // All should have the same entity_id
    foreach ($result['valid'] as $individual) {
        expect($individual['entity_id'])->toBe($this->entity->id);
    }
});

test('different rows can have different entities', function () {
    // Create a second entity with unique member_number
    $entity2 = Entity::factory()->create([
        'member_number' => 100002,
        'name' => 'Second Club',
    ]);

    $individuals = [
        [
            'name' => 'User1',
            'surname' => 'Test',
            'email' => 'user1.multi@example.com',
            'birthdate' => '1990-01-01',
            'country_id' => $this->country->id,
            'entity_member_number' => '100001',
        ],
        [
            'name' => 'User2',
            'surname' => 'Test',
            'email' => 'user2.multi@example.com',
            'birthdate' => '1991-01-01',
            'country_id' => $this->country->id,
            'entity_member_number' => '100002',
        ],
        [
            'name' => 'User3',
            'surname' => 'Test',
            'email' => 'user3.multi@example.com',
            'birthdate' => '1992-01-01',
            'country_id' => $this->country->id,
            // No entity_member_number - should not have entity
        ],
    ];

    $result = $this->validateAction->execute($individuals);

    expect($result['errors'])->toBeEmpty()
        ->and($result['valid'])->toHaveCount(3)
        ->and($result['valid'][0]['entity_id'])->toBe($this->entity->id)
        ->and($result['valid'][1]['entity_id'])->toBe($entity2->id)
        ->and(isset($result['valid'][2]['entity_id']))->toBeFalse();
});

test('per-row entity_id takes precedence over options entity_id', function () {
    // Create another entity for options with unique member_number
    $optionsEntity = Entity::factory()->create([
        'member_number' => 100003,
        'name' => 'Options Entity',
    ]);

    $individuals = [
        [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.precedence@example.com',
            'birthdate' => '1990-01-01',
            'country_id' => $this->country->id,
            'entity_id' => $this->entity->id, // Per-row entity_id
        ],
    ];

    // Pass a different entity_id in options
    $result = $this->bulkInsertAction->execute($individuals, [
        'entity_id' => $optionsEntity->id,
    ]);

    expect($result['individuals'])->toBe(1);

    $individual = Individual::where('email', 'john.precedence@example.com')->first();
    expect($individual)->not->toBeNull();

    // Check that per-row entity_id wins
    $entityRelation = $individual->individualEntities()->where('entity_id', $this->entity->id)->first();
    expect($entityRelation)->not->toBeNull();

    // Ensure only one entity relationship exists
    expect($individual->individualEntities)->toHaveCount(1);
});
