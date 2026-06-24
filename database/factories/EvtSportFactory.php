<?php

namespace Database\Factories;

use Domain\EvtEvents\Models\Sport as EvtSport;
use Illuminate\Database\Eloquent\Factories\Factory;

class EvtSportFactory extends Factory
{
    protected $model = EvtSport::class;

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

        return [
            'name' => $this->faker->unique()->randomElement($sportsNames),
            'sport_type' => $this->faker->randomElement(['individual', 'team']),
        ];
    }
}
