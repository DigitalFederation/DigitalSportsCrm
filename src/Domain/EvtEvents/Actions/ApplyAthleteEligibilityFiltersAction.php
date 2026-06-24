<?php

namespace Domain\EvtEvents\Actions;

use Carbon\Carbon;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Event;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\OfficialDocuments\States\ActiveOfficialDocumentState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ApplyAthleteEligibilityFiltersAction
{
    public function execute(Builder $query, Event $event, ?Discipline $discipline, ?Entity $entity = null): Builder
    {
        // Apply competition-specific requirements
        if ($event->competition) {
            $this->applyCompetitionRequirements($query, $event->competition, $entity, $event->end_date);
        }

        // Apply discipline-specific requirements if discipline exists
        if ($discipline) {
            $this->applyDisciplineRequirements($query, $discipline);

            // Filter out already enrolled athletes only if discipline exists
            $query->whereDoesntHave('athleteEnrollments', function (Builder $query) use ($event, $discipline) {
                return $query->where('event_id', $event->id)
                    ->where('discipline_id', $discipline->id);
            });
        }

        return $query;
    }

    protected function applyCompetitionRequirements(Builder $query, Competition $competition, ?Entity $entity = null, ?Carbon $eventEndDate = null): void
    {
        if (! empty($competition->required_athlete_licenses)) {
            $query->whereHas('licenses', function (Builder $query) use ($competition) {
                $query->whereIn('license_id', $competition->required_athlete_licenses)
                    ->where('status_class', ActiveLicenseAttributedState::class)
                    ->where(function ($q) {
                        $q->whereNull('current_term_ends_at')
                            ->orWhere('current_term_ends_at', '>', DB::raw('CURDATE()'));
                    });
            });
        }

        if ($competition->requires_local_federation_affiliation && $entity) {
            $this->applyLocalFederationRequirement($query, $entity);
        }

        if ($entity && $competition->sport_id && $competition->requires_athlete_entity_sport_registration) {
            $this->applyEntityAthleteRequirement($query, $entity, $competition->sport_id);
        }

        if (! empty($competition->required_athlete_documents)) {
            $this->applyRequiredDocuments($query, $competition->required_athlete_documents, $eventEndDate);
        }
    }

    /**
     * Apply the local federation affiliation requirement.
     * Individual must have active membership in at least one of the entity's local federations.
     */
    protected function applyLocalFederationRequirement(Builder $query, Entity $entity): void
    {
        // Get entity's active local federations (territorial associations)
        $entityLocalFederationIds = $entity->entityFederations()
            ->where('status_class', ActiveEntityFederationState::class)
            ->whereHas('federation', fn ($q) => $q->where('is_local', true))
            ->pluck('federation_id')
            ->toArray();

        if (empty($entityLocalFederationIds)) {
            // Entity has no local federations - no individuals can match
            $query->whereRaw('1 = 0');

            return;
        }

        // Individual must have active membership in at least one of entity's local federations
        $query->whereHas('individualFederations', function ($q) use ($entityLocalFederationIds) {
            $q->whereIn('federation_id', $entityLocalFederationIds)
                ->where('status_class', ActiveIndividualFederationState::class);
        });
    }

    /**
     * Apply the entity athlete requirement.
     * Individual must be registered as an athlete for this sport in the entity.
     */
    protected function applyEntityAthleteRequirement(Builder $query, Entity $entity, int $sportId): void
    {
        $query->whereHas('entityAthletes', function (Builder $q) use ($entity, $sportId) {
            $q->where('entity_id', $entity->id)
                ->where('sport_id', $sportId)
                ->where('status_class', ActiveEntityProfessionalRoleState::class);
        });
    }

    /**
     * Apply the required documents filter.
     * Individual must have ALL required documents with active status and valid expiry.
     * Document expiry_date must be >= the event's end_date (not just > now()).
     *
     * @param  array<string>  $requiredDocumentTypes
     */
    protected function applyRequiredDocuments(Builder $query, array $requiredDocumentTypes, ?Carbon $eventEndDate = null): void
    {
        foreach ($requiredDocumentTypes as $docType) {
            $query->whereHas('officialDocuments', function (Builder $q) use ($docType, $eventEndDate) {
                $q->where('type', $docType)
                    ->where('status_class', ActiveOfficialDocumentState::class)
                    ->where(function (Builder $expiry) use ($eventEndDate) {
                        $expiry->whereNull('expiry_date')
                            ->orWhereDate('expiry_date', '>=', $eventEndDate ?? now());
                    });
            });
        }
    }

    protected function applyDisciplineRequirements(Builder $query, Discipline $discipline): void
    {
        // Apply discipline-specific license requirements
        if ($discipline->licenses->isNotEmpty()) {
            $disciplineLicenseIds = $discipline->licenses()->pluck('license.id');
            $query->whereHas('licenses', function (Builder $query) use ($disciplineLicenseIds) {
                $query->whereIn('license_id', $disciplineLicenseIds)
                    ->where('status_class', ActiveLicenseAttributedState::class)
                    ->where(function ($q) {
                        $q->whereNull('current_term_ends_at')
                            ->orWhere('current_term_ends_at', '>', DB::raw('CURDATE()'));
                    });
            });
        }

        // Apply gender requirements
        if (in_array($discipline->gender, ['male', 'female'])) {
            $query->where('gender', $discipline->gender);
        }

        if ($discipline->sportAgeGroups->isNotEmpty()) {
            $query->where(function (Builder $subQuery) use ($discipline) {
                foreach ($discipline->sportAgeGroups as $sportAgeGroup) {
                    $subQuery->orWhere(function ($q) use ($sportAgeGroup) {
                        $q->whereDate('birthdate', '>=', $sportAgeGroup->birthday_start)
                            ->whereDate('birthdate', '<=', $sportAgeGroup->birthday_end);
                    });
                }
            });
        }
    }
}
