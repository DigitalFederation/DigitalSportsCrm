<?php

namespace Domain\EvtEvents\Actions;

use Carbon\Carbon;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Event;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\OfficialDocuments\States\ActiveOfficialDocumentState;
use Illuminate\Support\Facades\DB;

/**
 * Check if an individual meets all competition-level requirements for an event.
 * Returns an array of human-readable failure reasons. Empty array = eligible.
 *
 * This is used as a gate check for individual self-enrollment, where the question is
 * "can this person enroll at all?" rather than "which people from a group are eligible?".
 */
class CheckIndividualCompetitionEligibilityAction
{
    /**
     * @return array<string> Human-readable failure reasons. Empty = eligible.
     */
    public function execute(Event $event, Individual $individual): array
    {
        if (! $event->competition) {
            return [];
        }

        $reasons = [];

        $this->checkRequiredLicenses($event->competition, $individual, $reasons);
        $this->checkRequiredDocuments($event->competition, $individual, $reasons, $event->end_date);

        return $reasons;
    }

    protected function checkRequiredLicenses(Competition $competition, Individual $individual, array &$reasons): void
    {
        if (empty($competition->required_athlete_licenses)) {
            return;
        }

        $hasRequiredLicense = $individual->licenses()
            ->whereIn('license_id', $competition->required_athlete_licenses)
            ->where('status_class', ActiveLicenseAttributedState::class)
            ->where(function ($q) {
                $q->whereNull('current_term_ends_at')
                    ->orWhere('current_term_ends_at', '>', DB::raw('CURDATE()'));
            })
            ->exists();

        if (! $hasRequiredLicense) {
            $reasons[] = __('events.competition_missing_required_license');
        }
    }

    protected function checkRequiredDocuments(Competition $competition, Individual $individual, array &$reasons, ?Carbon $eventEndDate = null): void
    {
        if (empty($competition->required_athlete_documents)) {
            return;
        }

        foreach ($competition->required_athlete_documents as $docType) {
            $hasDocument = $individual->officialDocuments()
                ->where('type', $docType)
                ->where('status_class', ActiveOfficialDocumentState::class)
                ->where(function ($q) use ($eventEndDate) {
                    $q->whereNull('expiry_date')
                        ->orWhereDate('expiry_date', '>=', $eventEndDate ?? now());
                })
                ->exists();

            if (! $hasDocument) {
                $translatedType = __('official_documents.types.' . $docType);
                $reasons[] = __('events.competition_missing_required_document', ['document' => $translatedType]);
            }
        }
    }
}
