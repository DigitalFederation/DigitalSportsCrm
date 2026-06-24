<?php

namespace Domain\Individuals\Actions;

use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class GetIndividualsFromCertificationsAction
{
    public function __invoke(array $certificationIds)
    {
        // Generate a cache key based on the certification IDs
        $cacheKey = 'individuals_with_certifications:'.implode(',', $certificationIds);

        // Get individuals who have been attributed with specific certifications.
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($certificationIds) {
            // If not in cache, run the query and cache the results
            return Individual::whereHas('certificationsAttributed', function (Builder $certificationQuery) use ($certificationIds) {
                $certificationQuery->whereIn('certification_id', $certificationIds);
            })->get();
        });
    }
}
