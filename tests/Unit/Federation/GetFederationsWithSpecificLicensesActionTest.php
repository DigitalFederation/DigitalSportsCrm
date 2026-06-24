<?php

use Domain\Federations\Actions\GetFederationsFromLicensesAction;
use Domain\Federations\Models\Federation;
use Domain\Licenses\Models\License;
use Domain\Memberships\Models\Membership;
use Domain\Memberships\Models\MembershipPlan;
use Domain\Memberships\States\ActiveMembershipState;

it('returns federations with specific licenses', function () {

    // Create a federation
    $federation = Federation::factory()->create();

    // Create a license
    $license = License::factory()->create();

    // Create an active membership associated with the federation
    $membership = Membership::factory()->state(['federation_id' => $federation->id, 'status_class' => ActiveMembershipState::class])->create();

    // Create a membership plan associated with the membership and license
    $plan = MembershipPlan::factory()->create();
    $plan->memberships()->attach($membership);
    $plan->licenses()->attach($license);

    // Fetch federations with the specific license using the action
    $action = new GetFederationsFromLicensesAction;
    $federations = $action([$license->id]);

    // Assertions
    expect($federations)->toHaveCount(1);
    expect($federations->first()->id)->toBe($federation->id);
});
