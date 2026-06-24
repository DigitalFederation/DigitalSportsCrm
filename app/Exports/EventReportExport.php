<?php

namespace App\Exports;

use App\Livewire\EvtEvents\JudgeEnrollments;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\States\ActiveRefereeEnrollmentState;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EventReportExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(protected Event $event) {}

    public function query()
    {
        return RefereeEnrollment::query()
            ->where('event_id', $this->event->id)
            ->where('status_class', ActiveRefereeEnrollmentState::class)
            ->join('individual', 'evt_referees_enrollment.individual_id', '=', 'individual.id')
            ->orderBy('individual.name')
            ->orderBy('individual.surname')
            ->select('evt_referees_enrollment.*')
            ->with([
                'individual:id,name,surname,email',
                'refereeFunctionAssignments' => function ($query) {
                    $query->where('event_id', $this->event->id)->with('refereeFunction');
                },
            ]);
    }

    public function map($enrollment): array
    {
        $assignments = $enrollment->refereeFunctionAssignments;

        $functions = $assignments->isEmpty()
            ? __('events.no_function_assigned')
            : $assignments->map(fn ($a) => $a->function_name)->implode(', ');

        $allPresent = $assignments->isNotEmpty() && $assignments->every('is_present', true);
        $isPresent = $assignments->contains('is_present', true);

        if ($assignments->isEmpty()) {
            $presenceLabel = '-';
        } elseif ($allPresent) {
            $presenceLabel = __('events.all_present');
        } elseif ($isPresent) {
            $presenceLabel = __('events.partially_present');
        } else {
            $presenceLabel = __('events.not_present');
        }

        $totalCompetitionDays = $assignments->sum('competition_days');
        $totalGames = $assignments->sum('number_of_games');
        $assignmentNotes = $assignments->pluck('notes')->filter()->implode('; ');

        $evaluation = $enrollment->evaluation
            ? $enrollment->evaluation . ' - ' . JudgeEnrollments::getEvaluationLabel((int) $enrollment->evaluation)
            : '-';

        return [
            $enrollment->individual?->full_name,
            $enrollment->individual?->email ?? '-',
            $functions,
            $presenceLabel,
            $totalCompetitionDays ?: '-',
            $totalGames ?: '-',
            $evaluation,
            $enrollment->evaluation_notes ?? '-',
            $assignmentNotes ?: '-',
        ];
    }

    public function headings(): array
    {
        return [
            __('events.technical_official'),
            __('events.email'),
            __('events.assigned_functions'),
            __('events.presence'),
            __('events.competition_days'),
            __('events.number_of_games'),
            __('events.evaluation'),
            __('events.evaluation_notes'),
            __('events.notes'),
        ];
    }
}
