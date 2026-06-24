<?php

namespace App\Reports;

use Carbon\Carbon;
use Domain\Entities\Models\Entity;
use Domain\Insurance\Models\Insurance;
use Domain\Insurance\States\ActiveInsuranceState;

class EntityInsurancesListReport implements ReportTemplate
{
    public static function getDisplayName(): string
    {
        return __('reports.entity_insurances_list');
    }

    public function query($filters)
    {
        $query = Insurance::query()
            ->select([
                'insurances.id',
                'insurances.insurance_plan_id',
                'insurances.member_type',
                'insurances.member_id',
                'insurances.status_class',
                'insurances.created_at',
            ])
            ->where(function ($q) {
                // Filter only entities
                $q->where('member_type', Entity::class)
                    ->orWhere('member_type', 'entity');
            })
            ->with([
                'member',
                'insurancePlan',
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

        return $data->map(function ($insurance) {
            $member = $insurance->member;
            $na = __('reports.not_available');

            return [
                __('reports.columns.entity') => $member?->name ?? $na,
                __('reports.columns.member_number') => $member?->member_number ?? $na,
                __('reports.columns.activation_date') => $this->formatDate($insurance->created_at),
                __('reports.columns.insurance_plan') => $insurance->insurancePlan?->name ?? $na,
                __('reports.columns.status') => $this->getInsuranceStatus($insurance->status_class),
            ];
        });
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
            __('reports.columns.entity'),
            __('reports.columns.member_number'),
            __('reports.columns.activation_date'),
            __('reports.columns.insurance_plan'),
            __('reports.columns.status'),
        ];
    }
}
