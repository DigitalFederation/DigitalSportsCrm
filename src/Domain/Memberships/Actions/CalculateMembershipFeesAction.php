<?php

namespace Domain\Memberships\Actions;

use Domain\Memberships\Models\MembershipPackage;
use Domain\Memberships\Models\MemberSubscription;

class CalculateMembershipFeesAction
{
    public function calculateTotalFee(MembershipPackage $package): float
    {
        $totalFee = $package->price;

        foreach ($package->affiliationPlans as $plan) {
            $totalFee += $plan->base_fee;
        }

        foreach ($package->insurancePlans as $plan) {
            $totalFee += $plan->fee;
        }

        return $totalFee;
    }

    public function calculateFederationRetention(MemberSubscription $subscription): float
    {
        // Implement federation retention calculation logic
        // This might involve checking federation rules and rates
        // For now, we'll return a placeholder value
        return $subscription->membershipPackage->price * 0.1; // 10% retention
    }
}
