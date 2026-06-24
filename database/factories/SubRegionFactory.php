<?php

namespace Database\Factories;

use App\Models\GeoZone;
use App\Models\SubRegion;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubRegionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SubRegion::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word,
            'geo_zone_id' => GeoZone::factory(),
        ];
    }
}
