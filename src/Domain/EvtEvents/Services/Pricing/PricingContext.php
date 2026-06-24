<?php

namespace Domain\EvtEvents\Services\Pricing;

use App\Enums\EvtEventFeeTypeEnum;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Exception;

/**
 * Class PricingContext
 *
 * Manages the pricing strategy and calculates the total enrollment fee.
 */
class PricingContext
{
    private PricingStrategy $strategy;

    public function __construct(PricingStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * Set the pricing strategy to be used.
     *
     * @param  PricingStrategy  $strategy  The pricing strategy to use.
     */
    public function setStrategy(PricingStrategy $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * Calculate the total enrollment fee using the set strategy.
     *
     * @param  int  $eventId  The ID of the event.
     * @param  array  $enrollments  An array of enrollments with discipline IDs and counts.
     * @return float The total enrollment fee.
     *
     * @throws Exception If the pricing strategy is not set.
     */
    public function calculate(int $eventId, array $enrollments): float
    {
        if (! $this->strategy) {
            throw new Exception('Pricing strategy not set.');
        }

        return $this->strategy->calculate($eventId, $enrollments);
    }

    /**
     * Resolve the appropriate pricing strategy for an event based on its pricing type.
     *
     * @param  Event  $event  The event for which to resolve the pricing strategy.
     * @return PricingStrategy The resolved pricing strategy.
     *
     * @throws Exception If no active pricing is found for the event or the pricing type is unsupported.
     */
    public static function resolveStrategy(Event $event): PricingStrategy
    {
        $pricing = Pricing::where('event_id', $event->id)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (! $pricing) {
            return new NoCostPricingStrategy;
        }

        return match ($pricing->price_type) {
            EvtEventFeeTypeEnum::PER_PERSON->value => new PerPersonPricingStrategy,
            EvtEventFeeTypeEnum::FLAT_FEE->value => new FlatFeePricingStrategy,
            default => throw new Exception("Unsupported pricing type {$pricing->price_type}"),
        };
    }
}
