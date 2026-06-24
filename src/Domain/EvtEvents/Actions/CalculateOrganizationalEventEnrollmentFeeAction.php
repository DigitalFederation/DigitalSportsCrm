<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Pricing;
use Exception;

class CalculateOrganizationalEventEnrollmentFeeAction
{
    /**
     * Calculate the total enrollment fee for an organizational event.
     *
     * @param  int  $eventId  The ID of the event.
     * @param  int  $numberOfEnrollees  The number of individuals/entities/federations enrolling.
     * @return float The total enrollment fee.
     *
     * @throws Exception If the event pricing cannot be found.
     */
    public function execute(int $eventId, int $numberOfEnrollees): float
    {
        $pricing = Pricing::active()
            ->where('event_id', $eventId)
            ->where('price_type', 'flat_fee')
            ->first();

        if (! $pricing) {
            throw new Exception("No active flat fee pricing found for event {$eventId}");
        }

        return $pricing->price * $numberOfEnrollees;
    }
}
