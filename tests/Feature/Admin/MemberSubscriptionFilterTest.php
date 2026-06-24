<?php

use App\Models\Group;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\States\ActiveMemberSubscriptionState;
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

    $this->membershipPackage = MembershipPackage::firstOrCreate(
        ['id' => 1],
        ['name' => 'Test Package', 'description' => 'Test Package for Tests']
    );
});

function memberSubscriptionIndexUrl(array $filters = []): string
{
    $query = http_build_query(['filter' => $filters]);

    return route('admin.member-subscriptions.index') . '?' . $query;
}

function createSubscriptionForIndividual(Individual $individual, MembershipPackage $package): MemberSubscription
{
    return MemberSubscription::create([
        'membership_package_id' => $package->id,
        'member_type' => 'individual',
        'member_id' => $individual->id,
        'start_date' => now()->subMonth(),
        'end_date' => now()->addYear(),
        'status_class' => ActiveMemberSubscriptionState::class,
    ]);
}

function createSubscriptionForEntity(Entity $entity, MembershipPackage $package): MemberSubscription
{
    return MemberSubscription::create([
        'membership_package_id' => $package->id,
        'member_type' => 'entity',
        'member_id' => $entity->id,
        'start_date' => now()->subMonth(),
        'end_date' => now()->addYear(),
        'status_class' => ActiveMemberSubscriptionState::class,
    ]);
}

it('filters individual subscriptions by name', function () {
    $individual = Individual::factory()->create(['name' => 'Carlos', 'surname' => 'Silva']);
    createSubscriptionForIndividual($individual, $this->membershipPackage);

    $response = $this->actingAs($this->admin)
        ->get(memberSubscriptionIndexUrl(['member.name' => 'Carlos']));

    $response->assertSuccessful();
    $response->assertSee('Carlos');
});

it('filters individual subscriptions by surname', function () {
    $individual = Individual::factory()->create(['name' => 'Maria', 'surname' => 'Fernandes']);
    createSubscriptionForIndividual($individual, $this->membershipPackage);

    $response = $this->actingAs($this->admin)
        ->get(memberSubscriptionIndexUrl(['member.name' => 'Fernandes']));

    $response->assertSuccessful();
    $response->assertSee('Fernandes');
});

it('filters entity subscriptions by name', function () {
    $entity = Entity::factory()->create(['name' => 'Clube Nautilus']);
    createSubscriptionForEntity($entity, $this->membershipPackage);

    $response = $this->actingAs($this->admin)
        ->get(memberSubscriptionIndexUrl(['member.name' => 'Nautilus']));

    $response->assertSuccessful();
    $response->assertSee('Clube Nautilus');
});

it('does not crash when filtering with both entity and individual subscriptions', function () {
    $individual = Individual::factory()->create(['name' => 'Ana', 'surname' => 'Costa']);
    $entity = Entity::factory()->create(['name' => 'Diving School Porto']);

    createSubscriptionForIndividual($individual, $this->membershipPackage);
    createSubscriptionForEntity($entity, $this->membershipPackage);

    $response = $this->actingAs($this->admin)
        ->get(memberSubscriptionIndexUrl(['member.name' => 'Ana']));

    $response->assertSuccessful();
    $response->assertSee('Ana');
});

it('does not match names where search term is in the middle of a word', function () {
    $individual = Individual::factory()->create(['name' => 'Liliana', 'surname' => 'Rodrigues']);
    createSubscriptionForIndividual($individual, $this->membershipPackage);

    $response = $this->actingAs($this->admin)
        ->get(memberSubscriptionIndexUrl(['member.name' => 'Ana']));

    $response->assertSuccessful();
    $response->assertDontSee('Liliana');
});

it('matches word boundary in multi-word names', function () {
    $individual = Individual::factory()->create(['name' => 'Maria Ana', 'surname' => 'Santos']);
    createSubscriptionForIndividual($individual, $this->membershipPackage);

    $response = $this->actingAs($this->admin)
        ->get(memberSubscriptionIndexUrl(['member.name' => 'Ana']));

    $response->assertSuccessful();
    $response->assertSee('Maria Ana');
});
