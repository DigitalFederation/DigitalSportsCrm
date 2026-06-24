<?php

namespace Domain\Federations\Actions;

use Domain\Federations\Models\Federation;
use Domain\Licenses\Models\License;
use Domain\Memberships\States\ActiveMembershipState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class GetFederationsFromLicensesAction
{
    public function __invoke(array $licenseIds)
    {

        // Generate a cache key based on the license IDs
        $cacheKey = 'federations_with_licenses:'.implode(',', $licenseIds);

        // Get federations that have memberships associated with a specific set of licenses.
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($licenseIds) {
            // If not in cache, run the query and cache the results
            return Federation::whereHas('memberships', function (Builder $membershipQuery) use ($licenseIds) {
                $membershipQuery->where('status_class', ActiveMembershipState::class)
                    ->whereHas('plans', function (Builder $planQuery) use ($licenseIds) {
                        $planQuery->whereHas('licenses', function (Builder $licenseQuery) use ($licenseIds) {
                            $licenseQuery->whereIn('license.id', $licenseIds);
                        });
                    });
            })->get();
        });
    }
}
