<?php

namespace App\Exports;

use Domain\EvtEvents\Models\Event;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CoachEnrollmentsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    protected Event $event;
    private $filteredData = null;
    protected Collection $uniqueAttributes;

    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->uniqueAttributes = collect();
    }

    public function setFilteredData($data)
    {
        $this->filteredData = $data;

        return $this;
    }

    public function setUniqueAttributes(Collection $attributes): void
    {
        $this->uniqueAttributes = $attributes;
    }

    public function collection()
    {
        // Use filtered data if provided, otherwise run default query
        if ($this->filteredData !== null) {
            return $this->filteredData;
        }

        return $this->event->coachEnrollments()
            ->with([
                'individual',
                'federation:id,name,member_code',
                'enrollment.event',
                'attributes.attribute',
            ])
            ->get();
    }

    public function headings(): array
    {
        $baseHeadings = [
            'Name',
            'Member Number',
            'Federation',
            'Federation Member Code',
            'Status',
            'Registration Date',
        ];

        return array_merge($baseHeadings, $this->uniqueAttributes->toArray());
    }

    public function map($enrollment): array
    {
        $baseData = [
            $enrollment->individual->full_name,
            $enrollment->individual->member_number,
            $enrollment->federation->name,
            $enrollment->federation->member_code,
            $enrollment->stateName(),
            $enrollment->created_at->format('Y-m-d H:i:s'),
        ];

        // Map attributes
        foreach ($this->uniqueAttributes as $attributeName) {
            $attributeValue = $enrollment->attributes
                ->first(function ($attr) use ($attributeName) {
                    return $attr->attribute->name === $attributeName;
                });

            $baseData[] = $attributeValue ? $attributeValue->value : '-';
        }

        return $baseData;
    }
}
