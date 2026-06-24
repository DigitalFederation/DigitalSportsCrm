<?php

namespace App\Exports;

use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InstructorCertificationsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        public string $individualId,
        public ?array $filters = [],
    ) {}

    public function query(): Builder
    {
        $query = CertificationAttributed::query()
            ->select('certification_attributed.*')
            ->join('certifications_attributed_instructors', 'certification_attributed.id', '=', 'certifications_attributed_instructors.attributed_id')
            ->where('certifications_attributed_instructors.individual_id', $this->individualId)
            ->addSelect('certifications_attributed_instructors.is_main');

        $this->applyFilters($query);

        return $query->orderByDesc('activated_at');
    }

    public function map($row): array
    {
        return [
            $row->activated_at ? \Carbon\Carbon::parse($row->activated_at)->format('d/m/Y') : '-',
            $row->certification_name,
            $row->entity_name ?? '-',
            $row->holder_name,
            $row->is_main ? __('certifications.validate.export.course_director') : __('certifications.validate.export.assistant'),
        ];
    }

    public function headings(): array
    {
        return [
            __('certifications.validate.export.issue_date'),
            __('certifications.validate.export.certification'),
            __('certifications.validate.export.entity'),
            __('certifications.validate.export.student'),
            __('certifications.validate.export.function_role'),
        ];
    }

    protected function applyFilters(Builder $query): void
    {
        if (empty($this->filters)) {
            return;
        }

        if (! empty($this->filters['filter_status'])) {
            $query->certificationAttributedStatus($this->filters['filter_status']);
        } else {
            $query->whereNot('status_class', ActiveCertificationAttributedState::class);
        }

        if (! empty($this->filters['filter_certification'])) {
            $query->certificationId($this->filters['filter_certification']);
        }

        if (! empty($this->filters['filter_entity'])) {
            $query->entityName($this->filters['filter_entity']);
        }

        if (! empty($this->filters['filter_individual'])) {
            $query->individualName($this->filters['filter_individual']);
        }

        if (! empty($this->filters['filter_emission_start'])) {
            $query->emissionBefore($this->filters['filter_emission_start']);
        }

        if (! empty($this->filters['filter_emission_end'])) {
            $query->emissionAfter($this->filters['filter_emission_end']);
        }

        if (! empty($this->filters['filter_committee'])) {
            $query->filterCommittee($this->filters['filter_committee']);
        }
    }
}
