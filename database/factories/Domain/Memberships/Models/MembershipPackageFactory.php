<?php

namespace Database\Factories\Domain\Memberships\Models;

use App\Enums\MembershipTargetType;
use Domain\Memberships\Models\MembershipPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MembershipPackage>
 */
class MembershipPackageFactory extends Factory
{
    protected $model = MembershipPackage::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true) . ' Package',
            'description' => $this->faker->paragraph(),
            'target_type' => $this->faker->randomElement(MembershipTargetType::cases()),
            'distribution_methods' => $this->faker->randomElements(['direct', 'entity_managed'], $this->faker->numberBetween(1, 2)),
            'is_active' => true,
            'version' => 1,
        ];
    }

    /**
     * Indicate that the membership package is for individuals
     */
    public function individual(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_type' => MembershipTargetType::INDIVIDUAL,
        ]);
    }

    /**
     * Indicate that the membership package is for entities
     */
    public function entity(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_type' => MembershipTargetType::ENTITY,
        ]);
    }

    /**
     * Indicate that the membership package is inactive
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
