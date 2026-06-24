<?php

use App\Livewire\IndividualRequestEntity;
use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Federations\Models\Federation;
use Domain\Geographic\Models\District;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Models\Affiliation;
use Domain\Memberships\Models\AffiliationPlan;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\States\ActiveAffiliationState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createEntityWithActiveValidationAffiliation(Entity $entity, Federation $federation): Affiliation
{
    $package = MembershipPackage::factory()->entity()->create();
    $plan = AffiliationPlan::factory()->validation()->create(['federation_id' => $federation->id]);
    $package->affiliationPlans()->attach($plan);

    $subscription = MemberSubscription::factory()->forEntity($entity)->create([
        'membership_package_id' => $package->id,
    ]);

    return Affiliation::factory()->forEntity($entity)->create([
        'federation_id' => $federation->id,
        'member_subscription_id' => $subscription->id,
        'status_class' => ActiveAffiliationState::class,
        'start_date' => now()->subMonth(),
        'end_date' => now()->addYear(),
    ]);
}

beforeEach(function () {
    $this->individualGroup = Group::firstOrCreate(['code' => 'INDIVIDUAL'], ['name' => 'Individual']);

    $this->federation = Federation::factory()->create(['is_default_federation' => true]);
    $this->district = District::factory()->create(['is_active' => true]);
    $this->otherDistrict = District::factory()->create(['is_active' => true]);

    // Entity with active validation plan affiliation in the main district
    $this->activeEntity = Entity::factory()->create(['district_id' => $this->district->id]);
    $this->activeEntity->federations()->attach($this->federation->id, [
        'status_class' => ActiveEntityFederationState::class,
        'active' => true,
    ]);
    createEntityWithActiveValidationAffiliation($this->activeEntity, $this->federation);

    // Entity without active validation plan affiliation
    $this->inactiveEntity = Entity::factory()->create(['district_id' => $this->district->id]);
    $this->inactiveEntity->federations()->attach($this->federation->id, [
        'status_class' => ActiveEntityFederationState::class,
        'active' => true,
    ]);

    // Entity in a different district with active affiliation
    $this->otherDistrictEntity = Entity::factory()->create(['district_id' => $this->otherDistrict->id]);
    $this->otherDistrictEntity->federations()->attach($this->federation->id, [
        'status_class' => ActiveEntityFederationState::class,
        'active' => true,
    ]);
    createEntityWithActiveValidationAffiliation($this->otherDistrictEntity, $this->federation);

    // Create individual and user
    $this->individual = Individual::factory()->create();
    $this->user = User::factory()
        ->for($this->individualGroup, 'group')
        ->create();
    $this->individual->update(['user_id' => $this->user->id]);
});

it('renders the component with district dropdown', function () {
    Livewire::actingAs($this->user)
        ->test(IndividualRequestEntity::class)
        ->assertSee(__('entities.select_district'))
        ->assertSee(__('entities.only_active_affiliation_entities'))
        ->assertSuccessful();
});

it('shows only districts that have entities with active validation affiliation', function () {
    $emptyDistrict = District::factory()->create(['is_active' => true]);

    $component = Livewire::actingAs($this->user)
        ->test(IndividualRequestEntity::class);

    $districts = $component->instance()->districts;

    expect($districts->pluck('id'))
        ->toContain($this->district->id)
        ->toContain($this->otherDistrict->id)
        ->not->toContain($emptyDistrict->id);
});

it('shows no entities when no district is selected', function () {
    $component = Livewire::actingAs($this->user)
        ->test(IndividualRequestEntity::class);

    $entities = $component->instance()->entities;

    expect($entities)->toBeEmpty();
});

it('filters entities by selected district', function () {
    $component = Livewire::actingAs($this->user)
        ->test(IndividualRequestEntity::class)
        ->set('districtId', $this->district->id);

    $entities = $component->instance()->entities;

    expect($entities->pluck('id'))
        ->toContain($this->activeEntity->id)
        ->not->toContain($this->otherDistrictEntity->id);
});

it('only shows entities with active validation plan affiliation', function () {
    $component = Livewire::actingAs($this->user)
        ->test(IndividualRequestEntity::class)
        ->set('districtId', $this->district->id);

    $entities = $component->instance()->entities;

    expect($entities->pluck('id'))
        ->toContain($this->activeEntity->id)
        ->not->toContain($this->inactiveEntity->id);
});

it('resets entity selection when district changes', function () {
    Livewire::actingAs($this->user)
        ->test(IndividualRequestEntity::class)
        ->set('districtId', $this->district->id)
        ->set('entitySelected', $this->activeEntity->id)
        ->assertSet('entitySelected', $this->activeEntity->id)
        ->set('districtId', $this->otherDistrict->id)
        ->assertSet('entitySelected', null);
});

it('displays the active affiliation info message', function () {
    Livewire::actingAs($this->user)
        ->test(IndividualRequestEntity::class)
        ->assertSee(__('entities.only_active_affiliation_entities'));
});
