<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Domain\Payments\Models\WebhookLog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class WebhookLogController extends Controller
{
    public function index(): View
    {
        $webhookLogs = QueryBuilder::for(WebhookLog::class)
            ->allowedFilters([
                AllowedFilter::exact('gateway'),
                AllowedFilter::exact('status'),
                AllowedFilter::scope('filter_date_from', 'created_after'),
                AllowedFilter::scope('filter_date_to', 'created_before'),
            ])
            ->with(['transaction', 'document'])
            ->orderByDesc('created_at')
            ->paginate(25)
            ->appends(request()->query());

        $gatewayOptions = [
            ['id' => 'easypay', 'name' => 'EasyPay'],
        ];

        $statusOptions = [
            ['id' => 'success', 'name' => __('Success')],
            ['id' => 'already_processed', 'name' => __('Already Processed')],
            ['id' => 'failed', 'name' => __('Failed')],
            ['id' => 'error', 'name' => __('Error')],
            ['id' => 'invalid_signature', 'name' => __('Invalid Signature')],
            ['id' => 'acknowledged', 'name' => __('Acknowledged')],
        ];

        $stats = $this->getStatistics();

        return view('web.admin.webhook-logs.index', compact(
            'webhookLogs',
            'gatewayOptions',
            'statusOptions',
            'stats'
        ));
    }

    public function show(string $id): View
    {
        $webhookLog = WebhookLog::with(['transaction', 'document'])
            ->findOrFail($id);

        return view('web.admin.webhook-logs.show', compact('webhookLog'));
    }

    private function getStatistics(): array
    {
        $total = WebhookLog::count();

        return [
            'total' => $total,
            'today' => WebhookLog::where('created_at', '>=', now()->startOfDay())->count(),
            'this_week' => WebhookLog::where('created_at', '>=', now()->startOfWeek())->count(),
            'this_month' => WebhookLog::where('created_at', '>=', now()->startOfMonth())->count(),
            'success_rate' => $this->calculateSuccessRate($total),
            'by_status' => WebhookLog::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'avg_processing_time' => (int) WebhookLog::whereNotNull('processing_time_ms')
                ->avg('processing_time_ms'),
        ];
    }

    private function calculateSuccessRate(int $total): float
    {
        if ($total === 0) {
            return 0;
        }

        $successful = WebhookLog::whereIn('status', ['success', 'already_processed'])->count();

        return round(($successful / $total) * 100, 1);
    }
}
