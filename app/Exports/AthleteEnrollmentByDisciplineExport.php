<?php

namespace App\Exports;

use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Event;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AthleteEnrollmentByDisciplineExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    protected $event;
    protected $disciplines;
    protected $uniqueAttributes;

    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->disciplines = $this->event->competition->disciplineTemplate->disciplines;
    }

    public function setUniqueAttributes($uniqueAttributes)
    {
        $this->uniqueAttributes = $uniqueAttributes;

        return $this;
    }

    public function collection()
    {
        return AthleteEnrollment::where('event_id', $this->event->id)
            ->with(['individual', 'individual.country', 'discipline', 'attributes.attribute'])
            ->get()
            ->groupBy('individual_id')
            ->map(function ($enrollments) {
                $individual = $enrollments->first()->individual;
                /*
                $disciplineData = $this->disciplines->mapWithKeys(function ($discipline) use ($enrollments) {
                    $enrollment = $enrollments->firstWhere('discipline_id', $discipline->id);
                    return [$discipline->id => $enrollment ? 'Enrolled' : 'Not Enrolled'];
                });
                */
                $disciplineData = $this->disciplines->mapWithKeys(function ($discipline) use ($enrollments) {
                    $enrollment = $enrollments->firstWhere('discipline_id', $discipline->id);
                    if ($enrollment) {
                        $attributeValues = $enrollment->attributes->map(function ($attribute) {
                            return [
                                'name' => $attribute->attribute->name,
                                'value' => $attribute->value,
                            ];
                        });

                        return [$discipline->name => $attributeValues];
                    }

                    return [$discipline->name => null];
                });
                /*
                $attributes = $this->uniqueAttributes->mapWithKeys(function ($attributeName) use ($enrollments) {
                    $value = $enrollments->flatMap->attributes
                        ->firstWhere('attribute.name', $attributeName)->value ?? 'N/A';
                    return [$attributeName => $value];
                });
                */

                return [
                    'individual' => $individual,
                    'disciplines' => $disciplineData,
                ];
            });
    }

    public function headings(): array
    {
        return array_merge(
            ['Name', 'Family Name', 'Member Number', 'Gender', 'Date of Birth', 'Country', 'IOC'],
            $this->disciplines->pluck('name')->toArray()
        );
    }

    public function map($row): array
    {
        // Format name: Last Name UPPERCASE, Given Name Title Case using Latin fields
        $firstName = mb_convert_case($row['individual']->first_name_latin ?? '', MB_CASE_TITLE, 'UTF-8');
        $lastName = mb_strtoupper($row['individual']->last_name_latin ?? '', 'UTF-8');

        $baseInfo = [
            $firstName,  // Given Name (Title Case)
            $lastName,   // Last name (UPPERCASE)
            $row['individual']->member_number ?? 'N/A',
            $row['individual']->gender ?? 'N/A',
            $row['individual']->birthdate ?? 'N/A',
            $row['individual']->country->name ?? 'N/A',
            $row['individual']->country->ioc ?? 'N/A',
        ];

        $disciplineInfo = $row['disciplines']->map(function ($attributes) {
            if ($attributes === null) {
                return 'Not Enrolled';
            }

            return $attributes->map(function ($attr) {
                return "{$attr['name']}: {$attr['value']}";
            })->join("\n");
        });

        return array_merge($baseInfo, $disciplineInfo->toArray());
    }
}
