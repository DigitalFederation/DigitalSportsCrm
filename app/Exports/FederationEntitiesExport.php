<?php

namespace App\Exports;

use Domain\Entities\Models\Entity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FederationEntitiesExport implements FromQuery, ShouldQueue, WithHeadings, WithMapping
{
    use Exportable;

    public function query()
    {
        $user = Auth::user();
        $federationId = $user?->getFederationId();
        if (! $federationId) {
            return Entity::query()->whereNull('id'); // Return an empty query
        }

        // Query to get entities related to the specific federation
        return Entity::query()
            ->select('entity.*')
            ->join('entity_federation', 'entity.id', '=', 'entity_federation.entity_id')
            ->where('entity_federation.federation_id', $federationId);
    }

    public function map($row): array
    {
        return [
            $row->name,
            $row->legal_name,
            $row->legal_responsible_person,
            $row->phone,
            $row->website,
            $row->address,
            $row->location,
            $row->email,
            $row->member_code,
        ];
    }

    /**
     * Get the headings for the export.
     */
    public function headings(): array
    {
        return [
            'Name',
            'Legal Name',
            'Legal Responsible Person',
            'Phone',
            'Website',
            'Address',
            'Location',
            'Email',
            'International Code',
        ];
    }
}
