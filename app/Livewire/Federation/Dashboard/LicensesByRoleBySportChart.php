<?php

namespace App\Livewire\Federation\Dashboard;

use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class LicensesByRoleBySportChart extends ChartWidget
{
    protected static ?string $heading = null;

    public string $role = 'ATHLETE';

    protected $colors = [
        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
        '#FA8072', '#46BFBD', '#FDB45C', '#949FB1', '#4D5360',
    ];

    public function getHeading(): ?string
    {
        return __('dashboard.licenses_by_sport_heading', [
            'role' => __('dashboard.role_' . strtolower($this->role)),
            'year' => Carbon::now()->year,
        ]);
    }

    protected function getData(): array
    {
        $currentYear = Carbon::now()->year;

        $licensesData = LicenseAttributed::selectRaw('sports.name AS label, COUNT(*) AS value')
            ->join('license', 'license_attributed.license_id', '=', 'license.id')
            ->join('committee', 'license.committee_id', '=', 'committee.id')
            ->join('sports', 'license.sport_id', '=', 'sports.id')
            ->join('professional_roles', 'license.professional_role_id', '=', 'professional_roles.id')
            ->where('license_attributed.model_type', 'individual')
            ->where('license_attributed.status_class', ActiveLicenseAttributedState::class)
            ->where('committee.code', 'SPORT')
            ->where('professional_roles.role', $this->role)
            ->whereYear('license_attributed.created_at', $currentYear)
            ->groupBy('sports.name')
            ->orderByDesc('value')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('dashboard.active_licenses'),
                    'data' => $licensesData->pluck('value')->all(),
                    'backgroundColor' => $licensesData->map(function ($item, $index) {
                        return $this->colors[$index % count($this->colors)];
                    })->all(),
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $licensesData->pluck('label')->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => __('dashboard.count'),
                        'font' => [
                            'size' => 12,
                            'weight' => 'bold',
                        ],
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
                    'display' => false,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
