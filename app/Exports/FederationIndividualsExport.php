<?php

namespace App\Exports;

use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FederationIndividualsExport implements FromQuery, ShouldQueue, WithHeadings, WithMapping
{
    use Exportable;

    public function query()
    {

        $user = Auth::user();
        $federationId = $user?->getFederationId();
        if (! $federationId) {
            return Individual::query()->whereNull('id'); // Return an empty query
        }

        // Query to get individuals related to the specific federation
        return Individual::query()
            ->select('individual.*', 'local_federation.name as local_federation_name', 'entity.name as entity_name')
            ->join('individual_federation', 'individual.id', '=', 'individual_federation.individual_id')
            ->leftJoin('federation as local_federation', function ($join) {
                $join->on('local_federation.id', '=', 'individual_federation.federation_id')
                    ->where('local_federation.is_local', 1);
            })
            ->leftJoin('individual_entity', 'individual.id', '=', 'individual_entity.individual_id')
            ->leftJoin('entity', 'individual_entity.entity_id', '=', 'entity.id')
            ->where('individual_federation.federation_id', $federationId)
            ->where('individual_federation.status_class', ActiveIndividualFederationState::class);
    }

    public function map($row): array
    {

        return [
            $row->name,
            $row->surname,
            $row->birthdate,
            $row->gender,
            $row->email,
            $row->member_code,
            $row->address,
            $row->doc_ref,
            $row->doc_ref_validation_date,
            $row->national_federation_number,
            $row->local_federation_name,
            $row->entity_name,
        ];
    }

    /**
     * Get the headings for the export.
     */
    public function headings(): array
    {
        return [
            'Name',
            'Surname',
            'Birthdate',
            'Gender',
            'Email',
            'International Code',
            'Address',
            'Ident. Nº',
            'Ident. Expire',
            'National Federation Number',
            'National Organization',
            'Entity',
        ];
    }
}
