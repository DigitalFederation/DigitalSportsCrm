<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Event;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GetDisciplinesFromEventForIndividualAction
{
    /**
     * Execute the action to get eligible disciplines for an individual.
     */
    public function execute(Event $event, Individual $individual): Collection
    {
        // Check if event has competition and discipline template
        if (! $event->competition || ! $event->competition->disciplineTemplate) {
            return collect(); // Return empty collection if no competition or discipline template
        }

        // Get all disciplines related to the event
        $disciplines = $event->competition->disciplineTemplate->disciplines()->with(['attributes', 'sportAgeGroups', 'licenses'])->get();

        // Get the disciplines the individual is already enrolled in for this event
        $enrolledDisciplines = (new GetAthleteEnrolledDisciplinesFromEvent)->execute($event, $individual);

        // Filter disciplines based on individual's attributes
        $eligibleDisciplines = $disciplines->filter(function ($discipline) use ($event, $individual, $enrolledDisciplines) {

            // Check if the individual is already enrolled in this discipline
            if ($enrolledDisciplines->contains($discipline)) {

                return false;
            }

            // Check enrollment type
            if ($discipline->enrollment_type !== 'individual') {

                return false;
            }

            // Check gender
            if (! empty($discipline->gender) && $discipline->gender !== 'mixed' && $discipline->gender !== $individual->gender) {

                return false;
            }

            // Check age
            if ($discipline->sportAgeGroups->isNotEmpty()) {
                $birthdate = $individual->birthdate;
                $isEligibleAge = $discipline->sportAgeGroups->contains(function ($sportAgeGroup) use ($birthdate) {
                    return $birthdate >= $sportAgeGroup->birthday_start && $birthdate <= $sportAgeGroup->birthday_end;
                });
                if (! $isEligibleAge) {

                    return false;
                }
            }

            // Check licenses
            if ($discipline->licenses->isNotEmpty()) {
                $eventDate = $event->start_date ?? now(); // fallback to now if event start_date not set
                $hasValidLicense = $individual->licenses()
                    ->where('status_class', \Domain\Licenses\States\ActiveLicenseAttributedState::class)
                    ->whereHas('license', function (Builder $query) use ($discipline) {
                        $query->where('sport_id', $discipline->sport_id);
                    })
                    ->where(function ($query) use ($eventDate) {
                        $query->whereNull('current_term_ends_at')
                            ->orWhere('current_term_ends_at', '>=', $eventDate);
                    })
                    ->exists();

                if (! $hasValidLicense) {
                    return false;
                }
            }

            return true;
        });

        return $eligibleDisciplines->values();
    }
}
