<?php

namespace Domain\EvtEvents\Actions;

use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Traits\FiltersLocalFederationAffiliation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Illuminate\Database\Eloquent\Builder;

class GetEligibleCoachesAction
{
    use FiltersLocalFederationAffiliation;

    protected ValidateCoachEnrollmentCertificationsAction $certificationValidator;

    public function __construct(ValidateCoachEnrollmentCertificationsAction $certificationValidator)
    {
        $this->certificationValidator = $certificationValidator;
    }

    public function execute(Event $event, int $organizationId, ?string $organizationType = null): Builder
    {
        $isEntity = $organizationType === 'entity';
        $isFederation = $organizationType === 'federation';

        $query = Individual::query()
            ->when($isFederation, function ($query) use ($organizationId) {
                $query->whereHas('individualFederations', function (Builder $q) use ($organizationId) {
                    $q->where('federation_id', $organizationId)
                        ->where('status_class', ActiveIndividualFederationState::class);
                });
            })
            ->when($isEntity, function ($query) use ($organizationId, $event) {
                $query->whereHas('individualEntities', function (Builder $q) use ($organizationId) {
                    $q->where('entity_id', $organizationId)
                        ->where('status_class', ActiveIndividualEntityState::class);
                });

                // Require coach to be registered for the event's sport in this entity (if toggle is enabled)
                if ($event->competition?->sport_id && $event->competition?->requires_coach_entity_sport_registration) {
                    $query->whereHas('professionalRoleEntities', function (Builder $q) use ($organizationId, $event) {
                        $q->where('entity_id', $organizationId)
                            ->where('sport_id', $event->competition->sport_id)
                            ->where('status_class', ActiveEntityProfessionalRoleState::class);
                    });
                }
            });

        // Add certification requirements filter
        if ($event->competition?->requiredCoachCertifications()->exists()) {
            $query->whereHas('certificationsAttributed', function ($query) use ($event) {
                $query->where('status_class', ActiveCertificationAttributedState::class)
                    ->whereHas('certification', function ($query) use ($event) {
                        $query->whereIn('certification.id', $event->competition->requiredCoachCertifications()->pluck('certification.id'));
                    });
            });
        }

        // Apply local federation affiliation requirement for Entity enrollments
        if ($event->competition && $isEntity) {
            $entity = Entity::find($organizationId);
            $this->applyLocalFederationFilter($query, $event->competition, $entity);
        }

        return $query;
    }
}
