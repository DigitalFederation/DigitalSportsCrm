<?php

namespace App\Livewire\Federation\Dashboard;

use Domain\Licenses\Models\LicenseAttributed;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnnualEntityLicenseRevenueChart extends ChartWidget
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

        // Check if user is admin or federation-admin (show all associations)
        $isGlobalView = $user->hasAnyRole(['admin', 'federation-admin']);

        // For modalidade associations, get their federation
        $federationId = null;
        if (! $isGlobalView) {
            $federationId = $user->federations()->first()->id ?? null;
            if (! $federationId) {
                return $this->getEmptyData();
            }
        }

        $cacheKey = 'annual_entity_license_revenue_' .
            ($isGlobalView ? 'global' : $federationId) . '_' .
            Carbon::now()->year . '_' . app()->getLocale();
        $cacheDuration = 60;

        return cache()->remember($cacheKey, $cacheDuration, function () use ($federationId, $isGlobalView) {
            $currentYear = Carbon::now()->year;

            $query = LicenseAttributed::select(
                DB::raw('SUM(total_value) as total_revenue'),
                DB::raw('MONTH(activated_at) as month')
            )
                ->where('model_type', 'entity')
                ->whereYear('activated_at', $currentYear)
                ->whereNotNull('total_value')
                ->whereNotNull('activated_at');

            // Filter by federation for modalidade associations
            if (! $isGlobalView && $federationId) {
                $query->where('federation_id', $federationId);
            }

            $monthlyRevenue = $query
                ->groupBy(DB::raw('MONTH(activated_at)'))
                ->get()
                ->keyBy('month');

            $data = [];
            $labels = [];

            for ($month = 1; $month <= 12; $month++) {
                $revenue = $monthlyRevenue->get($month)?->total_revenue ?? 0;
                $data[] = round((float) $revenue, 2);
                $labels[] = Carbon::create()->month($month)->translatedFormat('M');
            }

            return [
                'datasets' => [
                    [
                        'label' => __('dashboard.revenue_eur', ['currency' => currency_code()]),
                        'data' => $data,
                        'backgroundColor' => 'rgba(139, 92, 246, 0.2)',
                        'borderColor' => '#8b5cf6',
                        'borderWidth' => 2,
                        'fill' => true,
                        'pointBackgroundColor' => '#8b5cf6',
                        'pointBorderColor' => '#ffffff',
                        'pointBorderWidth' => 2,
                    ],
                ],
                'labels' => $labels,
            ];
        });
    }

    protected function getEmptyData(): array
    {
        $labels = [];
        for ($month = 1; $month <= 12; $month++) {
            $labels[] = Carbon::create()->month($month)->translatedFormat('M');
        }

        return [
            'datasets' => [
                [
                    'label' => __('dashboard.revenue_eur', ['currency' => currency_code()]),
                    'data' => array_fill(0, 12, 0),
                    'backgroundColor' => 'rgba(139, 92, 246, 0.2)',
                    'borderColor' => '#8b5cf6',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
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
                    'title' => [
                        'display' => true,
                        'text' => currency_code(),
                        'font' => [
                            'size' => 12,
                            'weight' => 'bold',
                        ],
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
            'elements' => [
                'line' => [
                    'tension' => 0.3,
                ],
                'point' => [
                    'radius' => 4,
                    'hoverRadius' => 6,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
