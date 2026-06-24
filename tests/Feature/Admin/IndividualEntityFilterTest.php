<?php

use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\IndividualEntity;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\PendingIndividualEntityState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');
    $this->artisan('db:seed --class=UserGroupSeeder');

    $adminGroup = Group::where('code', 'ADMIN')->first();
    $this->admin = User::factory()->create([
        'group_id' => $adminGroup->id,
        'active' => true,
    ]);
    $this->admin->assignRole('admin');

    $this->actingAs($this->admin);
});

test('entity filter returns only individuals with active association to that entity', function () {
    $entity = Entity::factory()->create();

    $activeIndividual = Individual::factory()->create(['name' => 'ActiveMember']);
    IndividualEntity::create([
        'individual_id' => $activeIndividual->id,
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    $pendingIndividual = Individual::factory()->create(['name' => 'PendingMember']);
    IndividualEntity::create([
        'individual_id' => $pendingIndividual->id,
        'entity_id' => $entity->id,
        'status_class' => PendingIndividualEntityState::class,
    ]);

    $unrelatedIndividual = Individual::factory()->create(['name' => 'UnrelatedMember']);

    $response = $this->get(route('admin.individual.index', [
        'filter' => ['filter_entity' => $entity->id],
    ]));

    $response->assertOk();
    $response->assertSee('ActiveMember');
    $response->assertDontSee('PendingMember');
    $response->assertDontSee('UnrelatedMember');
});

test('entity filter with no selection shows all individuals', function () {
    $entity = Entity::factory()->create();

    $individual1 = Individual::factory()->create(['name' => 'MemberAlpha']);
    IndividualEntity::create([
        'individual_id' => $individual1->id,
        'entity_id' => $entity->id,
        'status_class' => ActiveIndividualEntityState::class,
    ]);

    $individual2 = Individual::factory()->create(['name' => 'MemberBeta']);

    $response = $this->get(route('admin.individual.index'));

    $response->assertOk();
    $response->assertSee('MemberAlpha');
    $response->assertSee('MemberBeta');
});
