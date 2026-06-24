<?php

namespace Database\Factories;

use App\Enums\EvtDisciplineEnrollmentTypeEnum;
use App\Enums\EvtDisciplineGenderEnum;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Sport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Discipline>
 */
class EvtDisciplineFactory extends Factory
{
    protected $model = Discipline::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'sport_id' => Sport::factory()->create(),
            'gender' => $this->faker->randomElement(EvtDisciplineGenderEnum::cases()),
            'enrollment_type' => $this->faker->randomElement(EvtDisciplineEnrollmentTypeEnum::cases()),
            'enrollment_type_value' => $this->faker->word(),
        ];
    }
}
