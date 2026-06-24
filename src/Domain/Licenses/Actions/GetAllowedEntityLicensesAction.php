<?php

namespace Domain\Licenses\Actions;

use Domain\Entities\Models\Entity;
use Domain\Licenses\Models\License;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class GetAllowedEntityLicensesAction
{
    /**
     * Get licenses available to an entity based on:
     * 1. Committee type
     * 2. License type (entity)
     * 3. Federation membership (NEW: only licenses offered by entity's federations)
     * 4. Not already attributed to the entity
     *
     * @param  string  $type  The committee type (e.g., 'sport').
     * @param  Entity  $entity  The entity for whom licenses are fetched.
     * @return \Illuminate\Database\Eloquent\Collection A collection of License models.
     */
    public function __invoke(string $type, Entity $entity)
    {
        $licensesCacheKey = "licenses_for_type_{$type}_entity_{$entity->id}";

        // Cache licenses for 5 minutes
        $licenses = Cache::remember($licensesCacheKey, 5, function () use ($type, $entity) {
            // Get all active federations the entity belongs to
            $federationIds = $entity->federations()
                ->where('entity_federation.status_class', 'Domain\\Entities\\States\\ActiveEntityFederationState')
                ->pluck('federation_id');

            return License::query()
                ->hasCommitteeCode($type)
                ->hasLicenseType('entity')
                ->forFederationEntities($federationIds) // Apply federation filtering
                ->whereDoesntHave('licensesAttributed', function (Builder $query) use ($entity) {
                    $query->where(['model_type' => 'entity', 'model_id' => $entity->id]);
                })
                ->orderBy('name')
                ->get();
        });

        return $licenses;
    }
}
