<?php

namespace Domain\EvtEvents\Actions;

use Domain\EvtEvents\Models\Event;

class PricingTierResolverAction
{
    /**
     * Determine the applicable pricing tier for an individual based on an event.
     *
     * @return int|null Returns the pricing tier ID or null if no applicable tier found
     */
    public function execute(Event $event): ?int
    {
        // Fetch all active pricing options for the event targeting individuals
        $pricingOptions = $event->pricing()->get();

        if ($pricingOptions->isEmpty()) {
            return null;
        }

        // Define selection criteria (e.g., membership status, registration date)
        $today = now();
        $selectedPricing = $pricingOptions->first(function ($pricing) use ($today) {
            return $today->between($pricing->start_date, $pricing->end_date);
        });

        // Return the ID of the selected pricing tier if found
        return $selectedPricing ? $selectedPricing->id : $pricingOptions->first()->id;
    }
}
