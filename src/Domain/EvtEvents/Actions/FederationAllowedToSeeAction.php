<?php

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtEventEnrollmentTypeEnum;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\EvtEvents\States\PreparationEventState;
use Domain\Federations\Models\Federation;
use Illuminate\Support\Facades\Auth;

class FederationAllowedToSeeAction
{
    public function execute(Event $event): bool
    {
        $userFederationId = Auth::user()->getFederationId();
        $userFederationCountryId = Federation::where('id', $userFederationId)->first()->country_id;

        // Check if the event is visible first
        if (! $event->is_visible) {
            return false;
        }

        // Check if the event is active or in preparation state
        $isActive = false;
        if (
            $event->status_class == ActiveEventState::class ||
            $event->status_class == PreparationEventState::class
        ) {
            $isActive = true;
        }

        // Simplified visibility - no geographic coverage restrictions
        $isGeographicallyAllowed = true;

        // Check if the event is in preparation state and the user's federation is the organizer
        $isPreparationAndOrganizer = $event->status_class == PreparationEventState::class
            && $event->organizer_id == $userFederationId;

        // Check if the event is not exclusive to individuals or entities
        $isFederationExclusive = $event->enrollment_type === EvtEventEnrollmentTypeEnum::only_federations->name;
        $isNotExclusive = in_array($event->enrollment_type, [
            EvtEventEnrollmentTypeEnum::only_federations->name,
            EvtEventEnrollmentTypeEnum::all->name,
        ]);

        // if end_date of event as passed you cant see it
        if (! empty($event->end_date) && $event->end_date < now()->format('Y-m-d')) {
            return false;
        }

        // The event can be seen if:
        // 1. It's active (including preparation state) AND
        // 2. Either:
        //    - It's in preparation state and the federation is the organizer, or
        //    - It's federation exclusive, or
        //    - It's not exclusive to individuals/entities
        $result = $isActive && $isGeographicallyAllowed && ($isPreparationAndOrganizer || $isFederationExclusive || $isNotExclusive);

        return $result;
    }
}
