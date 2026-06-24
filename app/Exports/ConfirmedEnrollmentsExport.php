<?php

namespace App\Exports;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Exports\Concerns\ResolvesEnrollmentAttributes;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\CoachEnrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\Models\StaffEnrollment;
use Domain\EvtEvents\Models\TeamOfficialEnrollment;
use Domain\Federations\Models\Federation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ConfirmedEnrollmentsExport implements WithMultipleSheets
{
    protected Event $event;

    protected Entity|Federation $model;

    protected bool $isFederation;

    protected string $type;

    public function __construct(Event $event, Entity|Federation $model, bool $isFederation, string $type = 'all')
    {
        $this->event = $event;
        $this->model = $model;
        $this->isFederation = $isFederation;
        $this->type = $type;
    }

    public function sheets(): array
    {
        $sheets = [];

        if ($this->type === 'all' || $this->type === 'athletes') {
            $sheets[] = new AthletesSheet($this->event, $this->model, $this->isFederation);
        }

        if ($this->type === 'all' || $this->type === 'coaches') {
            $sheets[] = new CoachesSheet($this->event, $this->model, $this->isFederation);
        }

        if ($this->type === 'all' || $this->type === 'officials') {
            $sheets[] = new OfficialsSheet($this->event, $this->model, $this->isFederation);
        }

        if ($this->isFederation && ($this->type === 'all' || $this->type === 'referees')) {
            $sheets[] = new RefereesSheet($this->event, $this->model, $this->isFederation);
        }

        if ($this->isFederation && ($this->type === 'all' || $this->type === 'staff')) {
            $sheets[] = new StaffSheet($this->event, $this->model, $this->isFederation);
        }

        return $sheets;
    }
}

class AthletesSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use ResolvesEnrollmentAttributes;

    protected Event $event;

    protected Entity|Federation $model;

    protected bool $isFederation;

    protected Collection $enrollments;

    protected array $attributes = [];

    public function __construct(Event $event, Entity|Federation $model, bool $isFederation)
    {
        $this->event = $event;
        $this->model = $model;
        $this->isFederation = $isFederation;
        $this->enrollments = $this->buildCollection();
        $this->attributes = $this->resolveAttributes();
    }

    public function title(): string
    {
        return __('events.athletes_tab');
    }

    public function collection(): Collection
    {
        return $this->enrollments;
    }

    protected function buildCollection(): Collection
    {
        $query = AthleteEnrollment::query()
            ->where('event_id', $this->event->id)
            ->whereIn('status_class', [
                EvtAthleteEnrollmentStatusEnum::PAID->value,
                EvtAthleteEnrollmentStatusEnum::COMPLETED->value,
                EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value,
            ])
            ->with([
                'individual:id,name,surname,member_code,member_number,gender,birthdate',
                'discipline:id,name',
                'entity:id,name',
                'attributes.attribute',
            ])
            ->orderBy('created_at', 'desc');

        if (! $this->isFederation) {
            $query->where('entity_id', $this->model->id);
        }

        return $query->get();
    }

    public function headings(): array
    {
        $headings = [
            __('events.name'),
            __('events.birth_date'),
            __('events.gender'),
            __('events.member_number'),
            __('events.entity'),
            __('events.discipline'),
        ];

        foreach ($this->attributes as $attribute) {
            $headings[] = $attribute['name'];
        }

        return $headings;
    }

    public function map($enrollment): array
    {
        $data = [
            $enrollment->individual->full_name,
            $enrollment->individual->birthdate?->format('d/m/Y') ?? '-',
            $enrollment->individual->gender === 'male' ? 'M' : 'F',
            $enrollment->individual->member_number ?? '-',
            $enrollment->entity?->name ?? '-',
            $enrollment->discipline?->name ?? '-',
        ];

        foreach ($this->attributes as $attribute) {
            $data[] = $enrollment->attributes->firstWhere('attribute_id', $attribute['id'])?->value ?? '-';
        }

        return $data;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

}

class CoachesSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use ResolvesEnrollmentAttributes;

    protected Event $event;

    protected Entity|Federation $model;

    protected bool $isFederation;

    protected Collection $enrollments;

    protected array $attributes = [];

    public function __construct(Event $event, Entity|Federation $model, bool $isFederation)
    {
        $this->event = $event;
        $this->model = $model;
        $this->isFederation = $isFederation;
        $this->enrollments = $this->buildCollection();
        $this->attributes = $this->resolveAttributes();
    }

    public function title(): string
    {
        return __('events.coaches_tab');
    }

    public function collection(): Collection
    {
        return $this->enrollments;
    }

    protected function buildCollection(): Collection
    {
        $query = CoachEnrollment::query()
            ->where('event_id', $this->event->id)
            ->where(function ($q) {
                $q->where('status_class', 'like', '%RegisteredCoachEnrollmentState')
                    ->orWhere('status_class', 'like', '%AssignedCoachEnrollmentState');
            })
            ->with([
                'individual:id,name,surname,member_code,member_number,gender,birthdate',
                'entity:id,name',
                'attributes.attribute',
            ])
            ->orderBy('created_at', 'desc');

        if (! $this->isFederation) {
            $query->where('entity_id', $this->model->id);
        }

        return $query->get();
    }

    public function headings(): array
    {
        $headings = [
            __('events.name'),
            __('events.birth_date'),
            __('events.gender'),
            __('events.member_number'),
            __('events.entity'),
        ];

        foreach ($this->attributes as $attribute) {
            $headings[] = $attribute['name'];
        }

        return $headings;
    }

    public function map($enrollment): array
    {
        $data = [
            $enrollment->individual->full_name,
            $enrollment->individual->birthdate?->format('d/m/Y') ?? '-',
            $enrollment->individual->gender === 'male' ? 'M' : 'F',
            $enrollment->individual->member_number ?? '-',
            $enrollment->entity?->name ?? '-',
        ];

        foreach ($this->attributes as $attribute) {
            $data[] = $enrollment->attributes->firstWhere('attribute_id', $attribute['id'])?->value ?? '-';
        }

        return $data;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

}

class OfficialsSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use ResolvesEnrollmentAttributes;

    protected Event $event;

    protected Entity|Federation $model;

    protected bool $isFederation;

    protected Collection $enrollments;

    protected array $attributes = [];

    public function __construct(Event $event, Entity|Federation $model, bool $isFederation)
    {
        $this->event = $event;
        $this->model = $model;
        $this->isFederation = $isFederation;
        $this->enrollments = $this->buildCollection();
        $this->attributes = $this->resolveAttributes();
    }

    public function title(): string
    {
        return __('events.officials_tab');
    }

    public function collection(): Collection
    {
        return $this->enrollments;
    }

    protected function buildCollection(): Collection
    {
        $query = TeamOfficialEnrollment::query()
            ->where('event_id', $this->event->id)
            ->where(function ($q) {
                $q->where('status_class', 'like', '%RegisteredTeamOfficialEnrollmentState')
                    ->orWhere('status_class', 'like', '%AssignedTeamOfficialEnrollmentState');
            })
            ->with([
                'individual:id,name,surname,member_code,member_number,gender,birthdate',
                'entity:id,name',
                'attributes.attribute',
            ])
            ->orderBy('created_at', 'desc');

        if (! $this->isFederation) {
            $query->where('entity_id', $this->model->id);
        }

        return $query->get();
    }

    public function headings(): array
    {
        $headings = [
            __('events.name'),
            __('events.birth_date'),
            __('events.gender'),
            __('events.member_number'),
            __('events.entity'),
        ];

        foreach ($this->attributes as $attribute) {
            $headings[] = $attribute['name'];
        }

        return $headings;
    }

    public function map($enrollment): array
    {
        $data = [
            $enrollment->individual->full_name,
            $enrollment->individual->birthdate?->format('d/m/Y') ?? '-',
            $enrollment->individual->gender === 'male' ? 'M' : 'F',
            $enrollment->individual->member_number ?? '-',
            $enrollment->entity?->name ?? '-',
        ];

        foreach ($this->attributes as $attribute) {
            $data[] = $enrollment->attributes->firstWhere('attribute_id', $attribute['id'])?->value ?? '-';
        }

        return $data;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

}

class RefereesSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use ResolvesEnrollmentAttributes;

    protected Event $event;

    protected Entity|Federation $model;

    protected bool $isFederation;

    protected Collection $enrollments;

    protected array $attributes = [];

    public function __construct(Event $event, Entity|Federation $model, bool $isFederation)
    {
        $this->event = $event;
        $this->model = $model;
        $this->isFederation = $isFederation;
        $this->enrollments = $this->buildCollection();
        $this->attributes = $this->resolveAttributes();
    }

    public function title(): string
    {
        return __('events.referees_tab');
    }

    public function collection(): Collection
    {
        return $this->enrollments;
    }

    protected function buildCollection(): Collection
    {
        return RefereeEnrollment::query()
            ->where('event_id', $this->event->id)
            ->where(function ($q) {
                $q->where('status_class', 'like', '%ActiveRefereeEnrollmentState');
            })
            ->with([
                'individual:id,name,surname,member_code,member_number,gender,birthdate',
                'attributes.attribute',
            ])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        $headings = [
            __('events.name'),
            __('events.birth_date'),
            __('events.gender'),
            __('events.member_number'),
        ];

        foreach ($this->attributes as $attribute) {
            $headings[] = $attribute['name'];
        }

        return $headings;
    }

    public function map($enrollment): array
    {
        $data = [
            $enrollment->individual->full_name,
            $enrollment->individual->birthdate?->format('d/m/Y') ?? '-',
            $enrollment->individual->gender === 'male' ? 'M' : 'F',
            $enrollment->individual->member_number ?? '-',
        ];

        foreach ($this->attributes as $attribute) {
            $data[] = $enrollment->attributes->firstWhere('attribute_id', $attribute['id'])?->value ?? '-';
        }

        return $data;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

}

class StaffSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use ResolvesEnrollmentAttributes;

    protected Event $event;

    protected Entity|Federation $model;

    protected bool $isFederation;

    protected Collection $enrollments;

    protected array $attributes = [];

    public function __construct(Event $event, Entity|Federation $model, bool $isFederation)
    {
        $this->event = $event;
        $this->model = $model;
        $this->isFederation = $isFederation;
        $this->enrollments = $this->buildCollection();
        $this->attributes = $this->resolveAttributes();
    }

    public function title(): string
    {
        return __('events.staff_tab');
    }

    public function collection(): Collection
    {
        return $this->enrollments;
    }

    protected function buildCollection(): Collection
    {
        return StaffEnrollment::query()
            ->where('event_id', $this->event->id)
            ->with([
                'individual:id,name,surname,member_code,member_number,gender,birthdate',
                'attributes.attribute',
            ])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        $headings = [
            __('events.name'),
            __('events.birth_date'),
            __('events.gender'),
            __('events.member_number'),
        ];

        foreach ($this->attributes as $attribute) {
            $headings[] = $attribute['name'];
        }

        return $headings;
    }

    public function map($enrollment): array
    {
        $name = $enrollment->individual?->full_name
            ?? trim(($enrollment->first_name ?? '') . ' ' . ($enrollment->last_name ?? ''));

        $data = [
            $name ?: '-',
            $enrollment->individual?->birthdate?->format('d/m/Y') ?? '-',
            match ($enrollment->individual?->gender) {
                'male' => 'M',
                'female' => 'F',
                default => '-',
            },
            $enrollment->individual?->member_number ?? '-',
        ];

        foreach ($this->attributes as $attribute) {
            $data[] = $enrollment->attributes->firstWhere('attribute_id', $attribute['id'])?->value ?? '-';
        }

        return $data;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

}
