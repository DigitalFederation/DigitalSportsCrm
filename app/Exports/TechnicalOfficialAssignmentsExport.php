<?php

namespace App\Exports;

use Domain\EvtEvents\Models\RefereeFunctionAssignment;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TechnicalOfficialAssignmentsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function query()
    {
        return RefereeFunctionAssignment::query()
            ->with([
                'event.competition.sport',
                'refereeEnrollment.individual',
                'refereeFunction',
            ])
            ->whereHas('event')
            ->whereHas('refereeEnrollment.individual')
            ->join('evt_events', 'evt_events.id', '=', 'evt_referee_function_assignments.event_id')
            ->orderBy('evt_events.start_date', 'desc')
            ->select('evt_referee_function_assignments.*');
    }

    public function map($assignment): array
    {
        $individual = $assignment->refereeEnrollment?->individual;
        $event = $assignment->event;
        $competition = $event?->competition;
        $sport = $competition?->sport;

        return [
            $individual?->name . ' ' . $individual?->surname,
            $individual?->member_number ?? '-',
            $event?->name ?? '-',
            $this->translateSport($sport?->name),
            $assignment->refereeFunction?->function_name ?? $assignment->function_text ?? '-',
            $assignment->competition_days ?? '-',
            $competition?->cat_competition ?? '-',
            $event?->start_date?->format('d/m/Y') ?? '-',
            in_array($sport?->id, [4, 5]) ? ($assignment->number_of_games ?? '-') : 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            __('events.technical_official'),
            __('events.member_number'),
            __('events.event'),
            __('events.sport'),
            __('events.functions_short'),
            __('events.days_short'),
            __('events.category_short'),
            __('events.start_date'),
            __('events.number_of_games'),
        ];
    }

    private function translateSport(?string $name): string
    {
        if (! $name) {
            return '-';
        }

        $key = 'sports.' . str_replace(' ', '_', strtolower($name));

        return __($key) !== $key ? __($key) : $name;
    }
}
