<?php

namespace App\Livewire\Admin\Dashboard;

use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

/**
 * Admin Dashboard widget showing individuals and entities overview.
 */
class AdminIndividualsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $federation = Federation::where('is_default_federation', true)->first();
        $federationId = $federation->id ?? null;

        // Cache TTL in seconds (1 hour)
        $ttl = 3600;

        $totalIndividualCount = 0;
        $totalEntityCount = 0;

        if ($federationId) {
            // Cache keys
            $totalIndividualCacheKey = "admin_total_individual_count_federation_{$federationId}";
            $totalEntityCacheKey = "admin_total_entity_count_federation_{$federationId}";

            // Get total individuals count (all in federation)
            $totalIndividualCount = Cache::remember($totalIndividualCacheKey, $ttl, function () use ($federationId) {
                return Individual::whereHas('federations', function ($query) use ($federationId) {
                    $query->where('federation_id', $federationId);
                })->count();
            });

            // Get total entities count (all in federation)
            $totalEntityCount = Cache::remember($totalEntityCacheKey, $ttl, function () use ($federationId) {
                return Entity::whereHas('federations', function ($query) use ($federationId) {
                    $query->where('federation_id', $federationId);
                })->count();
            });
        }

        return [
            Stat::make(__('dashboard.individual_members'), number_format($totalIndividualCount))
                ->description(__('dashboard.total_description'))
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->extraAttributes([
                    'class' => 'ring-2 ring-green-500/20',
                ]),

            Stat::make(__('dashboard.collective_entities'), number_format($totalEntityCount))
                ->description(__('dashboard.total_description'))
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info')
                ->extraAttributes([
                    'class' => 'ring-2 ring-blue-500/20',
                ]),
        ];
    }
}
