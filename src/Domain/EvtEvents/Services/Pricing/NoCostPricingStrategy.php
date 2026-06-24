<?php

namespace Domain\EvtEvents\Services\Pricing;

class NoCostPricingStrategy implements PricingStrategy
{
    public function calculate(int $eventId, array $enrollments): float
    {
        return 0.0;
    }
}
