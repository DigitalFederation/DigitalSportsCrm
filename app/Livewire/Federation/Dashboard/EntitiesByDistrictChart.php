<?php

namespace App\Livewire\Federation\Dashboard;

use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Geographic\Models\District;
use Domain\Memberships\States\ActiveAffiliationState;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class EntitiesByDistrictChart extends ChartWidget
{
    protected static ?string $heading = null;

    protected static ?string $pollingInterval = null;

    public function getHeading(): ?string
    {
        return null;
    }

    protected function getData(): array
    {
        $user = Auth::user();
        $federationId = $user->federations()->first()->id ?? null;

        if (! $federationId) {
            return $this->getEmptyData();
        }

        $cacheIdentifier = 'federation_entities_by_district_v2_' . $federationId . '_' . app()->getLocale();
        $cacheDuration = 60;

        return cache()->remember($cacheIdentifier, $cacheDuration, function () use ($federationId) {
            // Query for total entities (registered in federation) by district
            $totalByDistrict = District::query()
                ->select('districts.id', 'districts.name')
                ->selectRaw('COUNT(DISTINCT entity.id) as total_count')
                ->join('entity', 'entity.district_id', '=', 'districts.id')
                ->join('entity_federation', 'entity_federation.entity_id', '=', 'entity.id')
                ->where('entity_federation.federation_id', $federationId)
                ->where('entity_federation.status_class', ActiveEntityFederationState::class)
                ->whereNull('entity.deleted_at')
                ->groupBy('districts.id', 'districts.name')
                ->orderBy('districts.name')
                ->get()
                ->keyBy('id');

            // Query for active entities (with active validation plan affiliation) by district
            $activeByDistrict = District::query()
                ->select('districts.id')
                ->selectRaw('COUNT(DISTINCT entity.id) as active_count')
                ->join('entity', 'entity.district_id', '=', 'districts.id')
                ->join('affiliations', function ($join) {
                    $join->on('affiliations.member_id', '=', 'entity.id')
                        ->where('affiliations.member_type', '=', 'entity');
                })
                ->join('member_subscriptions', 'member_subscriptions.id', '=', 'affiliations.member_subscription_id')
                ->join('membership_packages', 'membership_packages.id', '=', 'member_subscriptions.membership_package_id')
                ->join('package_affiliation', 'package_affiliation.package_id', '=', 'membership_packages.id')
                ->join('affiliation_plans', 'affiliation_plans.id', '=', 'package_affiliation.affiliation_id')
                ->where('affiliations.federation_id', $federationId)
                ->where('affiliations.status_class', ActiveAffiliationState::class)
                ->where('affiliations.end_date', '>=', now()->toDateString())
                ->where('affiliation_plans.is_validation_plan', true)
                ->whereNull('entity.deleted_at')
                ->groupBy('districts.id')
                ->get()
                ->keyBy('id');

            $labels = [];
            $totalData = [];
            $activeData = [];

            foreach ($totalByDistrict as $districtId => $item) {
                $labels[] = $item->name;
                $totalData[] = $item->total_count;
                $activeData[] = $activeByDistrict[$districtId]->active_count ?? 0;
            }

            return [
                'datasets' => [
                    [
                        'label' => __('dashboard.total_registered'),
                        'data' => $totalData,
                        'backgroundColor' => 'rgba(156, 163, 175, 0.7)',
                        'borderColor' => 'rgba(156, 163, 175, 1)',
                        'borderWidth' => 1,
                    ],
                    [
                        'label' => __('dashboard.active_entities'),
                        'data' => $activeData,
                        'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                        'borderColor' => 'rgba(59, 130, 246, 1)',
                        'borderWidth' => 1,
                    ],
                ],
                'labels' => $labels,
            ];
        });
    }

    protected function getEmptyData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => __('dashboard.total_registered'),
                    'data' => [],
                ],
                [
                    'label' => __('dashboard.active_entities'),
                    'data' => [],
                ],
            ],
            'labels' => [],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(0, 0, 0, 0.1)',
                    ],
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
