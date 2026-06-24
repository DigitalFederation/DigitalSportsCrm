<?php

namespace App\Reports;

use Domain\EvtEvents\Models\AthleteEnrollment;
use Illuminate\Support\Collection;

class AthleteEnrollmentReport implements ReportTemplate
{
    public static function getDisplayName(): string
    {
        return __('reports.athlete_enrollments');
    }

    public function query($filters)
    {
        return AthleteEnrollment::query()
            ->with(['individual', 'discipline', 'entity', 'federation'])
            ->when($filters['event_id'] ?? null, fn ($query, $eventId) => $query->where('event_id', $eventId));
    }

    public function processData($data): Collection
    {
        if (! $data instanceof Collection) {
            $data = collect($data);
        }

        return $data->map(function (AthleteEnrollment $enrollment): array {
            $individual = $enrollment->individual;

            return [
                __('reports.columns.full_name') => $individual
                    ? trim($individual->name . ' ' . ($individual->surname ?? ''))
                    : __('reports.not_available'),
                __('reports.columns.discipline') => $enrollment->discipline?->name ?? __('reports.not_available'),
                __('reports.columns.entity') => $enrollment->entity?->name ?? __('reports.not_available'),
                __('reports.columns.federation') => $enrollment->federation?->name ?? __('reports.not_available'),
                __('reports.columns.status') => $enrollment->status_class?->value ?? $enrollment->status_class,
            ];
        });
    }

    public function columns(): array
    {
        return [
            __('reports.columns.full_name'),
            __('reports.columns.discipline'),
            __('reports.columns.entity'),
            __('reports.columns.federation'),
            __('reports.columns.status'),
        ];
    }
}
