<?php

use App\Services\DashboardCacheService;
use Domain\Federations\Models\Federation;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->federation = Federation::factory()->create([
        'is_default_federation' => true,
    ]);

    $this->service = app(DashboardCacheService::class);
});

it('invalidates all payment-related caches', function () {
    $year = now()->year;
    $fed = $this->federation->id;
    $locales = config('app.locales', ['en', 'pt']);

    $keys = [
        "admin_monthly_payments_{$year}",
        "entity_billing_total_{$fed}_{$year}",
    ];

    foreach ($locales as $locale) {
        $keys[] = "admin_annual_entity_affiliation_revenue_{$fed}_{$year}_{$locale}";
        $keys[] = "admin_annual_individual_affiliation_revenue_{$fed}_{$year}_{$locale}";
        $keys[] = "admin_annual_entity_license_revenue_global_{$year}_{$locale}";
        $keys[] = "admin_annual_individual_license_revenue_global_{$year}_{$locale}";
    }

    foreach ($keys as $key) {
        Cache::put($key, 'test-data', 3600);
    }

    $this->service->invalidatePaymentCaches();

    foreach ($keys as $key) {
        expect(Cache::has($key))->toBeFalse("Expected cache key [{$key}] to be cleared");
    }
});

it('invalidates all activation-related caches', function () {
    $fed = $this->federation->id;
    $today = now()->format('Y-m-d');
    $locales = config('app.locales', ['en', 'pt']);

    $keys = [
        "admin_members_distribution_{$fed}_{$today}",
    ];

    foreach ($locales as $locale) {
        $keys[] = "admin_entities_by_district_v2_{$fed}_{$locale}";
        $keys[] = "admin_individuals_by_district_v2_{$fed}_{$locale}";
        $keys[] = "admin_entity_sport_licenses_global_{$locale}";
        $keys[] = "admin_individual_sport_licenses_by_role_global_{$locale}";
    }

    foreach ($keys as $key) {
        Cache::put($key, 'test-data', 3600);
    }

    $this->service->invalidateActivationCaches();

    foreach ($keys as $key) {
        expect(Cache::has($key))->toBeFalse("Expected cache key [{$key}] to be cleared");
    }
});

it('invalidates all caches when calling invalidateAll', function () {
    $year = now()->year;
    $fed = $this->federation->id;
    $today = now()->format('Y-m-d');

    $paymentKey = "admin_monthly_payments_{$year}";
    $activationKey = "admin_members_distribution_{$fed}_{$today}";

    Cache::put($paymentKey, 'payment-data', 3600);
    Cache::put($activationKey, 'activation-data', 3600);

    $this->service->invalidateAll();

    expect(Cache::has($paymentKey))->toBeFalse()
        ->and(Cache::has($activationKey))->toBeFalse();
});

it('respects the year parameter for payment caches', function () {
    $targetYear = 2023;
    $otherYear = now()->year;

    Cache::put("admin_monthly_payments_{$targetYear}", 'data-2023', 3600);
    Cache::put("admin_monthly_payments_{$otherYear}", 'data-current', 3600);

    $this->service->invalidatePaymentCaches($targetYear);

    expect(Cache::has("admin_monthly_payments_{$targetYear}"))->toBeFalse()
        ->and(Cache::has("admin_monthly_payments_{$otherYear}"))->toBeTrue();
});

it('does not throw when no default federation exists', function () {
    Federation::where('is_default_federation', true)->update(['is_default_federation' => false]);

    $this->service->invalidatePaymentCaches();
    $this->service->invalidateActivationCaches();
    $this->service->invalidateAll();

    expect(true)->toBeTrue();
});
