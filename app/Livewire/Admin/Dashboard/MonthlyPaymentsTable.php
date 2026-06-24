<?php

namespace App\Livewire\Admin\Dashboard;

use Domain\Documents\States\PaidDocumentState;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class MonthlyPaymentsTable extends Component
{
    public int $selectedYear;

    public function mount(): void
    {
        $this->selectedYear = now()->year;
    }

    public function updatedSelectedYear(): void
    {
        // Livewire reactivity handles re-render
    }

    public function render()
    {
        $cacheKey = "admin_monthly_payments_{$this->selectedYear}";
        $ttl = 3600;

        $data = Cache::remember($cacheKey, $ttl, function () {
            return $this->getMonthlyPayments($this->selectedYear);
        });

        return view('livewire.admin.dashboard.monthly-payments-table', [
            'monthlyData' => $data['monthlyData'],
            'categoryTotals' => $data['categoryTotals'],
            'monthTotals' => $data['monthTotals'],
            'grandTotal' => $data['grandTotal'],
            'availableYears' => $this->getAvailableYears(),
            'categories' => $this->getCategories(),
            'months' => $this->getMonths(),
        ]);
    }

    private function getMonthlyPayments(int $year): array
    {
        $paidStateClass = PaidDocumentState::class;

        $results = DB::table('document_detail as dd')
            ->join('document as d', 'dd.document_id', '=', 'd.id')
            ->leftJoin('member_subscriptions as ms', function ($join) {
                $join->on('ms.id', '=', 'dd.owner_id')
                    ->where('dd.owner_type', '=', 'Domain\\Memberships\\Models\\MemberSubscription');
            })
            ->select(
                DB::raw('MONTH(d.created_at) as month'),
                DB::raw("SUM(CASE
                    WHEN dd.owner_type = 'Domain\\\\Memberships\\\\Models\\\\MemberSubscription'
                        AND dd.description NOT LIKE 'Seguro:%' AND dd.description NOT LIKE 'Insurance:%'
                        AND (ms.member_type = 'entity' OR ms.member_type = 'Domain\\\\Entities\\\\Models\\\\Entity')
                    THEN dd.total_value ELSE 0 END) as entity_affiliations"),
                DB::raw("SUM(CASE
                    WHEN dd.owner_type = 'Domain\\\\Memberships\\\\Models\\\\MemberSubscription'
                        AND dd.description NOT LIKE 'Seguro:%' AND dd.description NOT LIKE 'Insurance:%'
                        AND (ms.member_type = 'individual' OR ms.member_type = 'Domain\\\\Individuals\\\\Models\\\\Individual')
                    THEN dd.total_value ELSE 0 END) as individual_affiliations"),
                DB::raw("SUM(CASE
                    WHEN dd.owner_type = 'Domain\\\\Licenses\\\\Models\\\\LicenseAttributed'
                        AND (d.owner_type = 'entity' OR d.owner_type = 'Domain\\\\Entities\\\\Models\\\\Entity')
                    THEN dd.total_value ELSE 0 END) as entity_licenses"),
                DB::raw("SUM(CASE
                    WHEN dd.owner_type = 'Domain\\\\Licenses\\\\Models\\\\LicenseAttributed'
                        AND (d.owner_type = 'individual' OR d.owner_type = 'Domain\\\\Individuals\\\\Models\\\\Individual')
                    THEN dd.total_value ELSE 0 END) as individual_licenses"),
                DB::raw("SUM(CASE
                    WHEN dd.owner_type = 'Domain\\\\EvtEvents\\\\Models\\\\Enrollment'
                    THEN dd.total_value ELSE 0 END) as event_registrations"),
                DB::raw("SUM(CASE
                    WHEN dd.owner_type = 'Domain\\\\Certifications\\\\Models\\\\CertificationAttributed'
                    THEN dd.total_value ELSE 0 END) as certifications"),
                DB::raw("SUM(CASE
                    WHEN dd.owner_type = 'Domain\\\\Memberships\\\\Models\\\\MemberSubscription'
                        AND (dd.description LIKE 'Seguro:%' OR dd.description LIKE 'Insurance:%')
                        AND (ms.member_type = 'entity' OR ms.member_type = 'Domain\\\\Entities\\\\Models\\\\Entity')
                    THEN dd.total_value ELSE 0 END) as entity_insurances"),
                DB::raw("SUM(CASE
                    WHEN dd.owner_type = 'Domain\\\\Memberships\\\\Models\\\\MemberSubscription'
                        AND (dd.description LIKE 'Seguro:%' OR dd.description LIKE 'Insurance:%')
                        AND (ms.member_type = 'individual' OR ms.member_type = 'Domain\\\\Individuals\\\\Models\\\\Individual')
                    THEN dd.total_value ELSE 0 END) as individual_insurances"),
                DB::raw("SUM(CASE
                    WHEN dd.owner_type NOT IN (
                        'Domain\\\\Memberships\\\\Models\\\\MemberSubscription',
                        'Domain\\\\Licenses\\\\Models\\\\LicenseAttributed',
                        'Domain\\\\EvtEvents\\\\Models\\\\Enrollment',
                        'Domain\\\\Certifications\\\\Models\\\\CertificationAttributed',
                        'Domain\\\\Documents\\\\Models\\\\Document',
                        ''
                    ) AND dd.owner_type IS NOT NULL
                    THEN dd.total_value ELSE 0 END) as others"),
            )
            ->where('d.status_class', $paidStateClass)
            ->where('d.owner_type', '!=', 'Domain\\Documents\\Models\\Document')
            ->where(function ($query) {
                $query->where('dd.is_debit', false)->orWhereNull('dd.is_debit');
            })
            ->whereYear('d.created_at', $year)
            ->whereNull('d.deleted_at')
            ->whereNull('dd.deleted_at')
            ->groupBy(DB::raw('MONTH(d.created_at)'))
            ->get()
            ->keyBy('month');

        $categories = array_keys($this->getCategories());
        $monthlyData = [];
        $categoryTotals = array_fill_keys($categories, 0);
        $monthTotals = [];
        $grandTotal = 0;

        for ($month = 1; $month <= 12; $month++) {
            $row = $results->get($month);
            $monthTotal = 0;

            foreach ($categories as $category) {
                $value = $row ? round((float) $row->{$category}, 2) : 0;
                $monthlyData[$category][$month] = $value;
                $categoryTotals[$category] += $value;
                $monthTotal += $value;
            }

            $monthTotals[$month] = round($monthTotal, 2);
            $grandTotal += $monthTotal;
        }

        return [
            'monthlyData' => $monthlyData,
            'categoryTotals' => $categoryTotals,
            'monthTotals' => $monthTotals,
            'grandTotal' => round($grandTotal, 2),
        ];
    }

    private function getAvailableYears(): array
    {
        $minYear = DB::table('document')
            ->whereNull('deleted_at')
            ->min(DB::raw('YEAR(created_at)'));

        $currentYear = now()->year;
        $startYear = $minYear ? (int) $minYear : $currentYear;

        return range($currentYear, $startYear);
    }

    public function getCategories(): array
    {
        return [
            'entity_affiliations' => __('dashboard.entity_affiliations'),
            'individual_affiliations' => __('dashboard.individual_affiliations'),
            'entity_licenses' => __('dashboard.entity_licenses'),
            'individual_licenses' => __('dashboard.individual_licenses'),
            'event_registrations' => __('dashboard.event_registrations'),
            'certifications' => __('dashboard.certifications'),
            'entity_insurances' => __('dashboard.entity_insurances'),
            'individual_insurances' => __('dashboard.individual_insurances'),
            'others' => __('dashboard.others'),
        ];
    }

    private function getMonths(): array
    {
        return [
            1 => __('dashboard.month_jan'),
            2 => __('dashboard.month_feb'),
            3 => __('dashboard.month_mar'),
            4 => __('dashboard.month_apr'),
            5 => __('dashboard.month_may'),
            6 => __('dashboard.month_jun'),
            7 => __('dashboard.month_jul'),
            8 => __('dashboard.month_aug'),
            9 => __('dashboard.month_sep'),
            10 => __('dashboard.month_oct'),
            11 => __('dashboard.month_nov'),
            12 => __('dashboard.month_dec'),
        ];
    }
}
