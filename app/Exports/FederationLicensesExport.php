<?php

namespace App\Exports;

use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FederationLicensesExport implements FromQuery, ShouldQueue, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        public ?string $committee = null,
        public ?string $holderType = null
    ) {}

    public function query()
    {
        $user = Auth::user();
        $federationId = $user?->getFederationId();
        if (! $federationId) {
            return LicenseAttributed::query()->whereNull('id'); // Return an empty query
        }

        // Query to get licenses attributed related to the specific federation
        $query = LicenseAttributed::query()
            ->select('license_attributed.*')
            ->join('license', 'license_attributed.license_id', '=', 'license.id')
            ->where('license_attributed.federation_id', $federationId);

        // Apply committee filter if provided
        if ($this->committee) {
            $query->whereHas('license', function ($q) {
                $q->whereHas('committee', function ($q) {
                    $q->where('code', $this->committee);
                });
            });
        }

        // Apply holder type filter if provided
        if ($this->holderType) {
            $query->holderType($this->holderType);
        }

        return $query;
    }

    public function map($row): array
    {
        return [
            $row->holder_name,
            $row->license_number,
            $row->license_name,
            $row->federation_name,
            $row->activated_at,
            $row->date_begin,
            $row->date_expire,
            $row->total_value,
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
            'International Code',
            'License Name',
            'Federation Name',
            'Activated At',
            'Starts At',
            'Ends At',
            'Total Value',
            'Status',
        ];
    }
}
