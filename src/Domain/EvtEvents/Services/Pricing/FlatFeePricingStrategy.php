<?php

namespace Domain\EvtEvents\Services\Pricing;

use Domain\EvtEvents\Models\Pricing;
use Exception;
use Illuminate\Support\Carbon;

/**
 * Class FlatFeePricingStrategy
 *
 * Implements the pricing strategy for events with a flat fee.
 */
class FlatFeePricingStrategy implements PricingStrategy
{
    /**
     * Calculate the total enrollment fee based on a flat fee.
     *
     * @param  int  $eventId  The ID of the event.
     * @param  array  $enrollments  An array of enrollments with discipline IDs and counts.
     * @return float The total enrollment fee.
     *
     * @throws Exception If the flat fee pricing for the event cannot be found.
     */
    public function calculate(int $eventId, array $enrollments): float
    {
        if (empty($enrollments)) {
            throw new Exception("No enrollments provided for event {$eventId}");
        }

        $pricingId = $enrollments[0]['pricing_id'] ?? null;
        $disciplineId = $enrollments[0]['discipline_id'] ?? null;
        $role = $enrollments[0]['role'] ?? null;

        $pricing = $this->findPricing($eventId, $pricingId, $disciplineId, $role);

        if (! $pricing) {
            throw new Exception("No active flat fee pricing found for event {$eventId}");
        }

        return $pricing->price;
    }

    protected function findPricing(int $eventId, ?int $pricingId, ?int $disciplineId, ?string $role): ?Pricing
    {
        if ($pricingId) {
            return Pricing::active()->find($pricingId);
        }

        $now = Carbon::now();

        $pricing = Pricing::active()
            ->where('event_id', $eventId)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->when($disciplineId, function ($query, $disciplineId) {
                $query->where(function ($query) use ($disciplineId) {
                    $query->whereNull('discipline_id')->orWhere('discipline_id', $disciplineId);
                });
            })
            ->when($role, function ($query, $role) {
                $query->where(function ($query) use ($role) {
                    $query->where('enrollment_role', '=', '')->orWhereNull('enrollment_role')->orWhere('enrollment_role', $role);
                });
            })
            ->first();

        return $pricing;
    }
}
