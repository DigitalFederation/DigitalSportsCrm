<?php

namespace App\Exports;

use App\Enums\EvtAthleteEnrollmentStatusEnum;
use App\Scopes\IndividualsFromFederationScope;
use Domain\Documents\States\DocumentState;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AthleteEnrollmentsExport implements FromQuery, ShouldQueue, WithHeadings, WithMapping
{
    use Exportable;

    protected $event;
    protected $discipline;
    protected $uniqueAttributes = [];

    public function __construct($event, $discipline = null)
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
        return $this->event->athleteEnrollments()
            ->whereNotNull('discipline_id')
            ->when($this->discipline && $this->discipline->exists, function (Builder $query) {
                return $query->where('discipline_id', $this->discipline->id);
            })
            ->with([
                'event',
                'individual' => function ($query) {
                    $query->withoutGlobalScope(IndividualsFromFederationScope::class);
                },
                'individual.federations',
                'individual.country',
                'individual.licenses' => function ($query) {
                    $query->whereHas('license', function ($q) {
                        $q->where('committee_id', 1);
                    })->orderBy('created_at', 'desc');
                },
                'federation',
                'discipline',
                'enrollment.enrollable',
                'enrollment.document',
                'attributes.attribute',
            ]);
    }

    public function map($enrollment): array
    {
        if (! $enrollment->individual) {
            return [];
        }

        $enrolledBy = $this->getEnrolledBy($enrollment);
        $federation = $enrollment->individual->federations->first();

        $document = $enrollment->enrollment?->document;

        // Licenses must be related to the sport of the event
        $sportLicense = $this->getSportLicense($enrollment->individual->licenses, $this->event);

        // Format name: Family Name UPPERCASE, Given Name Title Case
        $firstName = mb_convert_case($enrollment->individual?->name, MB_CASE_TITLE, 'UTF-8');
        $lastName = mb_strtoupper($enrollment->individual?->surname, 'UTF-8');

        $mappedData = [
            $enrollment->discipline?->name,
            $enrollment->discipline?->style,
            $enrollment->discipline?->distance,
            $enrollment->discipline?->enrollment_type,
            $firstName,  // Given Name (Title Case)
            $lastName,   // Family Name (UPPERCASE)
            $enrollment->individual?->email,
            $enrollment->individual?->member_number,
            $enrollment->individual?->gender,
            $enrollment->individual?->birthdate ? date('d/m/Y', strtotime($enrollment->individual->birthdate)) : '',
            $enrollment->individual?->country?->name ?? 'N/A',
            $enrollment->individual?->country?->ioc ?? 'N/A',
            $enrolledBy,
            $federation ? $federation->name : 'N/A',
            $sportLicense ? $sportLicense->license_name : 'N/A',
            $sportLicense ? $sportLicense->license_number : 'N/A',
            $sportLicense ? $sportLicense->current_term_ends_at : 'N/A',
            $enrollment->created_at->format('d/m/Y H:i:s'),
            $this->getDocumentStateName($document),
            $document ? $this->getDocumentOrderNumber($document) : 'N/A',
            $enrollment->team_identifier ?? 'N/A',
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
            'Discipline',
            'Discipline Style',
            'Discipline Distance',
            'Enrollment Type',
            'Given Name',
            'Family Name',
            'Email',
            'Member Number',
            'Gender',
            'Date of Birth',
            'Nationality',
            'IOC',
            'Enrolled By',
            'Federation',
            'Sport License Name',
            'Sport License Number',
            'Sport License End Date',
            'Enrollment Date',
            'Document Status',
            'Document ID',
            'Team Identifier',
        ];

        foreach ($this->uniqueAttributes as $attributeName) {
            $headers[] = $attributeName;
        }

        $headers[] = 'Enrollment Status';

        return $headers;
    }

    protected function getEnrolledBy($enrollment)
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
            'Domain\Individuals\Models\Individual' => $enrollable->name . ' ' . $enrollable->surname,
            default => class_basename($enrollable),
        };
    }

    private function getDocumentStateName($document): string
    {
        if (! $document) {
            return 'N/A';
        }

        $state = $document->state;

        if ($state instanceof DocumentState) {
            return ucfirst($state->name());
        }

        return 'Unknown';
    }

    private function getDocumentOrderNumber($document): string
    {
        if (! $document) {
            return 'N/A';
        }

        if (! empty($document->invoice_number)) {
            return $document->getInvoiceExtendedAttribute();
        } else {
            return $document->number_extended ?? 'N/A';
        }
    }

    private function getSportLicense($licensesAttributed, $event)
    {
        $sportId = $event->competition->sport_id;
        if ($licensesAttributed->isEmpty()) {
            return null;
        }

        $licensesAttributed->load('license');
        // Find licenseAttributed with a license that has sport_id equals to the sport of the event
        $sportLicense = $licensesAttributed->firstWhere('license.sport_id', $sportId);

        return $sportLicense;
    }
}
