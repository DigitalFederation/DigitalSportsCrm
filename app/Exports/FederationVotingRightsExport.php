<?php

namespace App\Exports;

use Domain\Federations\Models\Federation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FederationVotingRightsExport implements FromCollection, WithHeadings, WithMapping
{
    protected Collection $federations;
    protected int $year;

    public function __construct(Collection $federations, int $year)
    {
        $this->federations = $federations;
        $this->year = $year;
    }

    public function collection(): Collection
    {
        return $this->federations;
    }

    /**
     * @param  Federation  $federation
     */
    public function map($federation): array
    {
        $votingRight = $federation->votingRights->first(); // Should exist due to manager logic

        return [
            $this->year,
            $federation->member_code,
            $federation->name,
            $votingRight?->general_assembly_status ?? 'N/A',
            $votingRight?->technical_committee_status ?? 'N/A',
            $votingRight?->scientific_committee_status ?? 'N/A',
            $votingRight?->sport_committee_status ?? 'N/A',
            $votingRight?->finswimming_commission_status ?? 'N/A',
            $votingRight?->freediving_commission_status ?? 'N/A',
            $votingRight?->aquathlon_commission_status ?? 'N/A',
            $votingRight?->underwater_hockey_commission_status ?? 'N/A',
            $votingRight?->underwater_rugby_commission_status ?? 'N/A',
            $votingRight?->target_shooting_commission_status ?? 'N/A',
            $votingRight?->sport_diving_commission_status ?? 'N/A',
            $votingRight?->spearfishing_commission_status ?? 'N/A',
            $votingRight?->orienteering_commission_status ?? 'N/A',
            $votingRight?->visual_commission_status ?? 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'Year',
            'International Code',
            'Federation Name',
            'General Assembly',
            'Technical Committee',
            'Scientific Committee',
            'Sport Committee',
            'Finswimming Commission',
            'Freediving Commission',
            'Aquathlon Commission',
            'Underwater Hockey Commission',
            'Underwater Rugby Commission',
            'Target Shooting Commission',
            'Sport Diving Commission',
            'Spearfishing Commission',
            'Orienteering Commission',
            'Visual Commission',
        ];
    }
}
