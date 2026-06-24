<?php

use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Insurance\Models\Insurance;
use Domain\Insurance\Models\InsurancePlan;
use Domain\Insurance\States\ActiveInsuranceState;
use Domain\Memberships\Models\Affiliation;
use Domain\Memberships\Models\Membership;
use Domain\Memberships\States\ActiveAffiliationState;
use Domain\Memberships\States\ActiveMembershipState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed --class=RoleAndPermissionSeeder');

    Group::query()->delete();
    Group::insert([
        ['id' => 1, 'name' => 'Individual', 'code' => 'INDIVIDUAL'],
        ['id' => 2, 'name' => 'Entity', 'code' => 'ENTITY'],
        ['id' => 3, 'name' => 'Federation', 'code' => 'FEDERATION'],
        ['id' => 5, 'name' => 'Admin', 'code' => 'ADMIN'],
    ]);

    $this->user = User::factory()->create([
        'group_id' => Group::where('code', 'FEDERATION')->first()->id,
    ]);

    $this->federation = Federation::factory()->create(['is_local' => true]);
    Membership::factory()->create([
        'federation_id' => $this->federation->id,
        'status_class' => ActiveMembershipState::class,
    ]);

    $this->user->federations()->attach($this->federation);

    $this->insurancePlan = InsurancePlan::create([
        'name' => 'Test Insurance Plan',
        'description' => 'Test insurance plan',
        'individual_fee' => 25.00,
        'entity_fee' => 50.00,
        'target_audience' => 'general',
        'type' => 'personal_accident',
    ]);
});

test('entity insurances index eager loads member relationship', function () {
    DB::enableQueryLog();

    $entities = Entity::factory()->count(3)->create();

    foreach ($entities as $entity) {
        $entity->federations()->attach($this->federation, [
            'status_class' => ActiveEntityFederationState::class,
        ]);

        Affiliation::factory()->create([
            'member_type' => 'entity',
            'member_id' => $entity->id,
            'federation_id' => $this->federation->id,
            'status_class' => ActiveAffiliationState::class,
        ]);

        Insurance::create([
            'member_type' => 'entity',
            'member_id' => $entity->id,
            'insurance_plan_id' => $this->insurancePlan->id,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'individual_fee' => 0,
            'entity_fee' => 50.00,
            'is_external' => false,
            'status_class' => ActiveInsuranceState::class,
        ]);
    }

    DB::flushQueryLog();

    $response = $this->actingAs($this->user)
        ->get(route('federation.entity-insurances.index'));

    $response->assertStatus(200);

    $queries = collect(DB::getQueryLog());
    $lazyLoadQueries = $queries->filter(fn ($query) => str_contains($query['query'], 'select * from `entity` where `entity`.`id` = ? limit 1'));

    expect($lazyLoadQueries->count())->toBe(0);
});

test('individual insurances index eager loads member and entities relationships', function () {
    DB::enableQueryLog();

    $entity = Entity::factory()->create();
    $entity->federations()->attach($this->federation, [
        'status_class' => ActiveEntityFederationState::class,
    ]);

    $individuals = Individual::factory()->count(3)->create();

    foreach ($individuals as $individual) {
        $individual->entities()->attach($entity);

        Affiliation::factory()->create([
            'member_type' => 'individual',
            'member_id' => $individual->id,
            'federation_id' => $this->federation->id,
            'status_class' => ActiveAffiliationState::class,
        ]);

        Insurance::create([
            'member_type' => 'individual',
            'member_id' => $individual->id,
            'insurance_plan_id' => $this->insurancePlan->id,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'individual_fee' => 25.00,
            'entity_fee' => 0,
            'is_external' => false,
            'status_class' => ActiveInsuranceState::class,
        ]);
    }

    DB::flushQueryLog();

    $response = $this->actingAs($this->user)
        ->get(route('federation.individual-insurances.index'));

    $response->assertStatus(200);

    $queries = collect(DB::getQueryLog());

    $individualLazyLoadQueries = $queries->filter(fn ($query) => str_contains($query['query'], 'select * from `individual` where `individual`.`id` = ? limit 1'));
    $entityLazyLoadQueries = $queries->filter(fn ($query) => str_contains($query['query'], 'select * from `entity` inner join `entity_individual`'));

    expect($individualLazyLoadQueries->count())->toBe(0)
        ->and($entityLazyLoadQueries->count())->toBe(0);
});
