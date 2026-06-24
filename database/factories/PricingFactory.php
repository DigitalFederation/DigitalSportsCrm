<?php

namespace Database\Factories;

use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Pricing;
use Illuminate\Database\Eloquent\Factories\Factory;

class PricingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Pricing::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'event_id' => $this->faker->randomDigitNotNull,
            'discipline_id' => Discipline::factory(), // This ensures a valid discipline_id
            'price_type' => $this->faker->randomElement(['flat_fee', 'individual', 'team']),
            'target_group' => $this->faker->randomElement(['federation', 'entity', 'individual']),
            'start_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'end_date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'is_active' => $this->faker->boolean(80), // 80% chance of being true
        ];
    }
}
