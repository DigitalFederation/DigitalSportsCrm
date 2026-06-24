<?php

namespace Database\Factories;

use Domain\Licenses\Models\LicenseType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LicenseTypeFactory extends Factory
{
    protected $model = LicenseType::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'is_individual' => $this->faker->boolean,
        ];
    }
}
