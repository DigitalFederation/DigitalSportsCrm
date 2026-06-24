<?php

namespace Database\Factories;

use App\Models\Committee;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfessionalRoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProfessionalRole::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'code' => $this->faker->unique()->word,
            'role' => $this->faker->randomElement(['DIVER', 'INSTRUCTOR', 'LEADER', 'COACH', 'TECHNICAL_OFFICIAL', 'ATHLETE']),
            'committee_id' => Committee::factory(),
        ];
    }
}
