<?php

namespace Domain\Individuals\Actions;

use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Federations\Actions\ReconcileIndividualTerritorialFederationAction;
use Domain\Federations\Actions\ResolveIndividualTerritorialFederationAction;
use Domain\Federations\Enums\TerritorialAssignmentSource;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\IndividualFederation;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Illuminate\Support\Collection;

class SyncIndividualLocalFederationsAction
{
    public function __construct(
        private readonly ResolveIndividualTerritorialFederationAction $resolver = new ResolveIndividualTerritorialFederationAction,
        private readonly ReconcileIndividualTerritorialFederationAction $reconciler = new ReconcileIndividualTerritorialFederationAction,
    ) {}

    /**
     * Sync individual's local federation memberships based on entity's memberships.
     * Called when an individual joins an entity (accepts invitation or is approved).
     */
    public function execute(Individual $individual, Entity $entity): Collection
    {
        $resolution = $this->resolver->execute($individual, preferredEntity: $entity);

        if (! $resolution->isResolved() || $resolution->source !== TerritorialAssignmentSource::CLUB) {
            $this->reconciler->execute($individual, preferredEntity: $entity);

            return collect();
        }

        $existing = IndividualFederation::query()
            ->where('individual_id', $individual->id)
            ->where('federation_id', $resolution->federation->id)
            ->first();
        $wasActive = $existing?->status_class === ActiveIndividualFederationState::class;

        $this->reconciler->execute($individual, preferredEntity: $entity);

        $syncedFederations = $wasActive ? collect() : collect([$resolution->federation]);

        if ($syncedFederations->isNotEmpty()) {
            activity('Individual Local Federation Sync')
                ->performedOn($individual)
                ->event('sync')
                ->withProperties([
                    'entity_id' => $entity->id,
                    'entity_name' => $entity->name,
                    'federations_synced' => $syncedFederations->pluck('name')->toArray(),
                ])
                ->log('Individual synced to local federations from entity');
        }

        return $syncedFederations;
    }

    /**
     * Remove individual's local federation memberships when leaving an entity.
     * Only removes if the individual has no other active entity in that federation.
     */
    public function removeOnDeactivation(Individual $individual, Entity $entity): Collection
    {
        $removedFederations = collect();

        // Get the entity's local federations
        $entityLocalFederations = $entity->entityFederations()
            ->whereHas('federation', fn ($q) => $q->where('is_local', true))
            ->with('federation')
            ->get();

        foreach ($entityLocalFederations as $entityFederation) {
            // Check if individual has another active entity in this federation
            $hasOtherEntity = $individual->entities()
                ->where('individual_entity.status_class', ActiveIndividualEntityState::class)
                ->where('entity.id', '!=', $entity->id)
                ->whereHas('entityFederations', fn ($q) => $q
                    ->where('federation_id', $entityFederation->federation_id)
                    ->where('status_class', ActiveEntityFederationState::class))
                ->exists();

            if (! $hasOtherEntity) {
                $deleted = IndividualFederation::where('individual_id', $individual->id)
                    ->where('federation_id', $entityFederation->federation_id)
                    ->where(function ($query) {
                        $query->whereNull('assignment_source')
                            ->orWhere('assignment_source', TerritorialAssignmentSource::CLUB->value);
                    })
                    ->delete();

                if ($deleted) {
                    $removedFederations->push($entityFederation->federation);
                }
            }
        }

        $this->reconciler->execute($individual, $entity->id);

        if ($removedFederations->isNotEmpty()) {
            activity('Individual Local Federation Sync')
                ->performedOn($individual)
                ->event('remove')
                ->withProperties([
                    'entity_id' => $entity->id,
                    'entity_name' => $entity->name,
                    'federations_removed' => $removedFederations->pluck('name')->toArray(),
                ])
                ->log('Individual removed from local federations after leaving entity');
        }

        return $removedFederations;
    }
}
