<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\AthleteEnrollmentAttributes;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Discipline;
use Illuminate\Support\Collection;

class ValidateAthleteMaxDisciplinesAction
{
    public function execute(
        Competition $competition,
        string $individualId,
        Discipline $newDiscipline,
        array &$errorMessages,
        ?array $attributeValues = null,
        array $processingInFormDisciplines = []
    ): bool {

        // Check if this is an out-of-race enrollment
        $isOutOfRace = $this->isOutOfRaceEnrollment($newDiscipline, $attributeValues);
        // If this is an out-of-race enrollment, skip the discipline limit validation
        if ($isOutOfRace) {
            return true;
        }

        // Determine enrollments to validate
        $allEnrollments = $this->buildEnrollmentsForValidation(
            $competition,
            $individualId,
            $newDiscipline,
            $attributeValues,
            $processingInFormDisciplines
        );

        /*
        // Get current enrollments for this athlete in this competition
        $currentEnrollments = AthleteEnrollment::query()
            ->where('individual_id', $individualId)
            ->where('event_id', $competition->event_id)
            ->whereNotNull('discipline_id')
            ->with('discipline', 'attributes.attribute')
            ->get();

        // Merge with disciplines being processed in this request
        // Create temporary collection of in-form disciplines
        // * Flow: Combines database records + temporary form enrollments
        $formEnrollments = collect($processingInFormDisciplines)->map(function ($data) {
            $enrollment = new AthleteEnrollment([
                'discipline_id' => $data['discipline_id'],
            ]);

            // Add attributes relationship
            $enrollment->setRelation('attributes', collect($data['attributes'])->map(function ($value, $id) {
                return new AthleteEnrollmentAttributes([
                    'attribute_id' => $id,
                    'value' => $value,
                ]);
            }));

            return $enrollment;
        });
        // Merge existing and form enrollments
        $allEnrollments = $currentEnrollments->merge($formEnrollments);
        */

        // Filter out-of-race enrollments
        $filteredEnrollments = $allEnrollments->filter(function ($enrollment) {
            $outOfRaceAttribute = app(GetDisciplineOutOfRaceAttributeAction::class)
                ->execute($enrollment->discipline);

            if (! $outOfRaceAttribute) {
                return true;
            }
            // $attributeValue = $enrollment->attributes->get($outOfRaceAttribute->id);
            $attributeValue = $enrollment->attributes
                ->firstWhere('attribute_id', $outOfRaceAttribute->id)
                ?->value;

            return $attributeValue !== 'yes';
        });

        // Count current enrollments by type
        $currentCounts = [
            'individual' => $filteredEnrollments->where('discipline.enrollment_type', 'individual')->count(),
            'relay' => $filteredEnrollments->where('discipline.enrollment_type', 'relay')->count(),
            'team' => $filteredEnrollments->where('discipline.enrollment_type', 'team')->count(),
        ];

        // Check against limits based on new discipline type
        $isValid = match ($newDiscipline->enrollment_type) {
            'individual' => $this->validateIndividualDiscipline($competition, $currentCounts, $errorMessages),
            'relay' => $this->validateRelayDiscipline($competition, $currentCounts, $errorMessages),
            'team' => $this->validateTeamDiscipline($competition, $currentCounts, $errorMessages),
            default => true
        };

        return $isValid;
    }

    protected function buildEnrollmentsForValidation(
        Competition $competition,
        string $individualId,
        Discipline $newDiscipline,
        ?array $attributeValues,
        array $processingInFormDisciplines
    ): Collection {

        if (! empty($processingInFormDisciplines)) {
            // Form submission: Use disciplines from the form (final state)
            // Create a collection of NEW disciplines from the form data (skip ones marked as existing)
            $formDisciplines = collect($processingInFormDisciplines)
                ->reject(function ($data) {
                    // Skip disciplines that are marked as existing to avoid double-counting
                    return isset($data['is_existing']) && $data['is_existing'] === true;
                })
                ->map(fn ($data) => $this->createEnrollmentFromData($data));

            // Get existing enrollments from database to include in validation
            $currentEnrollments = AthleteEnrollment::query()
                ->where('individual_id', $individualId)
                ->where('event_id', $competition->event_id)
                ->whereNotNull('discipline_id')
                ->with('discipline', 'attributes.attribute')
                ->get();

            // Combine existing enrollments with new ones from the form
            return $currentEnrollments->merge($formDisciplines);
        }

        // Standalone check (e.g., test): Merge existing enrollments + new discipline
        $currentEnrollments = AthleteEnrollment::query()
            ->where('individual_id', $individualId)
            ->where('event_id', $competition->event_id)
            ->whereNotNull('discipline_id')
            ->with('discipline', 'attributes.attribute')
            ->get();

        // Add the new discipline being validated
        $newEnrollment = $this->createEnrollmentFromData([
            'discipline_id' => $newDiscipline->id,
            'attributes' => $attributeValues ?? [],
        ]);

        return $currentEnrollments->push($newEnrollment);
    }

    protected function createEnrollmentFromData(array $data): AthleteEnrollment
    {
        $enrollment = new AthleteEnrollment([
            'discipline_id' => $data['discipline_id'],
        ]);

        $attributes = collect($data['attributes'] ?? [])->map(fn ($value, $id) => new AthleteEnrollmentAttributes([
            'attribute_id' => $id,
            'value' => $value,
        ]));

        $enrollment->setRelation('attributes', $attributes);

        return $enrollment;
    }

    private function validateIndividualDiscipline(Competition $competition, array $counts, array &$errorMessages): bool
    {

        // Null means unlimited
        if ($competition->max_disciplines_per_athlete === null) {
            return true;
        }

        if ($counts['individual'] > $competition->max_disciplines_per_athlete) {
            $errorMessages[] = "The selected athlete(s) have reached the maximum number of individual disciplines. Limit is  ({$competition->max_disciplines_per_athlete})";

            return false;
        }

        return true;
    }

    private function validateRelayDiscipline(Competition $competition, array $counts, array &$errorMessages): bool
    {
        // Null means unlimited
        if ($competition->max_relays_per_athlete === null) {
            return true;
        }

        if ($counts['relay'] > $competition->max_relays_per_athlete) {
            $errorMessages[] = "The selected athlete(s) have reached the maximum number of relay disciplines. Limit is ({$competition->max_relays_per_athlete})";

            return false;
        }

        return true;
    }

    private function validateTeamDiscipline(Competition $competition, array $counts, array &$errorMessages): bool
    {
        // Null means unlimited
        if ($competition->max_teams_per_athlete === null) {
            return true;
        }

        if ($counts['team'] > $competition->max_teams_per_athlete) {
            $errorMessages[] = "The selected athlete(s) have reached the maximum number of team disciplines. Limit is ({$competition->max_teams_per_athlete})";

            return false;
        }

        return true;
    }

    private function isOutOfRaceEnrollment(Discipline $discipline, ?array $attributeValues): bool
    {

        if (! $attributeValues) {
            return false;
        }

        $outOfRaceAction = app(GetDisciplineOutOfRaceAttributeAction::class);
        $outOfRaceAttribute = $outOfRaceAction->execute($discipline);

        if (! $outOfRaceAttribute) {
            return false;
        }
        // Check both direct value and nested value formats
        $value = $attributeValues[$outOfRaceAttribute->id] ?? null;
        // Handle both string 'yes' and array format
        if (is_array($value)) {
            return ($value['value'] ?? null) === 'yes';
        }

        return $value === 'yes';
    }
}
