<?php

namespace App\Livewire\Admin\Dashboard;

use Domain\Licenses\Models\LicenseAttributed;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnnualIndividualLicenseRevenueChart extends ChartWidget
{
    protected static ?string $heading = null;

    protected static ?string $pollingInterval = null;

    public function getHeading(): ?string
    {
        return null;
    }

    protected function getData(): array
    {
        $cacheKey = 'admin_annual_individual_license_revenue_global_' . Carbon::now()->year . '_' . app()->getLocale();
        $cacheDuration = 60;

        return cache()->remember($cacheKey, $cacheDuration, function () {
            $currentYear = Carbon::now()->year;

            // Admin sees total of all licenses across all associations
            $monthlyRevenue = LicenseAttributed::select(
                DB::raw('SUM(total_value) as total_revenue'),
                DB::raw('MONTH(activated_at) as month')
            )
                ->where('model_type', 'individual')
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
                        'label' => __('dashboard.revenue_eur', ['currency' => currency_code()]),
                        'data' => $data,
                        'backgroundColor' => 'rgba(236, 72, 153, 0.2)',
                        'borderColor' => '#ec4899',
                        'borderWidth' => 2,
                        'fill' => true,
                        'pointBackgroundColor' => '#ec4899',
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
                    'backgroundColor' => 'rgba(236, 72, 153, 0.2)',
                    'borderColor' => '#ec4899',
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
