<?php

namespace Database\Factories\Domain\Geographic\Models;

use Domain\Geographic\Models\District;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Geographic\Models\District>
 */
class DistrictFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = District::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->city(),
            'code' => strtoupper($this->faker->unique()->lexify('???')),
            'country_id' => \App\Models\Country::factory(),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
