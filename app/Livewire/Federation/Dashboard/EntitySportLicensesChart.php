<?php

namespace App\Livewire\Federation\Dashboard;

use App\Models\Sport;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EntitySportLicensesChart extends ChartWidget
{
    protected static ?string $heading = null;

    protected static ?string $pollingInterval = null;

    public function getHeading(): ?string
    {
        return null;
    }

    protected function getData(): array
    {
        $cacheKey = 'entity_sport_licenses_global_' . app()->getLocale();
        $cacheDuration = 60;

        return cache()->remember($cacheKey, $cacheDuration, function () {
            $activeStateClass = ActiveLicenseAttributedState::class;

            // Get all sports
            $allSports = Sport::orderBy('name')->get();

            // Get license counts per sport (global - all federations)
            $licenseCounts = LicenseAttributed::query()
                ->select([
                    'license.sport_id',
                    DB::raw('COUNT(*) as license_count'),
                ])
                ->join('license', 'license_attributed.license_id', '=', 'license.id')
                ->where('license_attributed.model_type', 'entity')
                ->where('license_attributed.status_class', $activeStateClass)
                ->whereNull('license_attributed.deleted_at')
                ->groupBy('license.sport_id')
                ->get()
                ->keyBy('sport_id');

            // Build data with all sports (translated names, 0 for missing)
            $chartData = $allSports->map(function ($sport) use ($licenseCounts) {
                $translationKey = 'sports.' . Str::slug($sport->name, '_');
                $translatedName = __($translationKey);

                return [
                    'name' => $translatedName === $translationKey ? $sport->name : $translatedName,
                    'count' => $licenseCounts->get($sport->id)?->license_count ?? 0,
                ];
            })->sortByDesc('count')->values();

            $labels = $chartData->pluck('name')->toArray();
            $data = $chartData->pluck('count')->toArray();

            $backgroundColors = [
                'rgba(139, 92, 246, 0.8)',
                'rgba(59, 130, 246, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(239, 68, 68, 0.8)',
                'rgba(236, 72, 153, 0.8)',
                'rgba(20, 184, 166, 0.8)',
                'rgba(249, 115, 22, 0.8)',
                'rgba(99, 102, 241, 0.8)',
                'rgba(34, 197, 94, 0.8)',
            ];

            return [
                'datasets' => [
                    [
                        'label' => __('dashboard.license_count'),
                        'data' => $data,
                        'backgroundColor' => array_slice($backgroundColors, 0, count($data)),
                        'borderColor' => 'rgba(255, 255, 255, 0.8)',
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
                    'label' => __('dashboard.license_count'),
                    'data' => [],
                    'backgroundColor' => [],
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
