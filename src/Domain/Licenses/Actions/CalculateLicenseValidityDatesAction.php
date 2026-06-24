<?php

namespace Domain\Licenses\Actions;

use Carbon\Carbon as BaseCarbon;
use Domain\Licenses\Models\License;
use Illuminate\Support\Carbon;

class CalculateLicenseValidityDatesAction
{
    /**
     * Calculate license validity start and end dates based on the license configuration
     *
     * @param  License  $license  The license to calculate dates for
     * @param  BaseCarbon|null  $startDate  Optional start date, defaults to today
     * @return array{start_date: Carbon, end_date: Carbon|null} Array with calculated start and end dates
     */
    public function execute(License $license, ?BaseCarbon $startDate = null): array
    {
        $startDate = $startDate
            ? Carbon::instance($startDate->copy())
            : now();
        $validFrom = $startDate->copy()->startOfDay();

        // Handle licenses without expiration (no interval set)
        if (! $license->interval || ! $license->interval_unit) {
            return [
                'start_date' => $validFrom,
                'end_date' => null, // No expiration
            ];
        }

        // Check validity type
        $validityType = $license->validity_type ?? 'fixed_duration';

        // For calendar year licenses with yearly intervals, set to end of the target year
        if ($validityType === 'calendar_year' && $license->interval_unit === 'years') {
            $targetDate = $startDate->copy();

            if ($license->interval === 1) {
                // 1 year: end of current year
                $endDate = $targetDate->endOfYear()->endOfDay();
            } else {
                // Multiple years: add years minus 1, then go to end of that year
                // e.g., 3 years starting March 2025 → Dec 31, 2027
                $endDate = $targetDate->addYears($license->interval - 1)->endOfYear()->endOfDay();
            }

            return [
                'start_date' => $validFrom,
                'end_date' => $endDate,
            ];
        }

        // For fixed duration licenses, calculate normally
        $endDate = match ($license->interval_unit) {
            'weeks' => $startDate->copy()->addWeeks($license->interval)->endOfDay(),
            'months' => $startDate->copy()->addMonths($license->interval)->endOfDay(),
            'years' => $startDate->copy()->addYears($license->interval)->endOfDay(),
            default => null,
        };

        return [
            'start_date' => $validFrom,
            'end_date' => $endDate,
        ];
    }
}
