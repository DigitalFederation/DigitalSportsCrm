<?php

namespace Database\Factories\Domain\Memberships\Models;

use Domain\Federations\Models\Federation;
use Domain\Memberships\Models\AffiliationPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Memberships\Models\AffiliationPlan>
 */
class AffiliationPlanFactory extends Factory
{
    protected $model = AffiliationPlan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'federation_id' => Federation::factory(),
            'name' => $this->faker->words(3, true) . ' Affiliation Plan',
            'description' => $this->faker->sentence(),
            'duration_months' => $this->faker->randomElement([6, 12, 24]),
            'individual_fee' => $this->faker->randomFloat(2, 10, 200),
            'entity_fee' => $this->faker->randomFloat(2, 50, 500),
            'type' => $this->faker->randomElement(['standard', 'premium', 'basic']),
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'vat_rate' => $this->faker->randomElement([0, 6, 13, 23]),
            'is_validation_plan' => false,
        ];
    }

    /**
     * Indicate that the plan is a validation plan.
     */
    public function validation(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_validation_plan' => true,
        ]);
    }

    /**
     * Indicate that the plan is free.
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'individual_fee' => 0,
            'entity_fee' => 0,
        ]);
    }
}
