<?php

use App\Models\Committee;
use App\Models\Group;
use Domain\Federations\Models\Federation;
use Domain\Memberships\Models\Membership;
use Domain\Memberships\Models\MembershipPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);
beforeEach(function () {

    artisan('db:seed --class=UserGroupSeeder');
    artisan('db:seed --class=RoleAndPermissionSeeder');

    // Create roles that are needed for this test
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'federation-admin']);
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'association-territorial-admin']);
    // Create the committee-specific role that will be assigned when membership plan is attached
    \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'local-federation-scientific-admin']);

    $this->group = Group::factory()->create(['code' => 'FEDERATION']);
    $this->committee = Committee::factory()->create(['code' => 'SCIENTIFIC', 'name' => 'Technical Committee']);

    // Federations
    $this->federation = Federation::factory()->create(['is_local' => false]);
    $this->local = Federation::factory()->create(['parent_id' => $this->federation->id, 'is_local' => true]);

    // Create active membership for the parent federation
    Membership::factory()->create([
        'federation_id' => $this->federation->id,
        'status_class' => Domain\Memberships\States\ActiveMembershipState::class,
    ]);

    // Users
    $this->user = \App\Models\User::factory()->create(['group_id' => $this->group->id]);
    $this->user->assignRole('federation-admin');
    $this->user->federations()->attach($this->federation->id);

    $this->userLocal = \App\Models\User::factory()->create(['group_id' => $this->group->id]);
    $this->userLocal->assignRole('association-territorial-admin');
    $this->userLocal->federations()->attach($this->local->id);

    // Membership
    $this->membership = Membership::factory()->create(['federation_id' => $this->federation->id]);

    $this->membershipPlan = MembershipPlan::factory()->create(['committee_id' => $this->committee->id]);
    $this->membership->plans()->attach($this->membershipPlan->id);
});

it('can attach a membership to a local federation and assign roles correctly', function () {

    $this->actingAs($this->user);

    $data = [
        'local_federation_id' => $this->local->id,
        'membership_plan_id' => [$this->membershipPlan->id],
    ];
    $response = $this->post(route('federation.local-membership-plan.store'), $data);

    $response->assertStatus(302);

    assertDatabaseHas('local_membership_plan_associations', [
        'local_federation_id' => $this->local->id,
        'membership_plan_id' => $this->membershipPlan->id,
    ]);

    // Reload the local federation to fetch updated relations
    $this->local->refresh();

    // Assert that the first user of the local federation has the expected role
    $expectedRole = 'local-federation-' . strtolower($this->committee->code) . '-admin';
    $firstUser = $this->local->users->first();
    expect($firstUser)->not->toBeNull();
    expect($firstUser->hasRole($expectedRole))->toBeTrue();
});
