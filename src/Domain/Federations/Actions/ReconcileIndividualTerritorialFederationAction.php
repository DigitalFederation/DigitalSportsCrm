<?php

namespace Domain\Federations\Actions;

use Domain\Entities\Models\Entity;
use Domain\Federations\DataTransferObjects\TerritorialFederationResolution;
use Domain\Federations\Enums\TerritorialAssignmentSource;
use Domain\Federations\Enums\TerritorialResolutionOutcome;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\IndividualFederation;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Individuals\States\PendingIndividualFederationState;
use Illuminate\Support\Facades\DB;

class ReconcileIndividualTerritorialFederationAction
{
    public function __construct(
        private readonly ResolveIndividualTerritorialFederationAction $resolver = new ResolveIndividualTerritorialFederationAction
    ) {}

    public function execute(
        Individual $individual,
        ?int $excludedEntityId = null,
        ?Entity $preferredEntity = null,
        ?TerritorialAssignmentSource $sourceOverride = null,
    ): TerritorialFederationResolution {
        $resolution = $this->resolver->execute($individual, $excludedEntityId, $preferredEntity);

        if (! $resolution->isResolved()) {
            $this->logUnresolved($individual, $resolution);

            return $resolution;
        }

        DB::transaction(function () use ($individual, $resolution, $sourceOverride): void {
            $hasManualAssignment = IndividualFederation::query()
                ->where('individual_id', $individual->id)
                ->where('assignment_source', TerritorialAssignmentSource::MANUAL->value)
                ->whereHas('federation', fn ($query) => $query->where('is_local', true))
                ->exists();

            if ($hasManualAssignment) {
                return;
            }

            $this->removeSupersededAutomaticAssignments($individual, $resolution->federation->id);

            $membership = IndividualFederation::query()->firstOrNew([
                'individual_id' => $individual->id,
                'federation_id' => $resolution->federation->id,
            ]);

            $isClubAssignment = $resolution->source === TerritorialAssignmentSource::CLUB;
            $isAlreadyActive = $membership->status_class === ActiveIndividualFederationState::class;

            $assignmentSource = $sourceOverride ?? $resolution->source;

            $membership->fill([
                'assignment_source' => $assignmentSource,
                'assignment_entity_id' => $resolution->entity?->id,
                'assignment_zone_id' => $resolution->zone?->id,
                'assignment_district_id' => $resolution->district?->id,
                'assigned_at' => now(),
                'status_class' => $isClubAssignment || $isAlreadyActive
                    ? ActiveIndividualFederationState::class
                    : PendingIndividualFederationState::class,
                'active' => $isClubAssignment || $isAlreadyActive,
            ])->save();

            activity('Individual Territorial Federation')
                ->performedOn($individual)
                ->event('assign')
                ->withProperties([
                    'federation_id' => $resolution->federation->id,
                    'assignment_source' => $assignmentSource->value,
                    'assignment_entity_id' => $resolution->entity?->id,
                    'assignment_zone_id' => $resolution->zone?->id,
                    'assignment_district_id' => $resolution->district?->id,
                ])
                ->log('Individual territorial federation reconciled');
        });

        return $resolution;
    }

    private function removeSupersededAutomaticAssignments(Individual $individual, int $federationId): void
    {
        IndividualFederation::query()
            ->where('individual_id', $individual->id)
            ->where('federation_id', '!=', $federationId)
            ->whereIn('assignment_source', TerritorialAssignmentSource::automaticValues())
            ->delete();
    }

    private function logUnresolved(
        Individual $individual,
        TerritorialFederationResolution $resolution
    ): void {
        activity('Individual Territorial Federation')
            ->performedOn($individual)
            ->event('review')
            ->withProperties([
                'outcome' => $resolution->outcome->value,
                'assignment_source' => $resolution->source?->value,
                'candidate_federation_ids' => $resolution->candidates->pluck('id')->values()->all(),
                'assignment_zone_id' => $resolution->zone?->id,
                'assignment_district_id' => $resolution->district?->id,
            ])
            ->log($resolution->outcome === TerritorialResolutionOutcome::AMBIGUOUS
                ? 'Territorial federation requires review because multiple mappings apply'
                : 'Territorial federation requires review because no mapping applies');
    }
}
