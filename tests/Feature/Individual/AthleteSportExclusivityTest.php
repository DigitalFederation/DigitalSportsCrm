<?php

use App\Models\Group;
use App\Models\Sport;
use App\Models\User;
use Domain\Entities\DataTransferObject\EntityAthleteData;
use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityAthlete;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\CanceledEntityProfessionalRoleState;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Entities\States\RejectedEntityProfessionalRoleState;
use Domain\Individuals\Actions\AssociateAthleteToEntityAction;
use Domain\Individuals\Actions\DetectIfIndividualCanBeAthleteAction;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->individualGroup = Group::firstOrCreate(['code' => 'INDIVIDUAL'], ['name' => 'Individual']);
    $this->entityGroup = Group::firstOrCreate(['code' => 'ENTITY'], ['name' => 'Entity']);

    $this->athleteRole = ProfessionalRole::firstOrCreate(
        ['role' => 'ATHLETE'],
        ['name' => 'Athlete', 'code' => 'ATHLETE']
    );

    $this->sport1 = Sport::factory()->create(['name' => 'Sport 1']);
    $this->sport2 = Sport::factory()->create(['name' => 'Sport 2']);

    $this->entity1 = Entity::factory()->create(['legal_name' => 'Entity 1']);
    $this->entity2 = Entity::factory()->create(['legal_name' => 'Entity 2']);

    $this->user = User::factory()
        ->for($this->individualGroup, 'group')
        ->has(Individual::factory(), 'individual')
        ->create();

    $this->individual = $this->user->individual;

    // Create athlete license for both sports
    $this->license1 = License::factory()->create([
        'sport_id' => $this->sport1->id,
        'professional_role_id' => $this->athleteRole->id,
    ]);

    $this->license2 = License::factory()->create([
        'sport_id' => $this->sport2->id,
        'professional_role_id' => $this->athleteRole->id,
    ]);

    // Attribute licenses to individual
    LicenseAttributed::factory()->create([
        'license_id' => $this->license1->id,
        'model_type' => 'individual',
        'model_id' => $this->individual->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);

    LicenseAttributed::factory()->create([
        'license_id' => $this->license2->id,
        'model_type' => 'individual',
        'model_id' => $this->individual->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);
});

