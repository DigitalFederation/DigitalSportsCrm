<?php

namespace Database\Factories;

use App\Models\User;
use Domain\Geographic\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

class ZoneFactory extends Factory
{
    protected $model = Zone::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true) . ' Zone',
            'code' => strtoupper($this->faker->unique()->bothify('Z??##')),
            'description' => $this->faker->sentence(),
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withoutCode(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => null,
        ]);
    }

    public function withoutCreator(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => null,
        ]);
    }
}
