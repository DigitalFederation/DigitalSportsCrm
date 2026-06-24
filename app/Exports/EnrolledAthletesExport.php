<?php

namespace App\Exports;

use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Event;
use Domain\Federations\Models\Federation;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EnrolledAthletesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected Event $event;
    protected array $attributeColumns;
    protected ?Collection $athleteEnrollments = null;

    public function __construct(Event $event, array $attributeColumns)
    {
        $this->event = $event;
        $this->attributeColumns = $attributeColumns;
    }

    public function collection(): Collection
    {
        if ($this->athleteEnrollments) {
            return $this->athleteEnrollments;
        }

        $model = auth()->user()->federations()->first()
            ?? auth()->user()->entities()->first();

        $column = $model instanceof Federation ? 'federation_id' : 'entity_id';

        $this->athleteEnrollments = AthleteEnrollment::query()
            ->where('event_id', $this->event->id)
            ->where($column, $model->id)
            ->whereNotNull('discipline_id')
            ->with([
                'attributes' => fn ($q) => $q->with('attribute')->orderBy('attribute_id'),
                'individual:id,name,surname,member_code,gender',
                'discipline:id,name',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->athleteEnrollments;
    }

    public function headings(): array
    {
        return [
            'Name',
            'International Code',
            'Discipline',
            'Gender',
            'Enrolled At',
            ...$this->getAttributeHeadings(),
        ];
    }

    public function map($enrollment): array
    {
        return [
            $enrollment->individual->full_name,
            $enrollment->individual->member_code,
            $enrollment->discipline->name,
            $enrollment->individual->gender,
            $enrollment->created_at->format('Y-m-d H:i:s'),
            ...$this->getAttributeValues($enrollment),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A' => ['width' => 30],
            'B' => ['width' => 15],
            'C' => ['width' => 25],
        ];
    }

    protected function getAttributeHeadings(): array
    {
        return collect($this->attributeColumns)
            ->map(fn ($column) => $column->getLabel())
            ->values()
            ->toArray();
    }

    protected function getAttributeValues(AthleteEnrollment $enrollment): array
    {
        $attributeMap = $enrollment->attributes->keyBy('attribute_id');

        return collect($this->attributeColumns)->map(function ($column) use ($attributeMap) {
            $attrId = (int) str_replace('attr_', '', $column->getName());

            return $attributeMap->get($attrId)?->value ?? '-';
        })->toArray();
    }
}
