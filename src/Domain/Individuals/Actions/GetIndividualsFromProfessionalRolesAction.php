<?php

namespace Domain\Individuals\Actions;

use Domain\Individuals\Models\Individual;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GetIndividualsFromProfessionalRolesAction
{
    /**
     * Retrieve individuals associated with the provided professional roles.
     *
     * @return Collection
     */
    public function __invoke(array $professionalRoleIds)
    {
        // Generate a cache key based on the license IDs
        $cacheKey = 'individuals_with_professional_roles:'.implode(',', $professionalRoleIds);

        // Get individuals that have licenses associated with a specific set of licenses.
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($professionalRoleIds) {
            // If not in cache, run the query and cache the results
            return Individual::whereHas('professionalRoles', function ($query) use ($professionalRoleIds) {
                $query->whereIn('professional_roles.id', $professionalRoleIds);
            })->get();
        });
    }
}
