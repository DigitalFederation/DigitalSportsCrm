<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Domain\Invoicing\Models\MoloniSyncLog;
use Domain\Payments\Models\WebhookLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class IntegrationIssuesController extends Controller
{
    public function index(Request $request): View
    {
        $dateFrom = $request->input('filter_date_from')
            ? Carbon::parse($request->input('filter_date_from'))->startOfDay()
            : now()->subDays(30)->startOfDay();

        $dateTo = $request->input('filter_date_to')
            ? Carbon::parse($request->input('filter_date_to'))->endOfDay()
            : now()->endOfDay();

        $filterIntegration = $request->input('filter_integration', '');

        // Moloni errors
        $moloniErrorsQuery = MoloniSyncLog::where('status', 'failed')
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        // Easypay errors (failed, error, invalid_signature statuses)
        $easypayErrorsQuery = WebhookLog::whereIn('status', ['failed', 'error', 'invalid_signature'])
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        // Statistics
        $stats = [
            'moloni' => [
                'total_errors' => (clone $moloniErrorsQuery)->count(),
                'today' => MoloniSyncLog::where('status', 'failed')
                    ->whereDate('created_at', today())
                    ->count(),
                'last_error' => MoloniSyncLog::where('status', 'failed')
                    ->latest()
                    ->first()?->created_at,
            ],
            'easypay' => [
                'total_errors' => (clone $easypayErrorsQuery)->count(),
                'today' => WebhookLog::whereIn('status', ['failed', 'error', 'invalid_signature'])
                    ->whereDate('created_at', today())
                    ->count(),
                'last_error' => WebhookLog::whereIn('status', ['failed', 'error', 'invalid_signature'])
                    ->latest()
                    ->first()?->created_at,
            ],
        ];

        $stats['total_errors'] = $stats['moloni']['total_errors'] + $stats['easypay']['total_errors'];
        $stats['today_total'] = $stats['moloni']['today'] + $stats['easypay']['today'];

        // Get recent errors based on filter
        $moloniErrors = collect();
        $easypayErrors = collect();

        if ($filterIntegration === '' || $filterIntegration === 'moloni') {
            $moloniErrors = (clone $moloniErrorsQuery)
                ->latest()
                ->limit(20)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'integration' => 'moloni',
                        'type' => $log->sync_type,
                        'error_message' => $log->error_message,
                        'data' => $log->data,
                        'created_at' => $log->created_at,
                        'document_id' => $log->data['document_id'] ?? null,
                        'document_number' => $log->data['document_number'] ?? null,
                    ];
                });
        }

        if ($filterIntegration === '' || $filterIntegration === 'easypay') {
            $easypayErrors = (clone $easypayErrorsQuery)
                ->with(['transaction', 'document'])
                ->latest()
                ->limit(20)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'integration' => 'easypay',
                        'type' => $log->status,
                        'error_message' => $log->error_message,
                        'data' => [
                            'payload' => $log->payload,
                            'response' => $log->response,
                        ],
                        'created_at' => $log->created_at,
                        'document_id' => $log->document_id,
                        'document_number' => $log->document?->number_extended,
                        'transaction_id' => $log->transaction_id,
                        'request_id' => $log->request_id,
                    ];
                });
        }

        // Merge and sort by date
        $allErrors = $moloniErrors->merge($easypayErrors)
            ->sortByDesc('created_at')
            ->take(50)
            ->values();

        // Error type breakdown for each integration
        $moloniErrorTypes = MoloniSyncLog::where('status', 'failed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('sync_type, COUNT(*) as count')
            ->groupBy('sync_type')
            ->pluck('count', 'sync_type')
            ->toArray();

        $easypayErrorTypes = WebhookLog::whereIn('status', ['failed', 'error', 'invalid_signature'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('web.admin.integration-issues.index', compact(
            'stats',
            'allErrors',
            'moloniErrorTypes',
            'easypayErrorTypes',
            'dateFrom',
            'dateTo',
            'filterIntegration'
        ));
    }
}
