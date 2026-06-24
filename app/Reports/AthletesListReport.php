<?php

namespace App\Reports;

use Domain\Licenses\Models\LicenseAttributed;
use Illuminate\Support\Collection;

class AthletesListReport implements ReportTemplate
{
    public static function getDisplayName(): string
    {
        return __('reports.athletes_list');
    }

    public function query($filters)
    {
        $query = LicenseAttributed::query()
            ->with([
                'owner.district',
                'license.professionalRole',
                'license.committee',
            ])
            ->holderType('individual')
            ->committee('SPORT')
            ->whereHas('license', fn ($q) => $q
                ->whereHas('professionalRole', fn ($r) => $r->where('role', 'ATHLETE'))
            );

        if (! empty($filters['start_date'])) {
            $query->whereDate('license_attributed.created_at', '>=', $filters['start_date']);
        }
        if (! empty($filters['end_date'])) {
            $query->whereDate('license_attributed.created_at', '<=', $filters['end_date']);
        }

        return $query;
    }

    public function processData($data)
    {
        if (! $data instanceof Collection) {
            $data = collect($data);
        }

        return $data->map(function (LicenseAttributed $licenseAttributed) {
            $individual = $licenseAttributed->owner;

            $fullName = $individual
                ? trim($individual->name . ' ' . ($individual->surname ?? ''))
                : __('reports.not_available');

            $birthDate = $individual?->birthdate
                ? \Carbon\Carbon::parse($individual->birthdate)->format('d/m/Y')
                : __('reports.not_available');

            $memberNumber = $individual?->member_number ?? __('reports.not_available');

            $gender = $individual?->gender ?? __('reports.not_available');

            $district = $individual?->district?->name ?? __('reports.not_available');

            $licenseName = $licenseAttributed->license_name ?? __('reports.not_available');

            $licenseNumber = $licenseAttributed->license_number ?? __('reports.not_available');

            $issueDate = $licenseAttributed->current_term_starts_at
                ? $licenseAttributed->current_term_starts_at->format('d/m/Y')
                : __('reports.not_available');

            $expiryDate = $licenseAttributed->current_term_ends_at
                ? $licenseAttributed->current_term_ends_at->format('d/m/Y')
                : __('reports.not_available');

            $email = $individual?->email ?? __('reports.not_available');
            $phone = $individual?->phone ?? __('reports.not_available');

            return [
                __('reports.columns.full_name') => $fullName,
                __('reports.columns.member_number') => $memberNumber,
                __('reports.columns.birth_date') => $birthDate,
                __('reports.columns.gender') => $gender,
                __('reports.columns.district') => $district,
                __('reports.columns.license_name') => $licenseName,
                __('reports.columns.license_number') => $licenseNumber,
                __('reports.columns.issue_date') => $issueDate,
                __('reports.columns.expiry_date') => $expiryDate,
                __('reports.columns.email') => $email,
                __('reports.columns.phone') => $phone,
            ];
        });
    }

    public function columns(): array
    {
        return [
            __('reports.columns.full_name'),
            __('reports.columns.member_number'),
            __('reports.columns.birth_date'),
            __('reports.columns.gender'),
            __('reports.columns.district'),
            __('reports.columns.license_name'),
            __('reports.columns.license_number'),
            __('reports.columns.issue_date'),
            __('reports.columns.expiry_date'),
            __('reports.columns.email'),
            __('reports.columns.phone'),
        ];
    }
}
