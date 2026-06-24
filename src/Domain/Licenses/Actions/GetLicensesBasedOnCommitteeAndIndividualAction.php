<?php

namespace Domain\Licenses\Actions;

use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Retrieves licenses based on the committee type, individual's certifications,
 * and federation memberships.
 *
 * This action class is responsible for querying the License model with several
 * conditions: it filters licenses by the committee code, checks if the license
 * type is for individuals, ensures the individual has active attributed
 * certifications, and filters out licenses that are already attributed to the
 * individual. Additionally, for 'sport' committee type, it includes licenses
 * related to the 'ATHLETE' professional role. It also considers the licenses
 * available through the individual's federation memberships.
 */
class GetLicensesBasedOnCommitteeAndIndividualAction
{
    /**
     * Invokes the action to get licenses based on committee type and individual.
     *
     * @param  string  $type  The committee type (e.g., 'sport').
     * @param  Individual  $individual  The individual for whom licenses are fetched.
     * @param  Collection|int[]  $federationIds  Collection of federation IDs to filter licenses.
     * @return \Illuminate\Database\Eloquent\Collection A collection of License models.
     */
    public function __invoke(string $type, Individual $individual, array|Collection $federationIds)
    {
        // Convert Collection to array if needed
        $federationIdsArray = is_array($federationIds) ? $federationIds : $federationIds->toArray();

        // Create a cache key that includes federation IDs for proper caching
        sort($federationIdsArray);
        $federationIdsString = implode(',', $federationIdsArray);
        $licensesCacheKey = "licenses_for_type_{$type}_{$individual->id}_federations_{$federationIdsString}";

        // Cache licenses
        $licenses = Cache::remember($licensesCacheKey, 60, function () use ($type, $federationIdsArray) {

            // Get all licenses that are either:
            // 1. From the specified committee type OR
            // 2. Have requester_model set to Individual
            // AND belong to the individual's federations
            $licenseQuery = License::query()
                ->whereHas('type', function (Builder $query) {
                    $query->where('is_individual', 1);
                })
                ->where(function (Builder $query) use ($type) {
                    $query->whereHas('committee', function (Builder $subQuery) use ($type) {
                        $subQuery->where('code', $type);
                    })
                        ->orWhereJsonContains('requester_model', 'Individual');
                })
                ->forFederationEntities($federationIdsArray); // Filter by federation permissions

            return $licenseQuery->with(['committee', 'professionalRole', 'sport'])->orderBy('name')->get();

        });

        return $licenses;
    }

}
