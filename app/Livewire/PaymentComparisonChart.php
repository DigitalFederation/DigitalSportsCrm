<?php

namespace App\Livewire;

use Domain\Documents\Models\Document;
use Domain\Documents\States\PaidDocumentState;
use Domain\Federations\Models\Federation;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentComparisonChart extends ChartWidget
{
    protected static ?string $heading = 'Total Payments by Federation (Current Year)';

    protected function getData(): array
    {
        $federationClass = Federation::class;
        $currentYear = Carbon::now()->year;

        $paymentsData = Document::query()
            ->select('owner_id', DB::raw('SUM(total_value) as total_payments'))
            ->where('owner_type', $federationClass)
            ->where('status_class', PaidDocumentState::class)
            ->whereYear('created_at', $currentYear)
            ->groupBy('owner_id')
            ->with('owner') // Eager load federation
            ->get();

        $data = [];
        $labels = [];
        $backgroundColors = [];

        foreach ($paymentsData as $payment) {
            $federation = Federation::find($payment->owner_id); // Retrieve federation details
            $labels[] = $federation ? $federation->member_code : 'Unknown Federation';
            $data[] = $payment->total_payments;
            $backgroundColors[] = '#' . substr(md5($federation ? $federation->member_code : 'unknown'), 0, 6); // Color based on federation name
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Payments',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
