<?php

namespace Domain\EvtEvents\Services\Pricing;

/**
 * Interface PricingStrategy
 *
 * Defines the contract for pricing strategy implementations.
 */
interface PricingStrategy
{
    /**
     * Calculate the total enrollment fee for an event.
     *
     * @param  int  $eventId  The ID of the event.
     * @param  array  $enrollments  An array of enrollments with discipline IDs and counts.
     * @return float The total enrollment fee.
     */
    public function calculate(int $eventId, array $enrollments): float;
}
