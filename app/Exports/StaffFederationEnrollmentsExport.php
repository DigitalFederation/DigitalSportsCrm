<?php

namespace App\Exports;

use Domain\EvtEvents\Models\Event;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StaffFederationEnrollmentsExport implements FromCollection, WithHeadings, WithMapping
{
    protected Event $event;
    protected array $uniqueAttributes = [];

    public function __construct(Event $event)
    {
        $this->event = $event;

        // Collect unique attributes from all staff enrollments
        $this->uniqueAttributes = $this->event->staffEnrollments()
            ->with('attributes.attribute')
            ->get()
            ->flatMap(function ($enrollment) {
                return $enrollment->attributes->map(function ($attr) {
                    return $attr->attribute->name ?? null;
                });
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    public function collection()
    {
        return $this->event->staffEnrollments()
            ->whereHas('federation', function ($query) {
                return $query->where('id', auth()->user()->federations()->first()->id);
            })
            ->with(['individual', 'attributes.attribute', 'federation'])
            ->get();
    }

    public function headings(): array
    {
        $headers = [
            'Event Name',
            'Staff Name',
            'International Code',
            'Role',
            'Registration Date',
            'Federation',
        ];

        // Add attribute headers
        foreach ($this->uniqueAttributes as $attributeName) {
            $headers[] = $attributeName;
        }

        return $headers;
    }

    public function map($enrollment): array
    {
        // Get all attribute values with their labels
        $attributeValues = $enrollment->attributes->mapWithKeys(function ($attribute) {
            $attributeModel = $attribute->attribute;
            $value = $attribute->value;

            // Get options from attribute data
            $options = $attributeModel->attribute_data ?? [];

            // If options is array and value is numeric (index), get the label
            if (is_array($options) && is_numeric($value) && isset($options[$value])) {
                $value = $options[$value];
            }

            return [$attributeModel->name => $value];
        });

        $mappedData = [
            $this->event->name,
            $enrollment->individual?->full_name,
            $enrollment->individual?->member_code,
            $attributeValues->values()->join(', '), // Join all role values
            $enrollment->created_at->format('Y-m-d H:i'),
            $enrollment->federation->name,
        ];

        // Add attribute values in the same order as headers
        foreach ($this->uniqueAttributes as $attributeName) {
            $mappedData[] = $attributeValues->get($attributeName) ?? 'N/A';
        }

        return $mappedData;
    }
}
