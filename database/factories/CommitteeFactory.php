<?php

namespace Database\Factories;

use App\Models\Committee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Committee>
 */
class CommitteeFactory extends Factory
{
    protected $model = Committee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->bothify('???####'),
            'name' => $this->faker->unique()->word,
            'is_international' => false,
        ];
    }

    /**
     * Create an international committee (like DIVING, SCIENTIFIC).
     */
    public function international(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_international' => true,
        ]);
    }
}
