<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Event;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class GetEligibleAthletesAction
 *
 * This action class is responsible for retrieving eligible athletes for enrollment in a specific event
 * and discipline, considering various filtering criteria such as federation membership, required licenses,
 * gender, and age restrictions. It ensures that only individuals who are not already enrolled in the selected
 * discipline are included in the returned results.
 */
class GetEligibleAthletesAction
{
    private ApplyAthleteEligibilityFiltersAction $applyFiltersAction;

    public function __construct(ApplyAthleteEligibilityFiltersAction $applyFiltersAction)
    {
        $this->applyFiltersAction = $applyFiltersAction;
    }

    /**
     * Execute the action to find eligible athletes.
     *
     * This method returns a query builder instance that filters athletes based on:
     * - Federation membership and active status
     * - Discipline-specific gender and age requirements
     * - Exclusion of athletes already enrolled in the given event and discipline
     *
     * @param  int  $eventId  The event ID for which eligible athletes are being retrieved.
     * @param  int  $federationId  The federation ID that athletes must be members of.
     * @return Builder Returns a query builder with applied filters to retrieve eligible athletes.
     */
    public function execute(int $eventId, int $federationId, ?int $disciplineId = null): Builder
    {
        $event = Event::findOrFail($eventId);

        $query = Individual::query()
            ->whereHas('individualFederations', function (Builder $query) use ($federationId) {
                $query->where('federation_id', $federationId)
                    ->where('status_class', ActiveIndividualFederationState::class);
            });

        // Only apply sport filter if competition has required licenses
        if ($event->competition && ! empty($event->competition->required_athlete_licenses)) {
            $query->whereHas('licenses.license.sport', function (Builder $query) use ($event) {
                $query->where('id', $event->competition->sport_id);
            });
        }

        $discipline = $disciplineId ? Discipline::findOrFail($disciplineId) : null;

        return $this->applyFiltersAction->execute($query, $event, $discipline);
    }
}
