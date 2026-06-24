<?php

namespace Domain\Imports\Actions;

use App\Models\Country;
use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Geographic\Models\District;
use Illuminate\Support\Facades\Cache;

class ValidateBulkDataAction
{
    /**
     * Cache for country lookups to avoid repeated queries.
     */
    protected array $countryCache = [];

    /**
     * Cache for entity lookups to avoid repeated queries.
     */
    protected array $entityCache = [];

    /**
     * Cache for district lookups to avoid repeated queries.
     */
    protected array $districtCache = [];
    /**
     * Validate emails in bulk to check for existing users WITH linked individuals.
     * Users without individuals are allowed (they may be from failed import attempts).
     */
    public function validateEmails(array $emails): array
    {
        if (empty($emails)) {
            return [];
        }

        // Only reject emails where BOTH a user AND an individual exist
        // This allows re-importing users from failed attempts where the Individual wasn't created
        $existingEmails = User::whereIn('email', $emails)
            ->whereHas('individual')
            ->pluck('email')
            ->toArray();

        \Log::info('ValidateBulkDataAction::validateEmails', [
            'input_emails_count' => count($emails),
            'rejected_emails_count' => count($existingEmails),
            'sample_input_emails' => array_slice($emails, 0, 5),
            'rejected_emails' => array_slice($existingEmails, 0, 5),
        ]);

        return $existingEmails;
    }

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
            $countries = Country::where(function ($query) use ($uncachedCountries) {
                foreach ($uncachedCountries as $countryName) {
                    $query->orWhere('name', 'LIKE', '%' . $countryName . '%');
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
     * Validate and map entity member numbers to IDs with smart caching.
     *
     * @param  array  $memberNumbers  Array of entity member numbers to validate
     * @return array Map of member_number => entity_id (or null if not found)
     */
    public function validateEntities(array $memberNumbers): array
    {
        if (empty($memberNumbers)) {
            return [];
        }

        $entityMap = [];
        $uncachedMemberNumbers = [];

        // First check in-memory cache and Laravel cache for already resolved entities
        foreach ($memberNumbers as $memberNumber) {
            $normalizedNumber = trim($memberNumber);

            if (empty($normalizedNumber)) {
                continue;
            }

            if (isset($this->entityCache[$normalizedNumber])) {
                $entityMap[$normalizedNumber] = $this->entityCache[$normalizedNumber];
            } else {
                $cacheKey = 'entity_import_' . md5($normalizedNumber);
                $cachedId = Cache::get($cacheKey);

                if ($cachedId !== null) {
                    $this->entityCache[$normalizedNumber] = $cachedId;
                    $entityMap[$normalizedNumber] = $cachedId;
                } else {
                    $uncachedMemberNumbers[] = $normalizedNumber;
                }
            }
        }

        // Fetch uncached entities from database
        if (! empty($uncachedMemberNumbers)) {
            $entities = Entity::whereIn('member_number', $uncachedMemberNumbers)->get();

            foreach ($uncachedMemberNumbers as $memberNumber) {
                // Use non-strict comparison to match string from CSV with int from database
                $entity = $entities->first(fn ($e) => (string) $e->member_number === $memberNumber);

                if ($entity) {
                    $entityMap[$memberNumber] = $entity->id;
                    $this->entityCache[$memberNumber] = $entity->id;

                    // Cache for 24 hours
                    $cacheKey = 'entity_import_' . md5($memberNumber);
                    Cache::put($cacheKey, $entity->id, 60 * 60 * 24);
                }
            }
        }

        return $entityMap;
    }

    /**
     * Validate and map district names/IDs with smart caching.
     *
     * @param  array  $districtValues  Array of district names or IDs to validate
     * @return array Map of value => district_id (or null if not found)
     */
    public function validateDistricts(array $districtValues): array
    {
        if (empty($districtValues)) {
            return [];
        }

        $districtMap = [];
        $uncachedDistricts = [];

        // First check in-memory cache and Laravel cache for already resolved districts
        foreach ($districtValues as $districtValue) {
            $normalizedValue = trim((string) $districtValue);

            if (empty($normalizedValue)) {
                continue;
            }

            if (isset($this->districtCache[$normalizedValue])) {
                $districtMap[$normalizedValue] = $this->districtCache[$normalizedValue];
            } else {
                $cacheKey = 'district_import_' . md5(strtolower($normalizedValue));
                $cachedId = Cache::get($cacheKey);

                if ($cachedId !== null) {
                    $this->districtCache[$normalizedValue] = $cachedId;
                    $districtMap[$normalizedValue] = $cachedId;
                } else {
                    $uncachedDistricts[] = $normalizedValue;
                }
            }
        }

        // Fetch uncached districts from database
        if (! empty($uncachedDistricts)) {
            // For numeric values, check by ID; for non-numeric, search by name
            $numericIds = array_filter($uncachedDistricts, 'is_numeric');
            $nameValues = array_filter($uncachedDistricts, fn ($v) => ! is_numeric($v));

            $districts = collect();

            // Query by IDs
            if (! empty($numericIds)) {
                $byId = District::whereIn('id', array_map('intval', $numericIds))
                    ->where('is_active', true)
                    ->get();
                $districts = $districts->merge($byId);
            }

            // Query by names
            if (! empty($nameValues)) {
                $byName = District::where('is_active', true)
                    ->where(function ($query) use ($nameValues) {
                        foreach ($nameValues as $name) {
                            $escapedName = str_replace(['%', '_'], ['\\%', '\\_'], $name);
                            $query->orWhere('name', 'LIKE', '%' . $escapedName . '%');
                        }
                    })
                    ->get();
                $districts = $districts->merge($byName);
            }

            foreach ($uncachedDistricts as $districtValue) {
                $district = null;

                if (is_numeric($districtValue)) {
                    // For numeric values, find exact ID match
                    $district = $districts->first(fn ($d) => $d->id === (int) $districtValue);
                } else {
                    // For names, try exact match first
                    $district = $districts->first(fn ($d) => strcasecmp($d->name, $districtValue) === 0);

                    // If no exact match, try partial match
                    if (! $district) {
                        $district = $districts->first(fn ($d) => stripos($d->name, $districtValue) !== false);
                    }
                }

                if ($district) {
                    $districtMap[$districtValue] = $district->id;
                    $this->districtCache[$districtValue] = $district->id;

                    // Cache for 24 hours
                    $cacheKey = 'district_import_' . md5(strtolower($districtValue));
                    Cache::put($cacheKey, $district->id, 60 * 60 * 24);
                }
            }
        }

        return $districtMap;
    }

    /**
     * Validate all data for a chunk of individuals.
     */
    public function execute(array $individuals): array
    {
        $emails = array_column($individuals, 'email');

        // Extract countries safely, handling both 'country' and 'country_id' keys
        $countries = [];
        foreach ($individuals as $individual) {
            // Check if we have a country name that needs to be resolved
            if (isset($individual['country']) && ! empty($individual['country']) && ! is_numeric($individual['country'])) {
                $countries[] = $individual['country'];
            }
        }
        $countries = array_unique($countries);

        // Extract entity member numbers that need to be resolved
        $entityMemberNumbers = [];
        foreach ($individuals as $individual) {
            if (isset($individual['entity_member_number']) && ! empty($individual['entity_member_number'])) {
                $entityMemberNumbers[] = trim($individual['entity_member_number']);
            }
        }
        $entityMemberNumbers = array_unique($entityMemberNumbers);

        // Extract district values that need to be resolved (could be names or IDs)
        $districtValues = [];
        foreach ($individuals as $individual) {
            // Check for district name in 'district' key
            if (isset($individual['district']) && ! empty($individual['district'])) {
                $districtValues[] = trim((string) $individual['district']);
            }
            // Also check district_id for non-numeric values that need resolution
            if (isset($individual['district_id']) && ! empty($individual['district_id']) && ! is_numeric($individual['district_id'])) {
                $districtValues[] = trim((string) $individual['district_id']);
            }
        }
        $districtValues = array_unique($districtValues);

        $existingEmails = $this->validateEmails($emails);
        $countryMap = $this->validateCountries($countries);
        $entityMap = $this->validateEntities($entityMemberNumbers);
        $districtMap = $this->validateDistricts($districtValues);

        $validationResults = [
            'valid' => [],
            'errors' => [],
            'warnings' => [],
        ];

        foreach ($individuals as $index => $individual) {
            $errors = [];
            $warnings = [];

            // Check for existing email
            $email = $individual['email'] ?? null;
            if ($email && in_array($email, $existingEmails)) {
                $errors[] = __('validation.email_already_exists', ['email' => $email]);
            }

            // Validate country - optional (auto-set from Main Federation if not provided)
            if (isset($individual['country']) && ! empty($individual['country']) && ! is_numeric($individual['country'])) {
                // If country name is provided, validate it exists
                if (! isset($countryMap[$individual['country']])) {
                    $warnings[] = __('validation.country_not_found', ['country' => $individual['country']]);
                }
            } elseif (isset($individual['country_id']) && ! empty($individual['country_id']) && ! is_numeric($individual['country_id'])) {
                $warnings[] = __('validation.country_id_numeric');
            }

            // Validate required fields
            if (empty($individual['name'])) {
                $errors[] = __('validation.field_required', ['field' => __('common.name')]);
            }
            if (empty($individual['surname'])) {
                $errors[] = __('validation.field_required', ['field' => __('common.surname')]);
            }
            if (empty($individual['email'])) {
                $errors[] = __('validation.field_required', ['field' => __('common.email')]);
            }
            if (empty($individual['birthdate'])) {
                $errors[] = __('validation.field_required', ['field' => __('common.birthdate')]);
            } else {
                // Parse and validate birthdate
                $parsedDate = $this->parseDate($individual['birthdate']);
                if ($parsedDate === null) {
                    $errors[] = __('validation.invalid_date_format', ['field' => __('common.birthdate'), 'value' => $individual['birthdate']]);
                } else {
                    $individual['birthdate'] = $parsedDate;
                }
            }

            // Add country_id to individual data if we have a country name
            if (isset($individual['country']) && ! empty($individual['country']) && ! is_numeric($individual['country'])) {
                if (isset($countryMap[$individual['country']])) {
                    $individual['country_id'] = $countryMap[$individual['country']];
                    unset($individual['country']); // Remove country name after conversion
                }
            }

            // Validate entity_member_number and resolve to entity_id
            if (isset($individual['entity_member_number']) && ! empty($individual['entity_member_number'])) {
                $memberNumber = trim($individual['entity_member_number']);
                if (isset($entityMap[$memberNumber])) {
                    $individual['entity_id'] = $entityMap[$memberNumber];
                } else {
                    $errors[] = __('validation.entity_not_found', ['member_number' => $memberNumber]);
                }
                unset($individual['entity_member_number']); // Remove after processing
            }

            // Resolve district name to district_id
            if (isset($individual['district']) && ! empty($individual['district'])) {
                $districtValue = trim((string) $individual['district']);
                if (isset($districtMap[$districtValue])) {
                    $individual['district_id'] = $districtMap[$districtValue];
                } else {
                    $warnings[] = __('validation.district_not_found', ['district' => $districtValue]);
                }
                unset($individual['district']); // Remove after processing
            }

            // Resolve non-numeric district_id values (e.g., district names passed as district_id)
            if (isset($individual['district_id']) && ! empty($individual['district_id']) && ! is_numeric($individual['district_id'])) {
                $districtValue = trim((string) $individual['district_id']);
                if (isset($districtMap[$districtValue])) {
                    $individual['district_id'] = $districtMap[$districtValue];
                } else {
                    $warnings[] = __('validation.district_not_found', ['district' => $districtValue]);
                    unset($individual['district_id']); // Remove invalid value
                }
            }

            if (empty($errors)) {
                $validationResults['valid'][$index] = $individual;
            } else {
                $validationResults['errors'][$index] = $errors;
            }

            if (! empty($warnings)) {
                $validationResults['warnings'][$index] = $warnings;
            }
        }

        return $validationResults;
    }

    /**
     * Parse date from various formats to Y-m-d.
     * Supports: DD/MM/YYYY, DD-MM-YYYY, YYYY-MM-DD, YYYY/MM/DD
     */
    protected function parseDate(string $date): ?string
    {
        $date = trim($date);

        if (empty($date)) {
            return null;
        }

        // Already in Y-m-d format
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $parsed = \DateTime::createFromFormat('Y-m-d', $date);
            if ($parsed && $parsed->format('Y-m-d') === $date) {
                return $date;
            }
        }

        // DD/MM/YYYY format
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
            $parsed = \DateTime::createFromFormat('d/m/Y', $date);
            if ($parsed) {
                return $parsed->format('Y-m-d');
            }
        }

        // DD-MM-YYYY format
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
            $parsed = \DateTime::createFromFormat('d-m-Y', $date);
            if ($parsed) {
                return $parsed->format('Y-m-d');
            }
        }

        // YYYY/MM/DD format
        if (preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $date)) {
            $parsed = \DateTime::createFromFormat('Y/m/d', $date);
            if ($parsed) {
                return $parsed->format('Y-m-d');
            }
        }

        // Try Carbon as fallback for other formats
        try {
            $carbon = \Carbon\Carbon::parse($date);

            return $carbon->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