test('athlete can join multiple entities with different sports', function () {
    // Associate athlete with entity1 for sport1
    EntityAthlete::create([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'sport_id' => $this->sport1->id,
        'entity_name' => $this->entity1->legal_name,
        'individual_name' => $this->individual->full_name,
        'sport_name' => $this->sport1->name,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);

    // Check if athlete can be invited for sport2 at entity2
    $action = new DetectIfIndividualCanBeAthleteAction;
    $canBeAthlete = $action($this->individual->id, $this->sport2->id);

    expect($canBeAthlete)->toBeTrue();

    // Associate athlete with entity2 for sport2
    EntityAthlete::create([
        'entity_id' => $this->entity2->id,
        'individual_id' => $this->individual->id,
        'sport_id' => $this->sport2->id,
        'entity_name' => $this->entity2->legal_name,
        'individual_name' => $this->individual->full_name,
        'sport_name' => $this->sport2->name,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);

    // Verify both associations exist
    expect(EntityAthlete::where('individual_id', $this->individual->id)->count())->toBe(2);
});

test('athlete cannot join multiple entities with same sport when active', function () {
    // Associate athlete with entity1 for sport1 (active)
    EntityAthlete::create([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'sport_id' => $this->sport1->id,
        'entity_name' => $this->entity1->legal_name,
        'individual_name' => $this->individual->full_name,
        'sport_name' => $this->sport1->name,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);

    // Check if athlete can be invited for same sport at entity2
    $action = new DetectIfIndividualCanBeAthleteAction;
    $canBeAthlete = $action($this->individual->id, $this->sport1->id);

    expect($canBeAthlete)->toBeFalse();
});

test('athlete cannot join multiple entities with same sport when pending', function () {
    // Associate athlete with entity1 for sport1 (pending invitation)
    EntityAthlete::create([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'sport_id' => $this->sport1->id,
        'entity_name' => $this->entity1->legal_name,
        'individual_name' => $this->individual->full_name,
        'sport_name' => $this->sport1->name,
        'status_class' => PendingEntityProfessionalRoleState::class,
    ]);

    // Check if athlete can be invited for same sport at entity2
    $action = new DetectIfIndividualCanBeAthleteAction;
    $canBeAthlete = $action($this->individual->id, $this->sport1->id);

    expect($canBeAthlete)->toBeFalse();
});

test('athlete can rejoin same sport after previous association is canceled', function () {
    // Associate athlete with entity1 for sport1 (canceled)
    EntityAthlete::create([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'sport_id' => $this->sport1->id,
        'entity_name' => $this->entity1->legal_name,
        'individual_name' => $this->individual->full_name,
        'sport_name' => $this->sport1->name,
        'status_class' => CanceledEntityProfessionalRoleState::class,
    ]);

    // Check if athlete can be invited for same sport at entity2
    $action = new DetectIfIndividualCanBeAthleteAction;
    $canBeAthlete = $action($this->individual->id, $this->sport1->id);

    expect($canBeAthlete)->toBeTrue();
});

test('athlete can rejoin same sport after previous association is rejected', function () {
    // Associate athlete with entity1 for sport1 (rejected)
    EntityAthlete::create([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'sport_id' => $this->sport1->id,
        'entity_name' => $this->entity1->legal_name,
        'individual_name' => $this->individual->full_name,
        'sport_name' => $this->sport1->name,
        'status_class' => RejectedEntityProfessionalRoleState::class,
    ]);

    // Check if athlete can be invited for same sport at entity2
    $action = new DetectIfIndividualCanBeAthleteAction;
    $canBeAthlete = $action($this->individual->id, $this->sport1->id);

    expect($canBeAthlete)->toBeTrue();
});

test('AssociateAthleteToEntityAction throws exception when athlete already associated with another entity for same sport', function () {
    // First, create an entity user to act as
    $entityUser = User::factory()
        ->for($this->entityGroup, 'group')
        ->create();

    $this->entity1->users()->attach($entityUser->id);

    $this->actingAs($entityUser);

    // Create existing active association for sport1 at entity1
    EntityAthlete::create([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'sport_id' => $this->sport1->id,
        'entity_name' => $this->entity1->legal_name,
        'individual_name' => $this->individual->full_name,
        'sport_name' => $this->sport1->name,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);

    // Try to associate same athlete with entity2 for sport1
    $action = new AssociateAthleteToEntityAction;

    expect(fn () => $action(EntityAthleteData::fromArray([
        'entity_id' => $this->entity2->id,
        'individual_id' => $this->individual->id,
        'sport_id' => $this->sport1->id,
        'entity_name' => $this->entity2->legal_name,
        'individual_name' => $this->individual->full_name,
        'sport_name' => $this->sport1->name,
    ])))->toThrow(Exception::class);
});

test('AssociateAthleteToEntityAction succeeds when athlete at same entity for different sport', function () {
    // First, create an entity user to act as
    $entityUser = User::factory()
        ->for($this->entityGroup, 'group')
        ->create();

    $this->entity1->users()->attach($entityUser->id);

    $this->actingAs($entityUser);

    // Create existing active association for sport1 at entity1
    EntityAthlete::create([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'sport_id' => $this->sport1->id,
        'entity_name' => $this->entity1->legal_name,
        'individual_name' => $this->individual->full_name,
        'sport_name' => $this->sport1->name,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);

    // Associate same athlete with same entity for sport2 (should succeed)
    $action = new AssociateAthleteToEntityAction;

    $result = $action(EntityAthleteData::fromArray([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'sport_id' => $this->sport2->id,
        'entity_name' => $this->entity1->legal_name,
        'individual_name' => $this->individual->full_name,
        'sport_name' => $this->sport2->name,
    ]));

    expect($result)->toBeInstanceOf(EntityAthlete::class);
    expect(EntityAthlete::where('individual_id', $this->individual->id)->count())->toBe(2);
});

test('audit command finds athletes with sport exclusivity violations', function () {
    // Create a violation: same athlete at two entities for same sport
    EntityAthlete::create([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'sport_id' => $this->sport1->id,
        'entity_name' => $this->entity1->legal_name,
        'individual_name' => $this->individual->full_name,
        'sport_name' => $this->sport1->name,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);

    EntityAthlete::create([
        'entity_id' => $this->entity2->id,
        'individual_id' => $this->individual->id,
        'sport_id' => $this->sport1->id,
        'entity_name' => $this->entity2->legal_name,
        'individual_name' => $this->individual->full_name,
        'sport_name' => $this->sport1->name,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);

    $this->artisan('audit:athlete-sport-violations')
        ->expectsOutputToContain('Found 1 athletes with violations')
        ->assertExitCode(1);
});

test('audit command reports no violations when none exist', function () {
    // Create valid associations: different sports
    EntityAthlete::create([
        'entity_id' => $this->entity1->id,
        'individual_id' => $this->individual->id,
        'sport_id' => $this->sport1->id,
        'entity_name' => $this->entity1->legal_name,
        'individual_name' => $this->individual->full_name,
        'sport_name' => $this->sport1->name,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);

    EntityAthlete::create([
        'entity_id' => $this->entity2->id,
        'individual_id' => $this->individual->id,
        'sport_id' => $this->sport2->id,
        'entity_name' => $this->entity2->legal_name,
        'individual_name' => $this->individual->full_name,
        'sport_name' => $this->sport2->name,
        'status_class' => ActiveEntityProfessionalRoleState::class,
    ]);

    $this->artisan('audit:athlete-sport-violations')
        ->expectsOutputToContain('No violations found')
        ->assertExitCode(0);
});
