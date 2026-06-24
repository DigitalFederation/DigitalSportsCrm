<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtAttributeTypesEnum;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Discipline;
use Domain\Federations\Models\Federation;

class ValidateOutOfRaceEnrollmentAction
{
    public function execute(
        Discipline $discipline,
        int $eventId,
        Federation|Entity $enrollable,
        array $disciplineAttributeValues,
        array &$errorMessages
    ): bool {
        // Only applicable for individual disciplines
        if ($discipline->enrollment_type !== 'individual') {
            return true;
        }

        // Get the out-of-race attribute directly from the discipline's attributes
        $outOfRaceAttribute = app(GetDisciplineOutOfRaceAttributeAction::class)->execute($discipline);

        if (! $outOfRaceAttribute) {
            return true;
        }

        // Skip validation if athlete_limit is null (unlimited)
        if ($discipline->athlete_limit === null) {
            return true;
        }

        // Count new in-race athletes (treating NULL as in-race by default)
        $inRaceCount = collect($disciplineAttributeValues)
            ->filter(function ($values) use ($outOfRaceAttribute) {
                // Consider NULL or 'no' as in-race athletes
                return ($values[$outOfRaceAttribute->id] ?? null) === 'no' ||
                    ($values[$outOfRaceAttribute->id] ?? null) === null;
            })
            ->count();

        // Get current in-race enrollments
        $currentInRaceCount = AthleteEnrollment::where('discipline_id', $discipline->id)
            ->where('event_id', $eventId)
            ->where($enrollable instanceof Federation ? 'federation_id' : 'entity_id', $enrollable->id)
            ->whereHas('attributes', function ($query) use ($outOfRaceAttribute) {
                $query->where('attribute_id', $outOfRaceAttribute->id)
                    ->where(function ($subQuery) {
                        $subQuery->where('value', 'no')
                            ->orWhereNull('value');
                    });
            })
            ->count();

        // Add debug logging
        \Illuminate\Support\Facades\Log::debug('Out-of-race validation', [
            'discipline_id' => $discipline->id,
            'discipline_name' => $discipline->name,
            'event_id' => $eventId,
            'enrollable_id' => $enrollable->id,
            'enrollable_type' => get_class($enrollable),
            'current_in_race_count' => $currentInRaceCount,
            'new_in_race_count' => $inRaceCount,
            'total_in_race_count' => $currentInRaceCount + $inRaceCount,
            'athlete_limit' => $discipline->athlete_limit,
        ]);

        $totalInRaceCount = $currentInRaceCount + $inRaceCount;
        $athleteLimit = $discipline->athlete_limit ?? 'unlimited';

        if ($discipline->athlete_limit !== null && $totalInRaceCount > $discipline->athlete_limit) {
            $errorMessages[] = "The number of in-race athletes ({$totalInRaceCount}) exceeds the limit of {$athleteLimit} for {$discipline->name}.";

            return false;
        }

        return true;
    }

    public function shouldSkipInitialValidation(Discipline $discipline): bool
    {
        if ($discipline->enrollment_type !== 'individual') {
            return false;
        }

        return $discipline->attributes()
            ->where('attribute_type', EvtAttributeTypesEnum::OUTOFRACE)
            ->exists();
    }

    public function isOutOfRace(array $attributeValues, int $attributeId): bool
    {
        if ($attributeId === null) {
            return false;
        }

        return ($attributeValues[$attributeId] ?? null) === 'yes';
    }

    public function getOutOfRaceAttributeId(Discipline $discipline): ?int
    {
        $attribute = app(GetDisciplineOutOfRaceAttributeAction::class)->execute($discipline);

        return $attribute?->id;
    }
}
