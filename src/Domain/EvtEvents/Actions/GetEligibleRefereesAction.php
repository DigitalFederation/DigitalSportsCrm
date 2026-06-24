<?php

namespace Domain\EvtEvents\Actions;

use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Traits\FiltersLocalFederationAffiliation;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Builder;

class GetEligibleRefereesAction
{
    use FiltersLocalFederationAffiliation;

    public function execute(Event $event, int $organizerId, ?string $organizationType = null): Builder
    {
        $query = Individual::query()
            ->whereHas('professionalRoles', function ($query) {
                $query->where('role', 'TECHNICAL_OFFICIAL');
            })
            ->when($event->competition?->required_referee_certifications, function ($query) use ($event) {
                $query->whereHas('certifications', function ($q) use ($event) {
                    $q->whereIn('certification_id', $event->competition->required_referee_certifications)
                        ->where('status_class', ActiveCertificationAttributedState::class);
                });
            });

        // Apply local federation affiliation requirement for Entity enrollments
        if ($event->competition && $organizationType === 'entity') {
            $entity = Entity::find($organizerId);
            $this->applyLocalFederationFilter($query, $event->competition, $entity);
        }

        return $query;
    }
}
