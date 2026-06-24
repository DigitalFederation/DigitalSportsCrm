<?php

namespace App\Exports;

use App\Livewire\EvtEvents\JudgeEnrollments;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\States\ActiveRefereeEnrollmentState;
use Domain\EvtEvents\States\PendingRefereeEnrollmentState;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RefereeEnrollmentsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $event;

    public function __construct($event)
    {
        $this->event = $event;
    }

    public function query()
    {
        return RefereeEnrollment::query()
            ->where('event_id', $this->event->id)
            ->where(function ($q) {
                $q->where('status_class', ActiveRefereeEnrollmentState::class)
                    ->orWhere('status_class', PendingRefereeEnrollmentState::class);
            })
            ->with([
                'individual:id,name,surname,member_number,gender,birthdate',
                'attributes.attribute',
                'refereeFunctionAssignments.refereeFunction',
            ]);
    }

    public function map($enrollment): array
    {
        $gender = match ($enrollment->individual?->gender) {
            'male' => 'M',
            'female' => 'F',
            default => '-',
        };

        $functionsPerformed = $enrollment->refereeFunctionAssignments->isEmpty()
            ? __('events.no_function_assigned')
            : $enrollment->refereeFunctionAssignments->map(fn ($a) => $a->function_name)->implode(', ');

        $selectAttribute = $enrollment->attributes
            ->first(fn ($attr) => $attr->attribute && $attr->attribute->attribute_type === 'SELECT');
        $enrolledFunction = $selectAttribute?->value ?: '-';

        $evaluation = $enrollment->evaluation
            ? $enrollment->evaluation . ' - ' . JudgeEnrollments::getEvaluationLabel((int) $enrollment->evaluation)
            : '-';

        return [
            $enrollment->individual?->full_name,
            $enrollment->individual?->birthdate ? date('d/m/Y', strtotime($enrollment->individual->birthdate)) : '',
            $gender,
            $enrollment->individual?->member_number,
            $functionsPerformed,
            $enrolledFunction,
            $evaluation,
        ];
    }

    public function headings(): array
    {
        return [
            __('events.referee'),
            __('events.birth_date'),
            __('events.gender'),
            __('events.member_number'),
            __('events.functions_performed'),
            __('events.enrolled_function'),
            __('events.evaluation'),
        ];
    }
}
