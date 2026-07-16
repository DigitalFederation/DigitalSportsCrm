<?php

namespace App\Livewire\Federation\Dashboard;

use Domain\Memberships\Models\Affiliation;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnnualIndividualAffiliationRevenueChart extends ChartWidget
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

        $cacheKey = 'annual_individual_affiliation_revenue_' . $federationId . '_' . Carbon::now()->year . '_' . app()->getLocale();
        $cacheDuration = 60;

        return cache()->remember($cacheKey, $cacheDuration, function () use ($federationId) {
            $currentYear = Carbon::now()->year;

            $monthlyRevenue = Affiliation::select(
                DB::raw('SUM(individual_fee) as total_revenue'),
                DB::raw('MONTH(created_at) as month')
            )
                ->where('federation_id', $federationId)
                ->where('member_type', 'individual')
                ->whereYear('created_at', $currentYear)
                ->whereNotNull('individual_fee')
                ->groupBy(DB::raw('MONTH(created_at)'))
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
                        'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                        'borderColor' => '#10b981',
                        'borderWidth' => 2,
                        'fill' => true,
                        'pointBackgroundColor' => '#10b981',
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
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    'borderColor' => '#10b981',
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
