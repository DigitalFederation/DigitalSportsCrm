<?php

declare(strict_types=1);

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Event;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\States\ActiveLicenseAttributedState; // Correct Import
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class GetIneligibleDisciplinesForIndividualAction
{
    public const REASON_ALREADY_ENROLLED = 'ALREADY_ENROLLED';
    public const REASON_WRONG_ENROLLMENT_TYPE = 'WRONG_ENROLLMENT_TYPE';
    public const REASON_WRONG_GENDER = 'WRONG_GENDER';
    public const REASON_AGE_INELIGIBLE = 'AGE_INELIGIBLE';
    public const REASON_MISSING_LICENSE = 'MISSING_LICENSE';

    /**
     * Execute the action to get disciplines the individual is ineligible for with reasons.
     *
     * @param  Event  $event  The event to check against.
     * @param  Individual  $individual  The individual to check eligibility for.
     * @return Collection<int, array{discipline: Discipline, reason: string}> A collection of discipline-reason pairs.
     */
    public function execute(Event $event, Individual $individual): Collection
    {
        // Check if event has competition and discipline template
        if (! $event->competition || ! $event->competition->disciplineTemplate) {
            return collect(); // Return empty collection if no competition or discipline template
        }

        // Get all disciplines potentially related to the event
        $allDisciplines = $event->competition->disciplineTemplate->disciplines()
            ->with(['attributes', 'sportAgeGroups', 'licenses'])
            ->get();

        // Get the disciplines the individual is *actually* enrolled in for this event
        $enrolledDisciplines = (new GetAthleteEnrolledDisciplinesFromEvent)->execute($event, $individual);
        $enrolledDisciplineIds = $enrolledDisciplines->pluck('id');

        $ineligibleDisciplines = collect();

        /** @var Discipline $discipline */
        foreach ($allDisciplines as $discipline) {
            // Skip checks if already enrolled in this specific discipline
            if ($enrolledDisciplineIds->contains($discipline->id)) {
                continue;
            }

            $reasonsForThisDiscipline = [];

            // 1. Check enrollment type
            if ($discipline->enrollment_type !== 'individual') {
                $reasonsForThisDiscipline[] = self::REASON_WRONG_ENROLLMENT_TYPE;
            }

            // 2. Check gender
            if (! empty($discipline->gender) && $discipline->gender !== 'mixed' && $discipline->gender !== $individual->gender) {
                $reasonsForThisDiscipline[] = self::REASON_WRONG_GENDER;
            }

            // 3. Check age
            if ($discipline->sportAgeGroups->isNotEmpty()) {
                $birthdate = $individual->birthdate;
                if (! $birthdate) {
                    Log::warning("Individual ID {$individual->id} missing birthdate for age eligibility check.");
                    $reasonsForThisDiscipline[] = self::REASON_AGE_INELIGIBLE;
                } else {
                    $isEligibleAge = $discipline->sportAgeGroups->contains(function ($sportAgeGroup) use ($birthdate, $discipline) {
                        $start = $sportAgeGroup->birthday_start;
                        $end = $sportAgeGroup->birthday_end;
                        if ($start instanceof \Carbon\Carbon && $end instanceof \Carbon\Carbon && $birthdate instanceof \Carbon\Carbon) {
                            return $birthdate->gte($start) && $birthdate->lte($end);
                        }
                        Log::warning("Invalid date format encountered during age check for discipline ID {$discipline->id} and sportAgeGroup ID {$sportAgeGroup->id}.");

                        return false;
                    });
                    if (! $isEligibleAge) {
                        $reasonsForThisDiscipline[] = self::REASON_AGE_INELIGIBLE;
                    }
                }
            }

            // 4. Check licenses
            if ($discipline->licenses->isNotEmpty()) {
                $requiredLicenseIds = $discipline->licenses->pluck('id');
                $eventDate = $event->start_date ?? now();

                $hasValidLicense = $individual->licenses()
                    ->where('status_class', ActiveLicenseAttributedState::class)
                    ->whereHas('license', function (Builder $query) use ($discipline, $requiredLicenseIds) {
                        $query->where('sport_id', $discipline->sport_id)
                            ->whereIn('license.id', $requiredLicenseIds);
                    })
                    ->where(function (Builder $query) use ($eventDate) {
                        $query->whereNull('current_term_ends_at')
                            ->orWhere('current_term_ends_at', '>=', $eventDate->toDateString());
                    })
                    ->exists();

                if (! $hasValidLicense) {
                    $reasonsForThisDiscipline[] = self::REASON_MISSING_LICENSE;
                }
            }

            // Add each reason as a separate entry with the discipline
            foreach ($reasonsForThisDiscipline as $reason) {
                $ineligibleDisciplines->push([
                    'discipline' => $discipline,
                    'reason' => $this->translateReason($reason),
                ]);
            }
        }

        return $ineligibleDisciplines;
    }

    /**
     * Translate reason constant to human-readable text.
     */
    protected function translateReason(string $reason): string
    {
        return match ($reason) {
            self::REASON_ALREADY_ENROLLED => __('events.ineligible_already_enrolled'),
            self::REASON_WRONG_ENROLLMENT_TYPE => __('events.ineligible_wrong_enrollment_type'),
            self::REASON_WRONG_GENDER => __('events.ineligible_wrong_gender'),
            self::REASON_AGE_INELIGIBLE => __('events.ineligible_age'),
            self::REASON_MISSING_LICENSE => __('events.ineligible_missing_license'),
            default => $reason,
        };
    }
}
