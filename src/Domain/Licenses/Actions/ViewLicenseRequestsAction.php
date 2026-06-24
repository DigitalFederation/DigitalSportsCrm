<?php

namespace Domain\Licenses\Actions;

use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ViewLicenseRequestsAction
{
    /**
     * Filter and view license requests based on viewer role and permissions.
     *
     * @param  mixed  $viewer  The viewer (Federation, Entity, or Individual)
     * @param  array  $filters  Additional filters to apply
     * @param  int  $perPage  Number of items per page
     */
    public function __invoke($viewer, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = LicenseAttributed::query()->with([
            'license',
            'license.committee',
            'federation',
            'owner',
            'requestedBy',
        ]);

        // Apply role-based filtering
        if ($viewer instanceof Federation) {
            // Federations see all licenses in their federation
            $query->where('federation_id', $viewer->id);
        } elseif ($viewer instanceof Entity) {
            // Entities see their own licenses and member licenses they requested
            $query->where(function (Builder $q) use ($viewer) {
                // Entity's own licenses
                $q->where(function (Builder $sub) use ($viewer) {
                    $sub->where('model_type', 'entity')
                        ->where('model_id', $viewer->id);
                })
                // Licenses requested by this entity for members
                    ->orWhere('requested_by_id', $viewer->id);
            });
        } elseif ($viewer instanceof Individual) {
            // Individuals see only their own licenses
            $query->where('model_type', 'individual')
                ->where('model_id', $viewer->id);
        }

        // Apply additional filters
        $this->applyFilters($query, $filters);

        // Order by most recent first
        $query->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Apply filters to the query.
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        // Filter by status
        if (! empty($filters['status'])) {
            $query->licenseAttributedStatus($filters['status']);
        }

        // Filter by license type
        if (! empty($filters['license_type_id'])) {
            $query->whereHas('license', function (Builder $q) use ($filters) {
                $q->where('type_id', $filters['license_type_id']);
            });
        }

        // Filter by holder type
        if (! empty($filters['holder_type'])) {
            $query->holderType($filters['holder_type']);
        }

        // Filter by request type
        if (! empty($filters['request_type'])) {
            if ($filters['request_type'] === 'direct') {
                $query->directRequests();
            } elseif ($filters['request_type'] === 'entity_group') {
                $query->entityGroupRequests();
            }
        }

        // Filter by date range
        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        // Filter by expiration
        if (! empty($filters['expiring_soon'])) {
            $daysAhead = $filters['expiring_soon_days'] ?? 30;
            $query->where('date_expire', '>', now())
                ->where('date_expire', '<=', now()->addDays($daysAhead));
        }

        // Search by holder name
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->holderName($search)
                    ->orWhere('license_name', 'like', "%{$search}%")
                    ->orWhere('license_number', 'like', "%{$search}%");
            });
        }

        // Filter by sport
        if (! empty($filters['sport_id'])) {
            $query->sport($filters['sport_id']);
        }

        // Filter by professional role
        if (! empty($filters['professional_role_id'])) {
            $query->professionalRole($filters['professional_role_id']);
        }

        // Filter by payment status
        if (! empty($filters['payment_status'])) {
            if ($filters['payment_status'] === 'paid') {
                $query->whereNotNull('purchased_at');
            } elseif ($filters['payment_status'] === 'unpaid') {
                $query->whereNull('purchased_at');
            }
        }
    }

    /**
     * Get summary statistics for licenses.
     *
     * @param  mixed  $viewer
     */
    public function getSummaryStats($viewer, array $filters = []): array
    {
        $baseQuery = LicenseAttributed::query();

        // Apply same role-based filtering
        if ($viewer instanceof Federation) {
            $baseQuery->where('federation_id', $viewer->id);
        } elseif ($viewer instanceof Entity) {
            $baseQuery->where(function (Builder $q) use ($viewer) {
                $q->where(function (Builder $sub) use ($viewer) {
                    $sub->where('model_type', 'entity')
                        ->where('model_id', $viewer->id);
                })
                    ->orWhere('requested_by_id', $viewer->id);
            });
        } elseif ($viewer instanceof Individual) {
            $baseQuery->where('model_type', 'individual')
                ->where('model_id', $viewer->id);
        }

        return [
            'total' => $baseQuery->count(),
            'active' => (clone $baseQuery)->licenseAttributedStatus('active')->count(),
            'pending' => (clone $baseQuery)->licenseAttributedStatus('pending')->count(),
            'suspended' => (clone $baseQuery)->licenseAttributedStatus('suspended')->count(),
            'expired' => (clone $baseQuery)->licenseAttributedStatus('expired')->count(),
            'expiring_soon' => (clone $baseQuery)
                ->where('date_expire', '>', now())
                ->where('date_expire', '<=', now()->addDays(30))
                ->count(),
        ];
    }
}
