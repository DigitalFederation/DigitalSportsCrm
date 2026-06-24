<?php

namespace App\Reports;

use Domain\Certifications\Models\CertificationAttributed;
use Illuminate\Support\Collection;

class TechnicalOfficialsListReport implements ReportTemplate
{
    public static function getDisplayName(): string
    {
        return __('reports.technical_officials_list');
    }

    public function query($filters)
    {
        $query = CertificationAttributed::query()
            ->with([
                'individual.individualFederations.federation',
                'certification.professionalRole',
                'certification.committee',
            ])
            ->whereHas('certification', fn ($q) => $q
                ->whereHas('professionalRole', fn ($r) => $r->where('role', 'TECHNICAL_OFFICIAL'))
                ->whereHas('committee', fn ($c) => $c->where('code', 'SPORT'))
            );

        if (! empty($filters['start_date'])) {
            $query->whereDate('certification_attributed.created_at', '>=', $filters['start_date']);
        }
        if (! empty($filters['end_date'])) {
            $query->whereDate('certification_attributed.created_at', '<=', $filters['end_date']);
        }

        return $query;
    }

    public function processData($data)
    {
        if (! $data instanceof Collection) {
            $data = collect($data);
        }

        return $data->map(function (CertificationAttributed $certAttributed) {
            $individual = $certAttributed->individual;

            $fullName = $individual
                ? trim($individual->name . ' ' . ($individual->surname ?? ''))
                : __('reports.not_available');

            $birthDate = $individual?->birthdate
                ? \Carbon\Carbon::parse($individual->birthdate)->format('d/m/Y')
                : __('reports.not_available');

            $memberNumber = $individual?->member_number ?? __('reports.not_available');

            $certificationName = $certAttributed->certification?->name ?? __('reports.not_available');

            $issueDate = $certAttributed->current_term_starts_at
                ? $certAttributed->current_term_starts_at->format('d/m/Y')
                : __('reports.not_available');

            $expiryDate = $certAttributed->current_term_ends_at
                ? $certAttributed->current_term_ends_at->format('d/m/Y')
                : __('reports.not_available');

            $certificationStatus = $certAttributed->status_class
                ? $certAttributed->stateName()
                : __('reports.not_available');

            $affiliationStatus = $this->getMainFederationAffiliationStatus($individual);

            $email = $individual?->email ?? __('reports.not_available');
            $phone = $individual?->phone ?? __('reports.not_available');

            return [
                __('reports.columns.full_name') => $fullName,
                __('reports.columns.birth_date') => $birthDate,
                __('reports.columns.member_number') => $memberNumber,
                __('reports.columns.certification_name') => $certificationName,
                __('reports.columns.certification_number') => $certAttributed->code ?? __('reports.not_available'),
                __('reports.columns.issue_date') => $issueDate,
                __('reports.columns.expiry_date') => $expiryDate,
                __('reports.columns.certification_status') => $certificationStatus,
                __('reports.columns.affiliation_status') => $affiliationStatus,
                __('reports.columns.email') => $email,
                __('reports.columns.phone') => $phone,
            ];
        });
    }

    public function columns(): array
    {
        return [
            __('reports.columns.full_name'),
            __('reports.columns.birth_date'),
            __('reports.columns.member_number'),
            __('reports.columns.certification_name'),
            __('reports.columns.certification_number'),
            __('reports.columns.issue_date'),
            __('reports.columns.expiry_date'),
            __('reports.columns.certification_status'),
            __('reports.columns.affiliation_status'),
            __('reports.columns.email'),
            __('reports.columns.phone'),
        ];
    }

    private function getMainFederationAffiliationStatus($individual): string
    {
        if (! $individual) {
            return __('reports.not_available');
        }

        $mainFederationAffiliation = $individual->individualFederations
            ->first(fn ($if) => $if->federation?->is_default_federation);

        if (! $mainFederationAffiliation) {
            return __('reports.not_available');
        }

        return $mainFederationAffiliation->stateName();
    }
}
