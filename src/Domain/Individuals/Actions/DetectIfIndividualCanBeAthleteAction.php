<?php

namespace Domain\Individuals\Actions;

use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Entities\States\PendingEntityProfessionalRoleState;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Builder;

class DetectIfIndividualCanBeAthleteAction
{
    /**
     * Check if an individual can be invited as an athlete for a specific sport.
     *
     * Returns true if:
     * 1. The individual has the required athlete license for this sport
     * 2. The individual does NOT have an active or pending athlete association
     *    at ANY entity for the same sport (global rule)
     */
    public function __invoke(string $individualId, int $sportId): bool
    {
        return Individual::where('id', $individualId)
            ->whereHas('licenses', function (Builder $query) use ($sportId) {
                $query->whereHas('license', function (Builder $query) use ($sportId) {
                    $query->where('sport_id', $sportId)
                        ->whereHas('professionalRole', fn (Builder $query) => $query->where('role', 'ATHLETE'));
                });
            })
            ->whereDoesntHave('EntityAthletes', function (Builder $query) use ($sportId) {
                $query->where('sport_id', $sportId)
                    ->whereIn('status_class', [
                        ActiveEntityProfessionalRoleState::class,
                        PendingEntityProfessionalRoleState::class,
                    ]);
            })
            ->exists();
    }
}
