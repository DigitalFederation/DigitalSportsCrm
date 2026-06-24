<?php

namespace Tests\Traits;

use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\Models\Affiliation;
use Domain\Memberships\States\ActiveAffiliationState;

trait MocksAffiliations
{
    /**
     * Create an active affiliation for an entity
     */
    protected function createActiveAffiliationForEntity(Entity $entity, ?Federation $federation = null): Affiliation
    {
        $federation = $federation ?? Federation::factory()->create();

        return Affiliation::factory()
            ->forEntity($entity)
            ->create([
                'federation_id' => $federation->id,
                'status_class' => ActiveAffiliationState::class,
                'start_date' => now()->subMonth(),
                'end_date' => now()->addYear(),
            ]);
    }

    /**
     * Create an active affiliation for an individual
     */
    protected function createActiveAffiliationForIndividual(Individual $individual, ?Federation $federation = null): Affiliation
    {
        $federation = $federation ?? Federation::factory()->create();

        return Affiliation::factory()
            ->forIndividual($individual)
            ->create([
                'federation_id' => $federation->id,
                'status_class' => ActiveAffiliationState::class,
                'start_date' => now()->subMonth(),
                'end_date' => now()->addYear(),
            ]);
    }

    /**
     * Mock hasActiveAffiliation to always return true
     */
    protected function mockActiveAffiliation($model): void
    {
        // If using Mockery
        if (method_exists($model, 'shouldReceive')) {
            $model->shouldReceive('hasActiveAffiliation')->andReturn(true);
        }

        // Alternative: Create actual affiliation
        if ($model instanceof Entity) {
            $this->createActiveAffiliationForEntity($model);
        } elseif ($model instanceof Individual) {
            $this->createActiveAffiliationForIndividual($model);
        }
    }

    /**
     * Bypass affiliation checks by creating necessary relationships
     */
    protected function bypassAffiliationChecks(Entity $entity): void
    {
        // Create federation relationship if needed
        if ($entity->federations()->count() === 0) {
            $federation = Federation::factory()->create();
            $entity->federations()->attach($federation);
        }

        // Create active affiliation
        $this->createActiveAffiliationForEntity($entity);
    }
}
