<?php

namespace Domain\Memberships\Actions;

use Domain\Memberships\Models\MembershipPackage;
use Illuminate\Support\Collection;

class FilterInsuranceOnlyPackagesAction
{
    /**
     * Filter out packages that only have insurance items and no affiliation plans
     */
    public function execute(Collection $packages): Collection
    {
        return $packages->filter(function (MembershipPackage $package) {
            // Package must have at least one affiliation plan
            if ($package->affiliationPlans->isEmpty()) {
                return false;
            }

            // If package has exactly 1 item total and it's an insurance, exclude it
            $totalItems = $package->affiliationPlans->count() + $package->insurancePlans->count();
            if ($totalItems === 1 && $package->insurancePlans->count() === 1) {
                return false;
            }

            return true;
        });
    }
}
