<?php

namespace App\Support;

use App\Models\Country;
use RuntimeException;

class DefaultCountryResolver
{
    public function resolve(?string $countryCode = null): Country
    {
        $configuredCode = $countryCode ?? config('app.default_country_code');

        if ($configuredCode === null || trim((string) $configuredCode) === '') {
            $legacyCountryId = config('app.legacy_default_country_id');

            if ($legacyCountryId !== null && $legacyCountryId !== '') {
                $country = Country::query()->find($legacyCountryId);

                if (! $country) {
                    throw new RuntimeException(
                        "The legacy default country ID [{$legacyCountryId}] does not identify a country."
                    );
                }

                return $country;
            }

            $configuredCode = 'PT';
        }

        $normalizedCode = strtoupper(trim((string) $configuredCode));

        if ($normalizedCode === '') {
            throw new RuntimeException('The default country code is not configured.');
        }

        $countries = Country::query()
            ->where('iso', $normalizedCode)
            ->limit(2)
            ->get();

        if ($countries->count() !== 1) {
            throw new RuntimeException(
                "Expected exactly one country with ISO code [{$normalizedCode}], found {$countries->count()}."
            );
        }

        return $countries->first();
    }
}
