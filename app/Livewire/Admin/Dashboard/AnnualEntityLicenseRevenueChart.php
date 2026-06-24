<?php

namespace App\Livewire\Admin\Dashboard;

use Domain\Licenses\Models\LicenseAttributed;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
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
        $cacheKey = 'admin_annual_entity_license_revenue_global_' . Carbon::now()->year . '_' . app()->getLocale();
        $cacheDuration = 60;

        return cache()->remember($cacheKey, $cacheDuration, function () {
            $currentYear = Carbon::now()->year;

            // Admin sees total of all licenses across all associations
            $monthlyRevenue = LicenseAttributed::select(
                DB::raw('SUM(total_value) as total_revenue'),
                DB::raw('MONTH(activated_at) as month')
            )
                ->where('model_type', 'entity')
                ->whereYear('activated_at', $currentYear)
                ->whereNotNull('total_value')
                ->whereNotNull('activated_at')
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
                        'label' => __('dashboard.revenue_eur'),
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
                    'label' => __('dashboard.revenue_eur'),
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
                        'text' => 'EUR',
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
