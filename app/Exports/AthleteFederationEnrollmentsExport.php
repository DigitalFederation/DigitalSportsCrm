<?php

namespace App\Exports;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Enums\EvtEnrollmentStatusEnum;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AthleteFederationEnrollmentsExport implements FromQuery, ShouldQueue, WithHeadings, WithMapping
{
    use Exportable;

    protected $event;
    protected $discipline;
    protected $uniqueAttributes = [];

    public function __construct($event, $discipline)
    {
        $this->event = $event;
        $this->discipline = $discipline;
    }

    public function setUniqueAttributes($uniqueAttributes)
    {
        $this->uniqueAttributes = collect($uniqueAttributes);

        return $this;
    }

    public function query()
    {
        $federationId = Auth::user()->getFederationId();

        // Apply the same filtering logic as in the controller
        $query = $this->event->athleteEnrollments()
            ->whereHas('federation', function ($query) use ($federationId) {
                return $query->where('id', $federationId);
            })
            ->whereNotNull('discipline_id')
            ->when($this->discipline && $this->discipline->exists, function (Builder $query) {
                return $query->where('discipline_id', $this->discipline->id);
            });

        // Apply status filtering based on event type
        if ($this->event->isSportEvent()) {
            $query->where(function ($query) {
                $query->where('status_class', EvtAthleteEnrollmentStatusEnum::PAID->value)
                    ->orWhere('status_class', EvtAthleteEnrollmentStatusEnum::DISCIPLINE_ASSIGNED->value)
                    ->orWhere('status_class', EvtAthleteEnrollmentStatusEnum::COMPLETED->value);
            });
        } else {
            $query->where('status_class', EvtEnrollmentStatusEnum::ACTIVE->value);
        }

        return $query->with([
            'event',
            'individual',
            'federation',
            'discipline',
            'enrollment',
            'attributes.attribute',
        ]);
    }

    public function map($enrollment): array
    {
        $mappedData = [
            $enrollment->individual?->name . ' ' . $enrollment->individual?->surname,
            $enrollment->individual?->member_code,
            $enrollment->individual?->gender,
            $enrollment->individual?->birthdate ? date('d/m/Y', strtotime($enrollment->individual->birthdate)) : '',
        ];

        foreach ($this->uniqueAttributes as $attributeName) {
            $attributeValue = $enrollment->attributes->firstWhere('attribute.name', $attributeName)?->value ?? 'N/A';
            $mappedData[] = $attributeValue;
        }

        $mappedData[] = EvtAthleteEnrollmentStatusEnum::toString($enrollment->status_class);

        return $mappedData;
    }

    public function headings(): array
    {
        $headers = [
            'Name',
            'International Code',
            'Gender',
            'Date of Birth',
        ];

        foreach ($this->uniqueAttributes as $attributeName) {
            $headers[] = $attributeName;
        }

        $headers[] = 'Status';

        return $headers;
    }
}
