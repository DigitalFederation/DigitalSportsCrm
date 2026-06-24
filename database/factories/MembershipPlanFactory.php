<?php

namespace Database\Factories;

use App\Models\Committee;
use Domain\Memberships\Models\MembershipPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MembershipPlan>
 */
class MembershipPlanFactory extends Factory
{
    protected $model = MembershipPlan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'committee_id' => $this->faker->randomElement(Committee::all()->pluck('id')->toArray()),
            'name' => $this->faker->name,
            'price' => $this->faker->randomFloat(3),
            'interval' => $this->faker->randomDigitNotZero(),
            'interval_unit' => $this->faker->randomElement(array_keys(config('enum.interval_unit'))),
        ];
    }
}
