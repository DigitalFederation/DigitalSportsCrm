<?php

namespace Database\Factories;

use Domain\Federations\Models\Federation;
use Domain\Memberships\Models\Membership;
use Domain\Memberships\States\ActiveMembershipState;
use Domain\Memberships\States\CanceledMembershipState;
use Domain\Memberships\States\PendingMembershipState;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Membership>
 */
class MembershipFactory extends Factory
{
    protected $model = Membership::class;

    public function definition()
    {
        $status_classes = [
            ActiveMembershipState::class,
            PendingMembershipState::class,
            CanceledMembershipState::class,
        ];

        return [
            'parent_id' => $this->faker->optional()->randomElement(Membership::pluck('id')->toArray()), // optional parent ID
            'federation_id' => Federation::factory(), // assuming you have a factory for Federation
            'name' => $this->faker->words(3, true),
            'status_class' => $this->faker->randomElement($status_classes),
            'activated_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'current_term_starts_at' => $start = $this->faker->date('Y-m-d', '-1 year'),
            'current_term_ends_at' => Carbon::parse($start)->addYears(1)->format('Y-m-d'),
            'last_billing_at' => $this->faker->dateTimeBetween('-1 years', 'now'),
            'cancelled_at' => $this->faker->optional()->dateTimeBetween('-2 years', 'now'), // optional cancelled date
            'created_at' => $this->faker->dateTimeBetween('-3 years', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
        ];
    }
}
