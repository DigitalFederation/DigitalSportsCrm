<?php

namespace App\Exports;

use Domain\EvtEvents\Models\Event;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TeamOfficialEnrollmentsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
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

        return $this->event->officialsEnrollments()
            ->with([
                'individual',
                'federation:id,name,member_code',
                'enrollment.event',
                'enrollment.enrollable',
                'attributes.attribute',
            ])
            ->get();
    }

    public function headings(): array
    {
        $baseHeadings = [
            'Name',
            'International Code',
            'Federation',
            'Federation Member Code',
            'Status',
            'Enrolled By',
            'Registration Date',
        ];

        return array_merge($baseHeadings, $this->uniqueAttributes->toArray());
    }

    public function map($enrollment): array
    {
        $enrolledBy = 'N/A';

        if (isset($enrollment->enrollment) && $enrollment->enrollment && isset($enrollment->enrollment->enrollable)) {
            if ($enrollment->enrollment->enrollable_type === 'Domain\Federations\Models\Federation') {
                $enrolledBy = $enrollment->enrollment->enrollable->name;
            } elseif ($enrollment->enrollment->enrollable_type === 'Domain\Entities\Models\Entity') {
                $enrolledBy = $enrollment->enrollment->enrollable->name;
            } elseif ($enrollment->enrollment->enrollable_type === 'Domain\Individuals\Models\Individual') {
                $enrolledBy = $enrollment->enrollment->enrollable->name . ' ' . $enrollment->enrollment->enrollable->surname;
            } else {
                $enrolledBy = ucwords(str_replace('_', ' ', class_basename($enrollment->enrollment->enrollable_type)));
            }
        }

        $baseData = [
            $enrollment->individual?->full_name ?? 'N/A',
            $enrollment->individual?->member_code ?? 'N/A',
            $enrollment->federation?->name ?? 'N/A',
            $enrollment->federation?->member_code ?? 'N/A',
            $enrollment->stateName(),
            $enrolledBy,
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
