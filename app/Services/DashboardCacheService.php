<?php

namespace App\Services;

use Domain\Federations\Models\Federation;
use Illuminate\Support\Facades\Cache;

class DashboardCacheService
{
    /**
     * Invalidate all payment-related dashboard caches.
     */
    public function invalidatePaymentCaches(?int $year = null): void
    {
        $year = $year ?? now()->year;
        $federation = $this->getDefaultFederation();

        Cache::forget("admin_monthly_payments_{$year}");

        if ($federation) {
            $fed = $federation->id;
            Cache::forget("entity_billing_total_{$fed}_{$year}");

            foreach ($this->getLocales() as $locale) {
                Cache::forget("admin_annual_entity_affiliation_revenue_{$fed}_{$year}_{$locale}");
                Cache::forget("admin_annual_individual_affiliation_revenue_{$fed}_{$year}_{$locale}");
            }
        }

        foreach ($this->getLocales() as $locale) {
            Cache::forget("admin_annual_entity_license_revenue_global_{$year}_{$locale}");
            Cache::forget("admin_annual_individual_license_revenue_global_{$year}_{$locale}");
        }
    }

    /**
     * Invalidate all activation-related dashboard caches (affiliations, licenses, districts).
     */
    public function invalidateActivationCaches(): void
    {
        $federation = $this->getDefaultFederation();

        if ($federation) {
            $fed = $federation->id;
            $today = now()->format('Y-m-d');

            Cache::forget("admin_members_distribution_{$fed}_{$today}");

            foreach ($this->getLocales() as $locale) {
                Cache::forget("admin_entities_by_district_v2_{$fed}_{$locale}");
                Cache::forget("admin_individuals_by_district_v2_{$fed}_{$locale}");
            }
        }

        foreach ($this->getLocales() as $locale) {
            Cache::forget("admin_entity_sport_licenses_global_{$locale}");
            Cache::forget("admin_individual_sport_licenses_by_role_global_{$locale}");
        }
    }

    /**
     * Invalidate all dashboard caches (payment + activation).
     */
    public function invalidateAll(?int $year = null): void
    {
        $this->invalidatePaymentCaches($year);
        $this->invalidateActivationCaches();
    }

    /**
     * @return array<string>
     */
    private function getLocales(): array
    {
        return config('app.locales', ['en', 'pt']);
    }

    private function getDefaultFederation(): ?Federation
    {
        return Federation::where('is_default_federation', true)->first();
    }
}
