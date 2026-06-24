<?php

namespace Database\Factories\Domain\Insurance\Models;

use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Insurance\Models\Insurance;
use Domain\Insurance\Models\InsurancePlan;
use Domain\Insurance\States\ActiveInsuranceState;
use Illuminate\Database\Eloquent\Factories\Factory;

class InsuranceFactory extends Factory
{
    protected $model = Insurance::class;

    public function definition(): array
    {
        return [
            'insurance_plan_id' => InsurancePlan::factory(),
            'member_type' => Individual::class,
            'member_id' => Individual::factory(),
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'individual_fee' => $this->faker->randomFloat(2, 10, 100),
            'entity_fee' => $this->faker->randomFloat(2, 20, 200),
            'policy_number' => $this->faker->unique()->numerify('POL-########'),
            'is_external' => false,
            'status_class' => ActiveInsuranceState::class,
            'requester_type' => Individual::class,
            'requester_id' => fn () => Individual::factory(),
            'request_type' => 'direct',
        ];
    }

    public function forEntity(): static
    {
        return $this->state(fn () => [
            'member_type' => Entity::class,
            'member_id' => Entity::factory(),
            'requester_type' => Entity::class,
            'requester_id' => fn () => Entity::factory(),
        ]);
    }

    public function federationFacilitated(): static
    {
        return $this->state(fn () => [
            'request_type' => 'federation_facilitated',
        ]);
    }
}
