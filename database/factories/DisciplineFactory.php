<?php

namespace Database\Factories;

use Domain\EvtEvents\Models\Discipline;
use Illuminate\Database\Eloquent\Factories\Factory;

class DisciplineFactory extends Factory
{
    protected $model = Discipline::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'sport_id' => SportFactory::new()->create(),
            'gender' => $this->faker->randomElement(['male', 'female', 'mixed']),
            'enrollment_type' => $this->faker->randomElement(['Individual', 'Team', 'Relay']),
            'enrollment_type_value' => $this->faker->numberBetween(1, 4),
            'athlete_limit' => 10,
            'team_composition_requirements' => [
                'male' => 2,
                'female' => 2,
            ],
        ];
    }
}
