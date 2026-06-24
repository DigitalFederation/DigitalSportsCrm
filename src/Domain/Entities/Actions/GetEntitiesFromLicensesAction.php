<?php

namespace Domain\Entities\Actions;

use Domain\Entities\Models\Entity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class GetEntitiesFromLicensesAction
{
    public function __invoke(array $licenseIds)
    {
        // Generate a cache key based on the license IDs
        $cacheKey = 'entities_with_licenses:'.implode(',', $licenseIds);

        // Get entities that have licenses associated with a specific set of licenses.
        return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($licenseIds) {
            // If not in cache, run the query and cache the results
            return Entity::whereHas('licenses', function (Builder $licenseQuery) use ($licenseIds) {
                $licenseQuery->whereIn('license_id', $licenseIds);
            })->get();
        });
    }
}
