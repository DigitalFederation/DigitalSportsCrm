<?php

use Domain\Federations\Actions\HasActiveMembershipAction;
use Domain\Federations\Models\Federation;
use Domain\Memberships\Models\Membership;
use Domain\Memberships\States\ActiveMembershipState;
use Domain\Memberships\States\PendingMembershipState;

use function Pest\Laravel\artisan;

beforeEach(function () {
    artisan('db:seed --class=CountrySeeder');
    artisan('db:seed --class=CommitteeSeeder');
    artisan('db:seed --class=MembershipPlanSeeder');
});

it('check if a federation has an active membership', function () {
    $action = app(HasActiveMembershipAction::class);

    $federation = Federation::factory()->create();
    $membership = Membership::factory()->create([
        'federation_id' => $federation->id,
        'status_class' => ActiveMembershipState::class,
    ]);

    $this->assertTrue($action($federation));
});

it('check if a federation doesn\'t have an active membership', function () {
    $action = app(HasActiveMembershipAction::class);

    $federation = Federation::factory()->create();
    $membership = Membership::factory()->create([
        'federation_id' => $federation->id,
        'status_class' => PendingMembershipState::class,
    ]);

    $this->assertFalse($action($federation));
});
