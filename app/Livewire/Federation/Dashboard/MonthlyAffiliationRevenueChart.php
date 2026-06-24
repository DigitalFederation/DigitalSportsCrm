<?php

namespace App\Livewire\Federation\Dashboard;

use Domain\Documents\Models\Document;
use Domain\Documents\States\PaidDocumentState;
use Domain\Memberships\Models\Affiliation;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MonthlyAffiliationRevenueChart extends ChartWidget
{
    protected static ?string $heading = null;

    protected static ?string $pollingInterval = null;

    public function getHeading(): ?string
    {
        return __('dashboard.monthly_affiliation_revenue');
    }

    protected function getData(): array
    {
        $user = Auth::user();
        $federationId = $user->federations()->first()->id ?? null;

        if (! $federationId) {
            return $this->getEmptyData();
        }

        $cacheKey = 'monthly_affiliation_revenue_' . $federationId . '_' . app()->getLocale();
        $cacheDuration = 60;

        return cache()->remember($cacheKey, $cacheDuration, function () use ($federationId) {
            $startOfYear = Carbon::now()->startOfYear();

            $monthlyRevenue = Document::select(
                DB::raw('SUM(document.total_value) as total_income'),
                DB::raw('MONTH(document.created_at) as month')
            )
                ->join('document_detail', 'document.id', '=', 'document_detail.document_id')
                ->where('document_detail.owner_type', Affiliation::class)
                ->whereIn('document_detail.owner_id', function ($query) use ($federationId) {
                    $query->select('id')
                        ->from('affiliations')
                        ->where('federation_id', $federationId);
                })
                ->where('document.status_class', PaidDocumentState::class)
                ->where('document.created_at', '>=', $startOfYear)
                ->groupBy(DB::raw('MONTH(document.created_at)'))
                ->get()
                ->keyBy('month');

            $data = [];
            $labels = [];

            for ($month = 1; $month <= 12; $month++) {
                $income = $monthlyRevenue->get($month)?->total_income ?? 0;
                $data[] = round($income, 2);
                $labels[] = Carbon::create()->month($month)->translatedFormat('F');
            }

            return [
                'datasets' => [
                    [
                        'label' => __('dashboard.revenue_eur'),
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
        return [
            'datasets' => [
                [
                    'label' => __('dashboard.revenue_eur'),
                    'data' => array_fill(0, 12, 0),
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
