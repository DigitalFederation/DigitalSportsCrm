<?php

namespace Domain\Licenses\Actions;

use Carbon\Carbon;
use Domain\Federations\Models\Federation;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\ExpiredLicenseAttributedState;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Domain\Licenses\States\SuspendedLicenseAttributedState;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LicenseAnalyticsAction
{
    /**
     * Get comprehensive license statistics for a federation.
     */
    public function getLicenseStatistics(?Federation $federation = null, array $filters = []): array
    {
        $query = LicenseAttributed::query();

        // Apply federation filter
        if ($federation) {
            $query->where('federation_id', $federation->id);
        }

        // Apply date range filter
        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return [
            'overview' => $this->getOverviewStats($query),
            'by_status' => $this->getStatusBreakdown($query),
            'by_type' => $this->getTypeBreakdown($query),
            'by_request_type' => $this->getRequestTypeBreakdown($query),
            'revenue' => $this->getRevenueStats($query),
            'trends' => $this->getTrendData($query, $filters),
            'expiration_alerts' => $this->getExpirationAlerts($query),
        ];
    }

    /**
     * Get overview statistics.
     */
    private function getOverviewStats($query): array
    {
        $baseQuery = clone $query;

        return [
            'total_licenses' => $baseQuery->count(),
            'active_licenses' => (clone $baseQuery)->where('status_class', ActiveLicenseAttributedState::class)->count(),
            'pending_licenses' => (clone $baseQuery)->where('status_class', PendingLicenseAttributedState::class)->count(),
            'suspended_licenses' => (clone $baseQuery)->where('status_class', SuspendedLicenseAttributedState::class)->count(),
            'expired_licenses' => (clone $baseQuery)->where('status_class', ExpiredLicenseAttributedState::class)->count(),
            'this_month' => (clone $baseQuery)->whereMonth('created_at', now()->month)->count(),
            'this_year' => (clone $baseQuery)->whereYear('created_at', now()->year)->count(),
        ];
    }

    /**
     * Get status breakdown with percentages.
     */
    private function getStatusBreakdown($query): array
    {
        $total = (clone $query)->count();

        if ($total === 0) {
            return [];
        }

        $statuses = (clone $query)
            ->select('status_class', DB::raw('count(*) as count'))
            ->groupBy('status_class')
            ->get()
            ->map(function ($item) use ($total) {
                return [
                    'status' => class_basename($item->status_class),
                    'count' => $item->count,
                    'percentage' => round(($item->count / $total) * 100, 2),
                ];
            });

        return $statuses->toArray();
    }

    /**
     * Get license type breakdown.
     */
    private function getTypeBreakdown($query): array
    {
        return (clone $query)
            ->join('license', 'license_attributed.license_id', '=', 'license.id')
            ->join('license_type', 'license.type_id', '=', 'license_type.id')
            ->select('license_type.name', DB::raw('count(*) as count'))
            ->groupBy('license_type.name')
            ->get()
            ->toArray();
    }

    /**
     * Get request type breakdown (direct vs entity_group).
     */
    private function getRequestTypeBreakdown($query): array
    {
        return (clone $query)
            ->select('request_type', DB::raw('count(*) as count'))
            ->groupBy('request_type')
            ->get()
            ->map(function ($item) {
                return [
                    'request_type' => $item->request_type ?: 'direct',
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Get revenue statistics.
     */
    private function getRevenueStats($query): array
    {
        $baseQuery = clone $query;

        return [
            'total_revenue' => (clone $baseQuery)->sum('total_value') ?: 0,
            'pending_revenue' => (clone $baseQuery)
                ->where('status_class', PendingLicenseAttributedState::class)
                ->sum('total_value') ?: 0,
            'confirmed_revenue' => (clone $baseQuery)
                ->where('status_class', ActiveLicenseAttributedState::class)
                ->sum('total_value') ?: 0,
            'this_month_revenue' => (clone $baseQuery)
                ->whereMonth('created_at', now()->month)
                ->sum('total_value') ?: 0,
            'this_year_revenue' => (clone $baseQuery)
                ->whereYear('created_at', now()->year)
                ->sum('total_value') ?: 0,
            'average_license_value' => (clone $baseQuery)->avg('total_value') ?: 0,
        ];
    }

    /**
     * Get trend data for charts.
     */
    private function getTrendData($query, array $filters): array
    {
        $period = $filters['trend_period'] ?? 'month';
        $dateFormat = $period === 'day' ? '%Y-%m-%d' : '%Y-%m';

        $trends = (clone $query)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as period"),
                DB::raw('count(*) as licenses_count'),
                DB::raw('sum(total_value) as revenue')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return $trends->toArray();
    }

    /**
     * Get expiration alerts.
     */
    private function getExpirationAlerts($query): array
    {
        $now = Carbon::now();

        return [
            'expiring_in_7_days' => (clone $query)
                ->where('status_class', ActiveLicenseAttributedState::class)
                ->whereBetween('date_expire', [$now, $now->copy()->addDays(7)])
                ->count(),
            'expiring_in_30_days' => (clone $query)
                ->where('status_class', ActiveLicenseAttributedState::class)
                ->whereBetween('date_expire', [$now, $now->copy()->addDays(30)])
                ->count(),
            'expired_last_30_days' => (clone $query)
                ->where('status_class', ExpiredLicenseAttributedState::class)
                ->whereBetween('date_expire', [$now->copy()->subDays(30), $now])
                ->count(),
        ];
    }

    /**
     * Get top performing licenses by revenue.
     */
    public function getTopPerformingLicenses(?Federation $federation = null, int $limit = 10): Collection
    {
        $query = LicenseAttributed::with(['license', 'license.type'])
            ->select('license_id', DB::raw('count(*) as count'), DB::raw('sum(total_value) as total_revenue'))
            ->groupBy('license_id')
            ->orderBy('total_revenue', 'desc')
            ->limit($limit);

        if ($federation) {
            $query->where('federation_id', $federation->id);
        }

        return $query->get();
    }

    /**
     * Get license holders with most licenses.
     */
    public function getTopLicenseHolders(?Federation $federation = null, int $limit = 10): Collection
    {
        $query = LicenseAttributed::select('model_type', 'model_id', 'holder_name', DB::raw('count(*) as license_count'))
            ->groupBy('model_type', 'model_id', 'holder_name')
            ->orderBy('license_count', 'desc')
            ->limit($limit);

        if ($federation) {
            $query->where('federation_id', $federation->id);
        }

        return $query->get();
    }

    /**
     * Get geographical distribution of licenses.
     */
    public function getGeographicalDistribution(?Federation $federation = null): Collection
    {
        $query = LicenseAttributed::join('federations', 'license_attributed.federation_id', '=', 'federations.id')
            ->join('countries', 'federations.country_id', '=', 'countries.id')
            ->select('countries.name as country', DB::raw('count(*) as license_count'))
            ->groupBy('countries.name')
            ->orderBy('license_count', 'desc');

        if ($federation) {
            $query->where('license_attributed.federation_id', $federation->id);
        }

        return $query->get();
    }

    /**
     * Export analytics data as array for further processing.
     */
    public function exportAnalyticsData(?Federation $federation = null, array $filters = []): array
    {
        return [
            'generated_at' => now()->toISOString(),
            'federation' => $federation ? $federation->name : 'All Federations',
            'filters' => $filters,
            'statistics' => $this->getLicenseStatistics($federation, $filters),
            'top_licenses' => $this->getTopPerformingLicenses($federation)->toArray(),
            'top_holders' => $this->getTopLicenseHolders($federation)->toArray(),
            'geographical' => $this->getGeographicalDistribution($federation)->toArray(),
        ];
    }
}
