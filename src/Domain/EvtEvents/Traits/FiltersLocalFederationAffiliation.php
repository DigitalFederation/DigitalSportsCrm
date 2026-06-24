<?php

namespace Domain\EvtEvents\Traits;

use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\EvtEvents\Models\Competition;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Illuminate\Database\Eloquent\Builder;

trait FiltersLocalFederationAffiliation
{
    /**
     * Apply the local federation affiliation requirement to a query.
     * Individual must have active membership in at least one of the entity's local federations.
     */
    protected function applyLocalFederationFilter(Builder $query, Competition $competition, ?Entity $entity): Builder
    {
        if (! $competition->requires_local_federation_affiliation || ! $entity) {
            return $query;
        }

        // Get entity's active local federations (territorial associations)
        $entityLocalFederationIds = $entity->entityFederations()
            ->where('status_class', ActiveEntityFederationState::class)
            ->whereHas('federation', fn ($q) => $q->where('is_local', true))
            ->pluck('federation_id')
            ->toArray();

        if (empty($entityLocalFederationIds)) {
            // Entity has no local federations - no individuals can match
            $query->whereRaw('1 = 0');

            return $query;
        }

        // Individual must have active membership in at least one of entity's local federations
        $query->whereHas('individualFederations', function ($q) use ($entityLocalFederationIds) {
            $q->whereIn('federation_id', $entityLocalFederationIds)
                ->where('status_class', ActiveIndividualFederationState::class);
        });

        return $query;
    }
}
