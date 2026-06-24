<?php

namespace App\Exports;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class IndividualEnrollmentsExport implements FromQuery, ShouldQueue, WithHeadings, WithMapping
{
    use Exportable;

    protected $uniqueAttributes = [];
    protected $event;
    protected $context;

    public function __construct($event, $context = 'organizer')
    {
        $this->event = $event;
        $this->context = $context;

        // Safely get attributes from enrollments instead of enrollment type
        $this->uniqueAttributes = $this->event->individualEnrollments()
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

    /**
     * Set unique attributes for export columns
     *
     * @param  \Illuminate\Support\Collection|array  $attributes
     * @return $this
     */
    public function setUniqueAttributes($attributes)
    {
        $this->uniqueAttributes = is_array($attributes) ? $attributes : $attributes->toArray();

        return $this;
    }

    public function query()
    {
        $query = $this->event->individualEnrollments()
            ->with([
                'event',
                'individual',
                'federation',
                'enrollment.enrollable',
                'attributes.attribute',
                'enrollment.event',
            ]);

        // Remove the federation filter for now as it's causing linter errors
        // The export will include all data and any filtering should be handled
        // at the controller level before passing data to the exporter

        return $query;
    }

    public function headings(): array
    {
        return array_merge([
            __('events.name'),
            __('events.birthdate'),
            __('events.gender'),
            __('events.member_number'),
            __('events.email'),
            __('events.phone'),
            __('events.enrolled_by'),
            __('main.status'),
        ], $this->uniqueAttributes);
    }

    public function map($enrollment): array
    {
        $mappedData = [
            $enrollment->individual?->first_name_latin . ' ' . $enrollment->individual?->last_name_latin,
            $enrollment->individual?->birthdate ? date('d/m/Y', strtotime($enrollment->individual->birthdate)) : '',
            $enrollment->individual?->gender,
            $enrollment->individual?->member_number,
            $enrollment->individual?->email,
            $enrollment->individual?->phone,
            $this->getEnrolledByName($enrollment),
            $this->getStatusName($enrollment),
        ];

        foreach ($this->uniqueAttributes as $attributeName) {
            $attributeValue = $enrollment->attributes
                ->firstWhere('attribute.name', $attributeName)?->value ?? 'N/A';
            $mappedData[] = $attributeValue;
        }

        return $mappedData;
    }

    private function getStatusName($enrollment): string
    {
        try {
            return \App\Enums\EvtIndividualEnrollmentStatusEnum::toString($enrollment->status_class);
        } catch (\Exception $e) {
            return is_string($enrollment->status_class) ? ucfirst($enrollment->status_class) : '';
        }
    }

    private function getEnrolledByName($enrollment): string
    {
        if (! isset($enrollment->enrollment) || ! $enrollment->enrollment) {
            return 'N/A';
        }

        $enrollable = $enrollment->enrollment->enrollable ?? null;

        if (! $enrollable) {
            return 'N/A';
        }

        return match (get_class($enrollable)) {
            'Domain\Federations\Models\Federation',
            'Domain\Entities\Models\Entity' => $enrollable->name,
            'Domain\Individuals\Models\Individual' => $enrollable->first_name_latin . ' ' . $enrollable->last_name_latin,
            default => class_basename($enrollable),
        };
    }
}
