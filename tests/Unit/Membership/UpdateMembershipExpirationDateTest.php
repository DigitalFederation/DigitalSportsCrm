<?php

use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Actions\UpdateMembershipExpirationDateAction;
use Domain\Memberships\Models\Membership;

use function Pest\Laravel\artisan;

beforeEach(function () {
    artisan('db:seed --class=UserGroupSeeder');
    artisan('db:seed --class=CommitteeSeeder');
    artisan('db:seed --class=MembershipPlanSeeder');
    $user = User::factory()
        ->has(Federation::factory())
        ->has(Entity::factory())
        ->has(Individual::factory())
        ->create(['group_id' => 3]);
    $this->actingAs($user);
});

it('can change the expiration date of a membership', function () {
    $action = app(UpdateMembershipExpirationDateAction::class);

    $membership = Membership::factory()->create();
    $newDate = fake()->date;
    $current_term_ends_at = $membership->current_term_ends_at;
    $membership = $action($membership, $newDate);

    $this->assertNotEquals($current_term_ends_at, $membership->current_term_ends_at);
    $this->assertDatabaseHas($membership->getTable(), [
        'id' => $membership->id,
        'current_term_ends_at' => $newDate,
    ]);
});
