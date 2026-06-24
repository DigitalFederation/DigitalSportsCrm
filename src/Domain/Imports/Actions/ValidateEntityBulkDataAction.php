<?php

namespace Domain\Imports\Actions;

use App\Models\Country;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Geographic\Models\District;
use Illuminate\Support\Facades\Cache;

class ValidateEntityBulkDataAction
{
    /**
     * Cache for country lookups to avoid repeated queries.
     */
    protected array $countryCache = [];

    /**
     * Cache for district lookups to avoid repeated queries.
     */
    protected array $districtCache = [];

    /**
     * Cache for federation lookups to avoid repeated queries.
     */
    protected array $federationCache = [];

    /**
     * Validate and map country names to IDs with smart caching.
     */
    public function validateCountries(array $countryNames): array
    {
        if (empty($countryNames)) {
            return [];
        }

        $countryMap = [];
        $uncachedCountries = [];

        // First check in-memory cache and Laravel cache for already resolved countries
        foreach ($countryNames as $countryName) {
            $normalizedName = trim($countryName);

            if (isset($this->countryCache[$normalizedName])) {
                // Use in-memory cache first (fastest)
                $countryMap[$normalizedName] = $this->countryCache[$normalizedName];
            } else {
                // Check Laravel cache
                $cacheKey = 'country_import_' . md5(strtolower($normalizedName));
                $cachedId = Cache::get($cacheKey);

                if ($cachedId !== null) {
                    $this->countryCache[$normalizedName] = $cachedId;
                    $countryMap[$normalizedName] = $cachedId;
                } else {
                    $uncachedCountries[] = $normalizedName;
                }
            }
        }

        // Fetch uncached countries from database
        if (! empty($uncachedCountries)) {
            // Query database for all uncached countries at once
            // Escape LIKE wildcards to prevent SQL injection
            $countries = Country::where(function ($query) use ($uncachedCountries) {
                foreach ($uncachedCountries as $countryName) {
                    $escapedName = str_replace(['%', '_'], ['\\%', '\\_'], $countryName);
                    $query->orWhere('name', 'LIKE', '%' . $escapedName . '%');
                }
            })->get();

            // Map results
            foreach ($uncachedCountries as $countryName) {
                // First try exact match
                $country = $countries->first(function ($c) use ($countryName) {
                    return strcasecmp($c->name, $countryName) === 0;
                });

                // If no exact match, try partial match
                if (! $country) {
                    $country = $countries->first(function ($c) use ($countryName) {
                        return stripos($c->name, $countryName) !== false;
                    });
                }

                if ($country) {
                    $countryMap[$countryName] = $country->id;
                    $this->countryCache[$countryName] = $country->id;

                    // Cache for 24 hours
                    $cacheKey = 'country_import_' . md5(strtolower($countryName));
                    Cache::put($cacheKey, $country->id, 60 * 60 * 24);
                }
            }
        }

        return $countryMap;
    }

    /**
     * Validate and map district names to IDs with smart caching.
     */
    public function validateDistricts(array $districtNames): array
    {
        if (empty($districtNames)) {
            return [];
        }

        $districtMap = [];
        $uncachedDistricts = [];

        // First check in-memory cache and Laravel cache for already resolved districts
        foreach ($districtNames as $districtName) {
            $normalizedName = trim($districtName);

            if (empty($normalizedName)) {
                continue;
            }

            if (isset($this->districtCache[$normalizedName])) {
                $districtMap[$normalizedName] = $this->districtCache[$normalizedName];
            } else {
                $cacheKey = 'district_import_' . md5(strtolower($normalizedName));
                $cachedId = Cache::get($cacheKey);

                if ($cachedId !== null) {
                    $this->districtCache[$normalizedName] = $cachedId;
                    $districtMap[$normalizedName] = $cachedId;
                } else {
                    $uncachedDistricts[] = $normalizedName;
                }
            }
        }

        // Fetch uncached districts from database
        if (! empty($uncachedDistricts)) {
            // Escape LIKE wildcards to prevent SQL injection
            $districts = District::where(function ($query) use ($uncachedDistricts) {
                foreach ($uncachedDistricts as $districtName) {
                    $escapedName = str_replace(['%', '_'], ['\\%', '\\_'], $districtName);
                    $query->orWhere('name', 'LIKE', '%' . $escapedName . '%');
                }
            })->get();

            foreach ($uncachedDistricts as $districtName) {
                // First try exact match
                $district = $districts->first(function ($d) use ($districtName) {
                    return strcasecmp($d->name, $districtName) === 0;
                });

                // If no exact match, try partial match
                if (! $district) {
                    $district = $districts->first(function ($d) use ($districtName) {
                        return stripos($d->name, $districtName) !== false;
                    });
                }

                if ($district) {
                    $districtMap[$districtName] = $district->id;
                    $this->districtCache[$districtName] = $district->id;

                    // Cache for 24 hours
                    $cacheKey = 'district_import_' . md5(strtolower($districtName));
                    Cache::put($cacheKey, $district->id, 60 * 60 * 24);
                }
            }
        }

        return $districtMap;
    }

