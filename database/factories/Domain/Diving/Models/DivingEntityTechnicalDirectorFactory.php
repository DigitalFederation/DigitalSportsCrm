<?php

namespace Database\Factories\Domain\Diving\Models;

use Domain\Diving\Models\DivingEntityTechnicalDirector;
use Domain\Diving\States\AssignedDivingTechnicalDirectorState;
use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template TModel of \Domain\Diving\Models\DivingEntityTechnicalDirector
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<TModel>
 */
class DivingEntityTechnicalDirectorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected $model = DivingEntityTechnicalDirector::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entity_id' => Entity::factory(),
            'individual_id' => Individual::factory(),
            'license_attributed_id' => LicenseAttributed::factory(),
            'license_id' => License::factory(),
            'certification_systems' => $this->faker->randomElements(['CMAS', 'PADI', 'SSI', 'SDI_TDI'], $this->faker->numberBetween(1, 3)),
            'status_class' => AssignedDivingTechnicalDirectorState::class,
            'message' => $this->faker->optional()->sentence(),
            'assigned_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Create an assigned technical director.
     */
    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_class' => AssignedDivingTechnicalDirectorState::class,
            'assigned_at' => now(),
        ]);
    }

    /**
     * Create a removed technical director.
     */
    public function removed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_class' => \Domain\Diving\States\RemovedDivingTechnicalDirectorState::class,
            'assigned_at' => $this->faker->dateTimeBetween('-1 year', '-1 month'),
        ]);
    }
}
