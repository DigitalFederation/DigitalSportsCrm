<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtAttributeTypesEnum;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Discipline;
use Domain\Federations\Models\Federation;

class ValidateAthleteLimitAction
{
    public function execute(Discipline $discipline, int $eventId, $enrollable, array $selectedIndividuals): array
    {

        // For relay/team disciplines, perform special validation
        if (in_array($discipline->enrollment_type, ['relay', 'team'])) {

            return $this->validateRegularLimit($discipline, $eventId, $enrollable, $selectedIndividuals);
        }

        // For individual disciplines, check for out-of-race attribute
        $hasOutOfRaceAttribute = $discipline->attributes()
            ->where('attribute_type', EvtAttributeTypesEnum::OUTOFRACE->value)
            ->exists();

        // If there's no out-of-race attribute, perform regular validation
        if (! $hasOutOfRaceAttribute) {
            return $this->validateRegularLimit($discipline, $eventId, $enrollable, $selectedIndividuals);
        }

        // If there is an out-of-race attribute, skip first step validation
        return [
            'valid' => true,
            'message' => 'This discipline has out-of-race conditions. Limits will be validated after attribute selection.',
        ];
    }

    private function validateRegularLimit(Discipline $discipline, int $eventId, $enrollable, array $selectedIndividuals): array
    {

        $currentEnrollmentCount = $this->getCurrentEnrollmentCount($discipline, $eventId, $enrollable);
        $newEnrollmentsCount = count($selectedIndividuals);

        $isValid = ($currentEnrollmentCount + $newEnrollmentsCount) <= $discipline->athlete_limit;

        return [
            'valid' => $isValid,
            'message' => ! $isValid
                ? "The number of selected individuals exceeds the limit of {$discipline->athlete_limit} for this discipline."
                : null,
            'skip_first_step' => false,
        ];
    }

    private function getCurrentEnrollmentCount(Discipline $discipline, int $eventId, $enrollable): int
    {
        $query = AthleteEnrollment::where('discipline_id', $discipline->id)
            ->where('event_id', $eventId);

        // Apply federation/entity filter
        if ($enrollable instanceof Federation) {
            $query->where('federation_id', $enrollable->id);
        } elseif ($enrollable instanceof Entity) {
            $query->where('entity_id', $enrollable->id);
        }

        // Don't count enrollments for different discipline types
        // This is critical - we shouldn't mix relay and individual enrollments
        // when checking limits

        // For relay disciplines, we need to consider team_identifier to group athletes
        if (in_array($discipline->enrollment_type, ['relay', 'team'])) {
            // Count each distinct team only once for relay/team disciplines
            // This will group enrollments by team_identifier
            if ($discipline->enrollment_type === 'relay') {
                // Clone the query to keep original conditions
                $teamQuery = clone $query;

                // Get unique team identifiers (representing unique teams)
                $uniqueTeams = $teamQuery->whereNotNull('team_identifier')
                    ->distinct('team_identifier')
                    ->count('team_identifier');

                return $uniqueTeams;
            }
        }

        // Get the count with detailed logging
        $count = $query->count();

        return $count;
    }
}
