<?php

namespace Domain\EvtEvents\Services\Pricing;

use Domain\EvtEvents\Models\Pricing;
use Exception;
use Illuminate\Support\Carbon;

/**
 * Class PerPersonPricingStrategy
 *
 * Implements the pricing strategy for events with per-person pricing.
 */
class PerPersonPricingStrategy implements PricingStrategy
{
    /**
     * Calculate the total enrollment fee based on per-person pricing.
     *
     * @param  int  $eventId  The ID of the event.
     * @param  array  $enrollments  An array of enrollments with discipline IDs and counts.
     * @return float The total enrollment fee.
     *
     * @throws Exception If the pricing for any discipline cannot be found.
     */
    public function calculate(int $eventId, array $enrollments): float
    {
        $totalFee = 0.0;

        foreach ($enrollments as $enrollment) {
            $pricingId = $enrollment['pricing_id'] ?? null;
            $disciplineId = $enrollment['discipline_id'] ?? null;
            $role = $enrollment['role'] ?? null;
            $count = isset($enrollment['count']) ? $enrollment['count'] : 1;

            // dd($eventId, $pricingId, $disciplineId, $role);
            $pricing = $this->findPricing($eventId, $pricingId, $disciplineId, $role);

            if (! $pricing) {
                throw new Exception("No active pricing found for event {$eventId}" .
                    ($role ? " with role {$role}" : '') .
                    ($disciplineId ? " and discipline {$disciplineId}" : ''));
            }

            $pricePerEnrollment = $pricing->price * $count;
            $totalFee += $pricePerEnrollment;
        }

        return $totalFee;
    }

    /**
     * Find the pricing for the given parameters.
     */
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
