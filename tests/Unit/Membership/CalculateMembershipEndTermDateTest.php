<?php

use Carbon\Carbon;
use Domain\Memberships\Actions\CalculateMembershipEndTermDateAction;

it('can calculate the end term date of membership', function ($interval_unit) {
    $interval = fake()->numerify;

    $action = app(CalculateMembershipEndTermDateAction::class);
    $term = $action(Carbon::now(), null, $interval, $interval_unit);

    $expect_term = date('Y-m-d', strtotime("+{$interval} {$interval_unit}", strtotime(Carbon::now())));

    $this->assertEquals($term, $expect_term);

})->with(['weeks', 'months', 'years']);
