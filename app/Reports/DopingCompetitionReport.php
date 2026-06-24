<?php

namespace App\Reports;

use Domain\EvtEvents\Models\Competition;
use Illuminate\Support\Collection;

class DopingCompetitionReport implements ReportTemplate
{
    public static function getDisplayName(): string
    {
        return __('reports.doping_competitions');
    }

    public function query($filters)
    {
        return Competition::query()
            ->with(['event', 'venueCountry', 'antiDopingRecords'])
            ->when($filters['year'] ?? null, fn ($query, $year) => $query->whereYear('start_date', $year));
    }

    public function processData($data): Collection
    {
        if (! $data instanceof Collection) {
            $data = collect($data);
        }

        return $data->map(function (Competition $competition): array {
            return [
                __('reports.columns.competition') => $competition->full_name,
                __('reports.columns.event') => $competition->event?->name ?? __('reports.not_available'),
                __('reports.columns.country') => $competition->venueCountry?->name ?? __('reports.not_available'),
                __('reports.columns.start_date') => $competition->start_date?->format('Y-m-d') ?? __('reports.not_available'),
                __('reports.columns.end_date') => $competition->end_date?->format('Y-m-d') ?? __('reports.not_available'),
                __('reports.columns.anti_doping_records') => $competition->antiDopingRecords->count(),
            ];
        });
    }

    public function columns(): array
    {
        return [
            __('reports.columns.competition'),
            __('reports.columns.event'),
            __('reports.columns.country'),
            __('reports.columns.start_date'),
            __('reports.columns.end_date'),
            __('reports.columns.anti_doping_records'),
        ];
    }
}
