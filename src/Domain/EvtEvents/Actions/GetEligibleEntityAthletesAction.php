<?php

namespace Domain\EvtEvents\Actions;

use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Event;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class GetEligibleEntityAthletesAction
 *
 * This action class is responsible for retrieving eligible athletes for enrollment by a Entity in a specific event
 * and discipline, considering various filtering criteria such as federation membership, required licenses,
 * gender, and age restrictions. It ensures that only individuals who are not already enrolled in the selected
 * discipline are included in the returned results.
 */
class GetEligibleEntityAthletesAction
{
    private ApplyAthleteEligibilityFiltersAction $applyFiltersAction;

    public function __construct(ApplyAthleteEligibilityFiltersAction $applyFiltersAction)
    {
        $this->applyFiltersAction = $applyFiltersAction;
    }

    public function execute(int $eventId, int $entityId, ?int $disciplineId = null): Builder
    {
        $event = Event::findOrFail($eventId);
        $entity = Entity::findOrFail($entityId);

        $query = Individual::query()
            ->whereHas('individualEntities', function ($q) use ($entityId) {
                return $q->where('entity_id', $entityId)
                    ->where('status_class', ActiveIndividualEntityState::class);
            });

        // Only apply sport filter if competition exists and has required licenses
        if ($event->competition && ! empty($event->competition->required_athlete_licenses)) {
            $query->whereHas('licenses.license.sport', function (Builder $query) use ($event) {
                return $query->where('id', $event->competition->sport_id);
            });
        }

        $discipline = $disciplineId ? Discipline::findOrFail($disciplineId) : null;

        return $this->applyFiltersAction->execute($query, $event, $discipline, $entity);
    }
}
