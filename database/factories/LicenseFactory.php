<?php

namespace Database\Factories;

use App\Models\Sport;
use Domain\Licenses\Models\License;
use Illuminate\Database\Eloquent\Factories\Factory;

class LicenseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = License::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        return [
            'committee_id' => \App\Models\Committee::factory(),
            'type_id' => LicenseTypeFactory::new()->create(),
            'professional_role_id' => \Domain\Individuals\Models\ProfessionalRole::factory(),
            'name' => $this->faker->word,
            'license_code' => strtoupper($this->faker->unique()->bothify('LIC-####-????')),
            'active' => $this->faker->boolean,
            'interval' => $this->faker->numberBetween(1, 5),
            'interval_unit' => $this->faker->randomElement(['weeks', 'months', 'years']),
            'sport_id' => Sport::factory()->create()->id,
            'unit_value' => $this->faker->randomFloat(2, 0, 1000),
            'unit_value_entity' => null,
            'unit_value_individual' => null,
            'requester_model' => null,
            'tax_value' => null,
            'tax_percentage' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (License $license) {
            if ($license->sport_id) {
                $license->sports()->syncWithoutDetaching([$license->sport_id]);
            }
        });
    }
}
