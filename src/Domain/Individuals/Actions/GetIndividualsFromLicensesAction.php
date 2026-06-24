<?php

namespace Domain\Individuals\Actions;

use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class GetIndividualsFromLicensesAction
{
    public function __invoke(array $licenseIds)
    {
        // Generate a cache key based on the license IDs
        $cacheKey = 'individuals_with_licenses:'.implode(',', $licenseIds);

        // Get individuals that have licenses associated with a specific set of licenses.
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($licenseIds) {
            // If not in cache, run the query and cache the results
            return Individual::whereHas('licenses', function (Builder $licenseQuery) use ($licenseIds) {
                $licenseQuery->whereIn('license_id', $licenseIds);
            })->get();
        });
    }
}
