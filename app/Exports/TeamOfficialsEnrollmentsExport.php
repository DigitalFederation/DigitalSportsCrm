<?php

namespace App\Exports;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TeamOfficialsEnrollmentsExport implements FromQuery, ShouldQueue, WithHeadings, WithMapping
{
    use Exportable;

    protected $event;
    private $filteredData = null;
    protected $uniqueAttributes = [];

    public function __construct($event)
    {
        $this->event = $event;
    }

    public function setFilteredData($data)
    {
        $this->filteredData = $data;

        return $this;
    }

    public function setUniqueAttributes($uniqueAttributes)
    {
        $this->uniqueAttributes = collect($uniqueAttributes);

        return $this;
    }

    public function query()
    {
        return $this->event->officialsEnrollments()
            ->with(['event', 'individual', 'enrollment.enrollable', 'attributes.attribute']);
    }

    public function collection()
    {        // Use filtered data if provided, otherwise run default query
        if ($this->filteredData !== null) {

            return $this->filteredData;
        }

        return $this->event->officialsEnrollments()
            ->with(['event', 'individual', 'enrollment.enrollable', 'attributes.attribute']);
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

        $mappedData = [
            $enrollment->individual?->name . ' ' . $enrollment->individual?->surname,
            $enrollment->individual?->email,
            $enrollment->individual?->member_number,
            $enrollment->individual?->gender,
            $enrollment->individual?->birthdate ? date('d/m/Y', strtotime($enrollment->individual->birthdate)) : '',
            $enrolledBy,
        ];

        foreach ($this->uniqueAttributes as $attributeName) {
            $attributeValue = $enrollment->attributes->firstWhere('attribute.name', $attributeName)?->value ?? 'N/A';
            $mappedData[] = $attributeValue;
        }

        $mappedData[] = $enrollment->stateName();

        return $mappedData;
    }

    public function headings(): array
    {
        $headers = [
            'Name',
            'Email',
            'Member Number',
            'Gender',
            'Date of Birth',
            'Enrolled By',
        ];

        foreach ($this->uniqueAttributes as $attributeName) {
            $headers[] = $attributeName;
        }

        $headers[] = 'Status';

        return $headers;
    }
}
