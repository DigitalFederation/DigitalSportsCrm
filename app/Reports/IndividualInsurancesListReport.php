<?php

namespace App\Reports;

use Carbon\Carbon;
use Domain\Individuals\Models\Individual;
use Domain\Insurance\Models\Insurance;
use Domain\Insurance\States\ActiveInsuranceState;

class IndividualInsurancesListReport implements ReportTemplate
{
    public static function getDisplayName(): string
    {
        return __('reports.individual_insurances_list');
    }

    public function query($filters)
    {
        $query = Insurance::query()
            ->select([
                'insurances.id',
                'insurances.insurance_plan_id',
                'insurances.member_type',
                'insurances.member_id',
                'insurances.member_subscription_id',
                'insurances.start_date',
                'insurances.end_date',
                'insurances.individual_fee',
                'insurances.status_class',
                'insurances.requester_type',
                'insurances.requester_id',
                'insurances.created_at',
            ])
            ->where(function ($q) {
                // Filter only individuals
                $q->where('member_type', Individual::class)
                    ->orWhere('member_type', 'individual');
            })
            ->with([
                'member',
                'insurancePlan',
                'memberSubscription.membershipPackage',
                'requester',
            ])
            // Only export insurances with Active status
            ->where('status_class', ActiveInsuranceState::class);

        // Apply date filters based on created_at (activation date)
        if (! empty($filters['start_date'])) {
            $query->whereDate('insurances.created_at', '>=', $filters['start_date']);
        }
        if (! empty($filters['end_date'])) {
            $query->whereDate('insurances.created_at', '<=', $filters['end_date']);
        }

        return $query;
    }

    public function processData($data)
    {
        if (! $data instanceof \Illuminate\Support\Collection) {
            $data = collect($data);
        }

        $na = __('reports.not_available');

        return $data->map(function ($insurance) use ($na) {
            $member = $insurance->member;

            return [
                __('reports.columns.member') => $this->getMemberName($member),
                __('reports.columns.birth_date') => $member?->birthdate ? $this->formatDate($member->birthdate, 'd/m/Y') : $na,
                __('reports.columns.member_number') => $member?->member_number ?? $na,
                __('reports.columns.activation_date') => $this->formatDate($insurance->created_at),
                __('reports.columns.age_category') => $this->calculateAgeCategory($member?->birthdate),
                __('reports.columns.requested_by') => $this->getRequesterName($insurance),
                __('reports.columns.insurance_plan') => $insurance->insurancePlan?->name ?? $na,
                __('reports.columns.status') => $this->getInsuranceStatus($insurance->status_class),
            ];
        });
    }

    private function getMemberName($member): string
    {
        if (! $member) {
            return __('reports.not_available');
        }

        return $member->native_name ?? trim(($member->name ?? '') . ' ' . ($member->surname ?? '')) ?: __('reports.not_available');
    }

    private function calculateAgeCategory($birthdate): string
    {
        if (! $birthdate) {
            return __('reports.not_available');
        }

        try {
            $birth = $birthdate instanceof Carbon ? $birthdate : Carbon::parse($birthdate);
            $age = $birth->age;

            if ($age <= 12) {
                return __('reports.age_categories.child');
            } elseif ($age >= 13 && $age <= 17) {
                return __('reports.age_categories.youth');
            } elseif ($age >= 18 && $age <= 45) {
                return __('reports.age_categories.senior');
            } else {
                return __('reports.age_categories.master');
            }
        } catch (\Exception $e) {
            return __('reports.not_available');
        }
    }

    private function getRequesterName($insurance): string
    {
        $requester = $insurance->requester;

        if (! $requester) {
            return __('reports.not_available');
        }

        $requesterType = $insurance->requester_type ?? '';

        if (str_contains($requesterType, 'Individual') || $requesterType === 'individual') {
            // If the requester is the same individual as the member, show the primary federation.
            if ($insurance->member_id === $insurance->requester_id) {
                return config('branding.primary.short_name', 'DF');
            }

            return $requester->native_name ?? trim(($requester->name ?? '') . ' ' . ($requester->surname ?? '')) ?: __('reports.not_available');
        }

        if (str_contains($requesterType, 'Entity') || $requesterType === 'entity') {
            return $requester->name ?? __('reports.not_available');
        }

        if (str_contains($requesterType, 'User')) {
            return $requester->name ?? $requester->email ?? __('reports.not_available');
        }

        return $requester->name ?? __('reports.not_available');
    }

    private function formatDate($date, $format = 'd/m/Y H:i'): string
    {
        if (! $date) {
            return __('reports.not_available');
        }

        if ($date instanceof Carbon) {
            return $date->format($format);
        }

        return Carbon::parse($date)->format($format);
    }

    private function getInsuranceStatus(?string $statusClass): string
    {
        if (! $statusClass) {
            return __('reports.not_available');
        }

        return match ($statusClass) {
            ActiveInsuranceState::class => __('insurances.active'),
            default => class_basename($statusClass),
        };
    }

    public function columns(): array
    {
        return [
            __('reports.columns.member'),
            __('reports.columns.birth_date'),
            __('reports.columns.member_number'),
            __('reports.columns.activation_date'),
            __('reports.columns.age_category'),
            __('reports.columns.requested_by'),
            __('reports.columns.insurance_plan'),
            __('reports.columns.status'),
        ];
    }
}
