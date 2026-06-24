<?php

namespace App\Livewire\Federation\Dashboard;

use App\Models\Sport;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IndividualSportLicensesByRoleChart extends ChartWidget
{
    protected static ?string $heading = null;

    protected static ?string $pollingInterval = null;

    public function getHeading(): ?string
    {
        return null;
    }

    protected function getData(): array
    {
        $cacheKey = 'individual_sport_licenses_by_role_global_' . app()->getLocale();
        $cacheDuration = 60;

        return cache()->remember($cacheKey, $cacheDuration, function () {
            $activeStateClass = ActiveLicenseAttributedState::class;

            // Get all sports with translated names
            $allSports = Sport::orderBy('name')->get()->map(function ($sport) {
                $translationKey = 'sports.' . Str::slug($sport->name, '_');
                $translatedName = __($translationKey);

                return [
                    'id' => $sport->id,
                    'name' => $translatedName === $translationKey ? $sport->name : $translatedName,
                ];
            });

            // Get license counts (global - all federations)
            $results = LicenseAttributed::query()
                ->select([
                    'license.sport_id',
                    'professional_roles.role',
                    DB::raw('COUNT(*) as license_count'),
                ])
                ->join('license', 'license_attributed.license_id', '=', 'license.id')
                ->join('professional_roles', 'license.professional_role_id', '=', 'professional_roles.id')
                ->where('license_attributed.model_type', 'individual')
                ->where('license_attributed.status_class', $activeStateClass)
                ->whereNull('license_attributed.deleted_at')
                ->whereIn('professional_roles.role', ['ATHLETE', 'COACH', 'TECHNICAL_OFFICIAL'])
                ->groupBy('license.sport_id', 'professional_roles.role')
                ->get();

            // Calculate totals per sport and sort
            $sportTotals = $allSports->map(function ($sport) use ($results) {
                $sportResults = $results->where('sport_id', $sport['id']);

                return [
                    'id' => $sport['id'],
                    'name' => $sport['name'],
                    'total' => $sportResults->sum('license_count'),
                ];
            })->sortByDesc('total')->values();

            // Prepare data for each role (all sports)
            $athleteData = [];
            $coachData = [];
            $officialData = [];
            $labels = [];

            foreach ($sportTotals as $sport) {
                $sportResults = $results->where('sport_id', $sport['id']);

                $labels[] = $sport['name'];
                $athleteData[] = $sportResults->where('role', 'ATHLETE')->first()?->license_count ?? 0;
                $coachData[] = $sportResults->where('role', 'COACH')->first()?->license_count ?? 0;
                $officialData[] = $sportResults->where('role', 'TECHNICAL_OFFICIAL')->first()?->license_count ?? 0;
            }

            return [
                'datasets' => [
                    [
                        'label' => __('dashboard.role_athlete'),
                        'data' => $athleteData,
                        'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                        'borderColor' => '#3b82f6',
                        'borderWidth' => 1,
                    ],
                    [
                        'label' => __('dashboard.role_coach'),
                        'data' => $coachData,
                        'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                        'borderColor' => '#10b981',
                        'borderWidth' => 1,
                    ],
                    [
                        'label' => __('dashboard.role_technical_official'),
                        'data' => $officialData,
                        'backgroundColor' => 'rgba(245, 158, 11, 0.8)',
                        'borderColor' => '#f59e0b',
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
                    'label' => __('dashboard.role_athlete'),
                    'data' => [],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                ],
                [
                    'label' => __('dashboard.role_coach'),
                    'data' => [],
                    'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                ],
                [
                    'label' => __('dashboard.role_technical_official'),
                    'data' => [],
                    'backgroundColor' => 'rgba(245, 158, 11, 0.8)',
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
