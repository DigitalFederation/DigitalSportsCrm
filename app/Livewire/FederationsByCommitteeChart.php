<?php

namespace App\Livewire;

use Domain\Memberships\Models\Membership;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class FederationsByCommitteeChart extends ChartWidget
{
    protected static ?string $heading = 'Federations by Committee';

    protected function getData(): array
    {
        $currentYear = Carbon::now()->year;

        // Fetching data
        $query = Membership::query()
            ->selectRaw('committee.name AS committee, COUNT(DISTINCT federation.id) AS total,
                COUNT(DISTINCT CASE WHEN membership.status_class = ? THEN federation.id END) as paid',
                [\Domain\Memberships\States\ActiveMembershipState::class])
            ->join('federation', 'membership.federation_id', '=', 'federation.id')
            ->join('membership_membership_plan', 'membership.id', '=', 'membership_membership_plan.membership_id')
            ->join('membership_plan', 'membership_membership_plan.membership_plan_id', '=', 'membership_plan.id')
            ->join('committee', 'membership_plan.committee_id', '=', 'committee.id')
            ->whereYear('membership.current_term_starts_at', $currentYear)
            ->groupBy('committee.name')
            ->get();

        $labels = $query->pluck('committee')->all();
        $totalData = $query->pluck('total')->all();
        $paidData = $query->pluck('paid')->all();

        // Preparing datasets
        $datasets = [
            [
                'label' => 'Total Federations',
                'data' => $totalData,
                'backgroundColor' => '#36A2EB',
                'borderColor' => '#888888',
                'borderWidth' => 0,
            ],
            [
                'label' => 'Paid Federations',
                'data' => $paidData,
                'backgroundColor' => '#4BCA81',
                'borderColor' => '#888888',
                'borderWidth' => 0,
            ],
        ];

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
