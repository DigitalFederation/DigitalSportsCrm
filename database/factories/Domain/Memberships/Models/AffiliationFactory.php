<?php

namespace Database\Factories\Domain\Memberships\Models;

use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Models\Affiliation;
use Domain\Memberships\States\ActiveAffiliationState;
use Illuminate\Database\Eloquent\Factories\Factory;

class AffiliationFactory extends Factory
{
    protected $model = Affiliation::class;

    public function definition(): array
    {
        $memberType = $this->faker->randomElement(['entity', 'individual']);

        return [
            'federation_id' => Federation::factory(),
            'member_type' => $memberType,
            'member_id' => function () use ($memberType) {
                return $memberType === 'entity'
                    ? Entity::factory()
                    : Individual::factory();
            },
            'member_subscription_id' => \Domain\Memberships\Models\MemberSubscription::factory(),
            'status_class' => ActiveAffiliationState::class,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addYear(),
        ];
    }

    public function pending(): self
    {
        return $this->state(fn (array $attributes) => [
            'status_class' => \Domain\Memberships\States\PendingAffiliationState::class,
        ]);
    }

    public function expired(): self
    {
        return $this->state(fn (array $attributes) => [
            'status_class' => \Domain\Memberships\States\ExpiredAffiliationState::class,
            'start_date' => now()->subYears(2),
            'end_date' => now()->subMonth(),
        ]);
    }

    public function forEntity(Entity $entity): self
    {
        return $this->state(fn (array $attributes) => [
            'member_type' => 'entity',
            'member_id' => $entity->id,
        ]);
    }

    public function forIndividual(Individual $individual): self
    {
        return $this->state(fn (array $attributes) => [
            'member_type' => 'individual',
            'member_id' => $individual->id,
        ]);
    }
}
