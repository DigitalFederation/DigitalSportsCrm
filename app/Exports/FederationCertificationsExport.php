<?php

namespace App\Exports;

use Domain\Certifications\Models\CertificationAttributed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FederationCertificationsExport implements FromQuery, ShouldQueue, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        public ?string $committee = null,
    ) {}

    public function query()
    {

        $user = Auth::user();
        $federationId = $user?->getFederationId();
        if (! $federationId) {
            return CertificationAttributed::query()->whereNull('id'); // Return an empty query
        }

        // Query to get certifications attributed related to the specific federation
        $query = CertificationAttributed::query()
            ->select('certification_attributed.*')
            ->join('certification', 'certification_attributed.certification_id', '=', 'certification.id')
            ->where('certification_attributed.federation_id', $federationId);

        // Apply committee filter if provided
        if ($this->committee) {
            $query->filterCommittee($this->committee);
        }

        return $query;
    }

    public function map($row): array
    {
        return [
            $row->holder_name,
            $row->national_code,
            $row->license_number,
            $row->certification_name,
            $row->federation_name,
            $row->entity_name,
            $row->activated_at,
            $row->current_term_starts_at,
            $row->current_term_ends_at,
            $row->stateName(),
        ];
    }
    /**
     * Get the headings for the export.
     */
    public function headings(): array
    {
        return [
            'Holder Name',
            'National Code',
            'International Code',
            'Certification Name',
            'Federation Name',
            'Entity Name',
            'Activated At',
            'Starts At',
            'Ends At',
            'Status',
        ];
    }
}
