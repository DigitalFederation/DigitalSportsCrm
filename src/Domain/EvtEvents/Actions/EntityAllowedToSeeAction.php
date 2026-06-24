<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtEventEnrollmentTypeEnum;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\EvtEvents\States\PreparationEventState;
use Illuminate\Support\Facades\Auth;

class EntityAllowedToSeeAction
{
    public function execute(Event $event): bool
    {
        $userEntityId = Auth::user()->getEntityId();
        $userEntityCountryId = Entity::where('id', $userEntityId)->first()->country_id;

        // Check if the event is visible first
        if (! $event->is_visible) {
            return false;
        }

        // Check if the event is in an allowed state (Active or Preparation)
        $isInAllowedState = in_array($event->status_class, [
            ActiveEventState::class,
            PreparationEventState::class,
        ]);

        // Simplified visibility - no geographic coverage restrictions
        $isGeographicallyAllowed = true;

        // Check if the enrollment type allows entities
        $isEntityAllowed = in_array($event->enrollment_type, [
            EvtEventEnrollmentTypeEnum::only_entities->name,
            EvtEventEnrollmentTypeEnum::only_federations_and_entities->name,
            EvtEventEnrollmentTypeEnum::all->name,
        ]);

        // If end_date of event has passed, you can't see it
        if (! empty($event->end_date) && $event->end_date < now()->startOfDay()) {
            return false;
        }

        // The event can be seen if it's in an allowed state and allows entities
        $result = $isInAllowedState && $isEntityAllowed && $isGeographicallyAllowed;

        return $result;
    }
}
