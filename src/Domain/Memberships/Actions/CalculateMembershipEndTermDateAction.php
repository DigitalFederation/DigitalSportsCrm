<?php

namespace Domain\Memberships\Actions;

class CalculateMembershipEndTermDateAction
{
    public function __invoke(?string $current_term_starts_at, ?string $current_term_ends_at, int $interval, string $interval_unit): string
    {
        // Check MembershipPlan cycle
        $plan_interval = "+{$interval} {$interval_unit}";

        // Compare with last date if exists
        if (! empty($current_term_ends_at)) {
            $date_ends_at = strtotime($current_term_ends_at);
        } else {
            $date_ends_at = strtotime($current_term_starts_at);
        }

        // Return new date
        $end_term_date = date('Y-m-d', strtotime($plan_interval, $date_ends_at));

        return $end_term_date;
    }
}