    /**
     * Validate member numbers against existing entities.
     */
    public function validateMemberNumbers(array $memberNumbers): array
    {
        if (empty($memberNumbers)) {
            return [];
        }

        $memberNumbers = array_filter($memberNumbers, fn ($n) => ! empty($n));

        if (empty($memberNumbers)) {
            return [];
        }

        return Entity::whereIn('member_number', $memberNumbers)
            ->pluck('member_number')
            ->toArray();
    }

    /**
     * Validate federation IDs exist.
     */
    public function validateFederations(array $federationIds): array
    {
        if (empty($federationIds)) {
            return [];
        }

        $federationIds = array_filter($federationIds, fn ($id) => ! empty($id) && is_numeric($id));

        if (empty($federationIds)) {
            return [];
        }

        return Federation::whereIn('id', $federationIds)
            ->pluck('id')
            ->toArray();
    }

    /**
     * Validate all data for a chunk of entities.
     */
    public function execute(array $entities): array
    {
        // Extract countries safely, handling both 'country' and 'country_id' keys
        $countries = [];
        foreach ($entities as $entity) {
            // Check if we have a country name that needs to be resolved
            if (isset($entity['country']) && ! empty($entity['country']) && ! is_numeric($entity['country'])) {
                $countries[] = $entity['country'];
            }
        }
        $countries = array_unique($countries);

        // Extract districts that need to be resolved
        $districts = [];
        foreach ($entities as $entity) {
            if (isset($entity['district']) && ! empty($entity['district']) && ! is_numeric($entity['district'])) {
                $districts[] = $entity['district'];
            }
        }
        $districts = array_unique($districts);

        // Extract member numbers to check for duplicates
        $memberNumbers = [];
        foreach ($entities as $entity) {
            if (isset($entity['member_number']) && ! empty($entity['member_number'])) {
                $memberNumbers[] = $entity['member_number'];
            }
        }
        $memberNumbers = array_unique($memberNumbers);

        // Extract federation IDs to validate
        $federationIds = [];
        foreach ($entities as $entity) {
            if (isset($entity['federation_id']) && ! empty($entity['federation_id']) && is_numeric($entity['federation_id'])) {
                $federationIds[] = $entity['federation_id'];
            }
        }
        $federationIds = array_unique($federationIds);

        $countryMap = $this->validateCountries($countries);
        $districtMap = $this->validateDistricts($districts);
        $existingMemberNumbers = $this->validateMemberNumbers($memberNumbers);
        $validFederationIds = $this->validateFederations($federationIds);

        $validationResults = [
            'valid' => [],
            'errors' => [],
            'warnings' => [],
        ];

        foreach ($entities as $index => $entity) {
            $errors = [];
            $warnings = [];

            // Validate required fields (country_id is auto-set from Main Federation)
            if (empty($entity['name'])) {
                $errors[] = __('validation.field_required', ['field' => __('common.name')]);
            }

            // Validate district if provided
            if (isset($entity['district']) && ! empty($entity['district']) && ! is_numeric($entity['district'])) {
                if (! isset($districtMap[$entity['district']])) {
                    $warnings[] = __('validation.district_not_found', ['district' => $entity['district']]);
                }
            }

            // Validate member number uniqueness
            if (isset($entity['member_number']) && ! empty($entity['member_number'])) {
                if (in_array($entity['member_number'], $existingMemberNumbers)) {
                    $errors[] = __('validation.member_number_exists', ['number' => $entity['member_number']]);
                }
            }

            // Validate federation ID if provided
            if (isset($entity['federation_id']) && ! empty($entity['federation_id']) && is_numeric($entity['federation_id'])) {
                if (! in_array((int) $entity['federation_id'], $validFederationIds)) {
                    $errors[] = __('validation.federation_not_found', ['id' => $entity['federation_id']]);
                }
            }

            // Email validation
            if (! empty($entity['email']) && ! filter_var($entity['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = __('validation.invalid_email', ['email' => $entity['email']]);
            }

            // URL validations
            $urlFields = ['website', 'facebook_url', 'x_url', 'instagram_url', 'linkedin_url'];
            foreach ($urlFields as $field) {
                if (! empty($entity[$field]) && ! filter_var($entity[$field], FILTER_VALIDATE_URL)) {
                    $warnings[] = __('validation.invalid_url', ['field' => $field, 'url' => $entity[$field]]);
                }
            }

            // Add country_id to entity data if we have a country name
            if (isset($entity['country']) && ! empty($entity['country']) && ! is_numeric($entity['country'])) {
                if (isset($countryMap[$entity['country']])) {
                    $entity['country_id'] = $countryMap[$entity['country']];
                    unset($entity['country']); // Remove country name after conversion
                }
            }

            // Add district_id to entity data if we have a district name
            if (isset($entity['district']) && ! empty($entity['district']) && ! is_numeric($entity['district'])) {
                if (isset($districtMap[$entity['district']])) {
                    $entity['district_id'] = $districtMap[$entity['district']];
                    unset($entity['district']); // Remove district name after conversion
                }
            }

            if (empty($errors)) {
                $validationResults['valid'][$index] = $entity;
            } else {
                $validationResults['errors'][$index] = $errors;
            }

            if (! empty($warnings)) {
                $validationResults['warnings'][$index] = $warnings;
            }
        }

        return $validationResults;
    }
}
