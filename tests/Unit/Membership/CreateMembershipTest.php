<?php

use Domain\Memberships\Actions\CreateMembershipAction;
use Domain\Memberships\DataTransferObject\MembershipData;
use Domain\Memberships\Models\Membership;
use Domain\Memberships\Models\MembershipPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a membership', function () {
    $plan = MembershipPlan::factory()->create();
    $membership = Membership::factory()->make();

    $membership->plans = [$plan->id];

    $value_array = MembershipData::fromArray($membership->toArray());
    $action = app(CreateMembershipAction::class);
    $value = $action($value_array);

    $this->assertDatabaseHas($value->getTable(), [
        'id' => $value->id,
    ]);

    $this->assertDatabaseHas('membership_membership_plan', [
        'membership_id' => $value->id,
        'membership_plan_id' => $plan->id,
    ]);
});

it('can create a membership with multiple plans', function () {
    $plans = MembershipPlan::factory(10)->create();
    $membership = Membership::factory()->make();

    $membership->plans = $plans->pluck('id')->toArray();

    $value_array = MembershipData::fromArray($membership->toArray());
    $action = app(CreateMembershipAction::class);
    $value = $action($value_array);

    $this->assertDatabaseHas($value->getTable(), [
        'id' => $value->id,
    ]);

    expect($value->plans()->count())->toBe(count($membership->plans));
});
