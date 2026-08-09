<?php

namespace Domain\Federations\Actions;

use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityFederation;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Federations\DataTransferObjects\TerritorialFederationResolution;
use Domain\Federations\Enums\TerritorialAssignmentSource;
use Domain\Federations\Enums\TerritorialResolutionOutcome;
use Domain\Federations\Models\Federation;
use Domain\Geographic\Models\District;
use Domain\Geographic\Models\Zone;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Illuminate\Support\Collection;

class ResolveIndividualTerritorialFederationAction
{
    public function execute(
        Individual $individual,
        ?int $excludedEntityId = null,
        ?Entity $preferredEntity = null,
    ): TerritorialFederationResolution {
        $clubResolution = $this->fromActiveClubs($individual, $excludedEntityId, $preferredEntity);

        if ($clubResolution !== null) {
            return $clubResolution;
        }

        return $this->fromResidence($individual);
    }

    private function fromActiveClubs(
        Individual $individual,
        ?int $excludedEntityId,
        ?Entity $preferredEntity,
    ): ?TerritorialFederationResolution {
        $entityIds = $individual->entities()
            ->wherePivot('status_class', ActiveIndividualEntityState::class)
            ->when($excludedEntityId !== null, fn ($query) => $query->where('entity.id', '!=', $excludedEntityId))
            ->pluck('entity.id');

        if ($preferredEntity !== null && $preferredEntity->id !== $excludedEntityId) {
            $entityIds->push($preferredEntity->id);
        }

        $entityIds = $entityIds->unique()->values();

        if ($entityIds->isEmpty()) {
            return null;
        }

        $memberships = EntityFederation::query()
            ->whereIn('entity_id', $entityIds)
            ->where('status_class', ActiveEntityFederationState::class)
            ->whereHas('federation', fn ($query) => $query->where('is_local', true))
            ->with(['entity', 'federation'])
            ->get();
        $candidates = $memberships->pluck('federation')->filter()->unique('id')->values();

        if ($candidates->isEmpty()) {
            return null;
        }

        if ($candidates->count() > 1) {
            return $this->ambiguous($candidates, TerritorialAssignmentSource::CLUB);
        }

        /** @var Federation $federation */
        $federation = $candidates->first();
        $contributingEntities = $memberships
            ->where('federation_id', $federation->id)
            ->pluck('entity')
            ->filter()
            ->unique('id')
            ->values();

        return new TerritorialFederationResolution(
            outcome: TerritorialResolutionOutcome::RESOLVED,
            candidates: $candidates,
            source: TerritorialAssignmentSource::CLUB,
            federation: $federation,
            entity: $contributingEntities->count() === 1 ? $contributingEntities->first() : null,
        );
    }

    private function fromResidence(Individual $individual): TerritorialFederationResolution
    {
        /** @var District|null $district */
        $district = $individual->district()->with(['administrativeZone', 'zones'])->first();

        if ($district === null) {
            return $this->unresolved(TerritorialAssignmentSource::RESIDENCE);
        }

        $zones = $district->administrativeZone !== null
            ? collect([$district->administrativeZone])
            : $district->zones;

        if ($zones->isEmpty()) {
            return $this->unresolved(TerritorialAssignmentSource::RESIDENCE, $district);
        }

        $candidates = Federation::query()
            ->where('is_local', true)
            ->where('country_id', $district->country_id)
            ->whereHas('zones', fn ($query) => $query->whereIn('zones.id', $zones->pluck('id')))
            ->get()
            ->unique('id')
            ->values();

        if ($candidates->count() > 1) {
            return $this->ambiguous(
                $candidates,
                TerritorialAssignmentSource::RESIDENCE,
                $district->administrativeZone,
                $district
            );
        }

        if ($candidates->isEmpty()) {
            return $this->unresolved(TerritorialAssignmentSource::RESIDENCE, $district);
        }

        /** @var Federation $federation */
        $federation = $candidates->first();

        return new TerritorialFederationResolution(
            outcome: TerritorialResolutionOutcome::RESOLVED,
            candidates: $candidates,
            source: TerritorialAssignmentSource::RESIDENCE,
            federation: $federation,
            zone: $district->administrativeZone ?? $zones->first(),
            district: $district,
        );
    }

    /**
     * @param  Collection<int, Federation>  $candidates
     */
    private function ambiguous(
        Collection $candidates,
        TerritorialAssignmentSource $source,
        ?Zone $zone = null,
        ?District $district = null
    ): TerritorialFederationResolution {
        return new TerritorialFederationResolution(
            outcome: TerritorialResolutionOutcome::AMBIGUOUS,
            candidates: $candidates,
            source: $source,
            zone: $zone,
            district: $district,
        );
    }

    private function unresolved(
        TerritorialAssignmentSource $source,
        ?District $district = null
    ): TerritorialFederationResolution {
        return new TerritorialFederationResolution(
            outcome: TerritorialResolutionOutcome::UNRESOLVED,
            candidates: collect(),
            source: $source,
            zone: $district?->administrativeZone,
            district: $district,
        );
    }
}
