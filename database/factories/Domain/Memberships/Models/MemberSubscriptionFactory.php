<?php

namespace Database\Factories\Domain\Memberships\Models;

use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Models\MemberSubscription;
use Domain\Memberships\States\ActiveMemberSubscriptionState;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemberSubscriptionFactory extends Factory
{
    protected $model = MemberSubscription::class;

    public function definition(): array
    {
        $memberType = $this->faker->randomElement([Entity::class, Individual::class]);

        return [
            'membership_package_id' => function () {
                // Create a membership package if needed
                return \Domain\Memberships\Models\MembershipPackage::firstOrCreate(
                    ['id' => 1],
                    [
                        'name' => 'Test Package',
                        'description' => 'Test Package for Tests',
                    ]
                )->id;
            },
            'member_type' => $memberType,
            'member_id' => function () use ($memberType) {
                return $memberType === Entity::class
                    ? Entity::factory()
                    : Individual::factory();
            },
            'start_date' => now()->subMonth(),
            'end_date' => now()->addYear(),
            'status_class' => ActiveMemberSubscriptionState::class,
        ];
    }

    public function forEntity(Entity $entity): self
    {
        return $this->state(fn (array $attributes) => [
            'member_type' => Entity::class,
            'member_id' => $entity->id,
        ]);
    }

    public function forIndividual(Individual $individual): self
    {
        return $this->state(fn (array $attributes) => [
            'member_type' => Individual::class,
            'member_id' => $individual->id,
        ]);
    }
}
