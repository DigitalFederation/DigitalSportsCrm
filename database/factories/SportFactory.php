<?php

namespace Database\Factories;

use App\Models\Sport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sport>
 */
class SportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Sport::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $sportsNames = [
            'Finswimming',
            'Freediving',
            'Aquathlon',
            'Underwater Hockey',
            'Underwater Rugby',
            'Target Shooting',
            'Sport Diving',
            'Spearfishing',
            'Orienteering',
            'Visual',
        ];

        // Remove unique() to prevent infinite loops when creating many sports
        // If uniqueness is needed, it should be handled at the test level
        return [
            'name' => $this->faker->randomElement($sportsNames) . ' ' . $this->faker->optional()->numerify('###'),
            'sport_type' => $this->faker->randomElement(['individual', 'team']),
        ];
    }
}
