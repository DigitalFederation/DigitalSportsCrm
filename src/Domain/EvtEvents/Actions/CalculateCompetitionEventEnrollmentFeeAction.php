<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Services\Pricing\PricingContext;
use Exception;

class CalculateCompetitionEventEnrollmentFeeAction
{
    /**
     * Execute the action to calculate the total enrollment fee.
     *
     * @param  int  $eventId  The ID of the event.
     * @param  array  $enrollments  An array of enrollments with discipline IDs and counts.
     * @return float The total enrollment fee.
     *
     * @throws Exception If no active pricing is found or the pricing type is unsupported.
     */
    public function execute(int $eventId, array $enrollments): float
    {

        $event = Event::findOrFail($eventId);
        $pricingStrategy = PricingContext::resolveStrategy($event);
        $pricingContext = new PricingContext($pricingStrategy);

        return $pricingContext->calculate($eventId, $enrollments);
    }
}
