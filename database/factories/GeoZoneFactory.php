<?php

namespace Database\Factories;

use App\Models\GeoZone;
use Illuminate\Database\Eloquent\Factories\Factory;

class GeoZoneFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GeoZone::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word,
        ];
    }
}
