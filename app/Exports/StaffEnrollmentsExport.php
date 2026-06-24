<?php

namespace App\Exports;

use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\StaffEnrollment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StaffEnrollmentsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $event;
    protected $discipline;
    protected $uniqueAttributes;

    public function __construct(Event $event, $discipline = null)
    {
        $this->event = $event;
        $this->discipline = $discipline;
        $this->uniqueAttributes = $this->extractUniqueAttributes();
    }

    /**
     * Extract unique attributes from staff enrollments
     */
    protected function extractUniqueAttributes(): Collection
    {
        $uniqueAttributes = collect();
        $enrollments = $this->event->staffEnrollments()
            ->with(['attributes.attribute'])
            ->get();

        if ($enrollments->isNotEmpty()) {
            foreach ($enrollments as $enrollment) {
                foreach ($enrollment->attributes as $attribute) {
                    if ($attribute->attribute) {
                        // Maintain the attribute_id as the key
                        $uniqueAttributes->put($attribute->attribute_id, $attribute->attribute->name);
                    }
                }
            }
            // Keep attribute IDs as keys
            $uniqueAttributes = $uniqueAttributes->sort();
        }

        return $uniqueAttributes;
    }

    public function query()
    {
        $query = StaffEnrollment::query()
            ->where('event_id', $this->event->id)
            ->with(['event', 'individual', 'federation', 'attributes.attribute']);

        if ($this->discipline) {
            $query->where('discipline_id', $this->discipline->id);
        }

        return $query;
    }

    public function headings(): array
    {
        // Base headers
        $headers = [
            'Staff Name',
            'Member Number',
            'Email',
            'Gender',
            'Date of Birth',
            'Federation',
        ];

        // Add attribute headers
        if ($this->uniqueAttributes->isNotEmpty()) {
            foreach ($this->uniqueAttributes as $attributeId => $attributeName) {
                $headers[] = $attributeName;
            }
        }

        return $headers;
    }

    public function map($enrollment): array
    {
        // Base data
        $data = [
            $enrollment->individual?->name . ' ' . $enrollment->individual?->surname, // Name
            $enrollment->individual?->member_number, // Member Number
            $enrollment->individual?->email, // Email
            $enrollment->individual?->gender, // Gender
            $enrollment->individual?->birthdate ? date('d/m/Y', strtotime($enrollment->individual->birthdate)) : '', // Date of Birth
            $enrollment->federation?->name, // Federation
        ];

        // Add attribute values
        if ($this->uniqueAttributes->isNotEmpty()) {
            foreach ($this->uniqueAttributes as $attributeId => $attributeName) {
                $attributeValue = $enrollment->attributes->where('attribute_id', $attributeId)->first()?->value ?? 'N/A';
                $data[] = $attributeValue;
            }
        }

        return $data;
    }

    /**
     * Allow setting unique attributes externally if needed
     */
    public function setUniqueAttributes(Collection $attributes): void
    {
        $this->uniqueAttributes = $attributes;
    }
}
