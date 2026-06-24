<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('payment_admin.webhook_logs') }}</h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.integration-issues.index') }}" class="btn btn-secondary">
                    {{ __('integration_issues.title') }}
                </a>
                <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-secondary">
                    {{ __('payment_admin.manage_payment_methods') }}
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</div>
                <div class="text-sm text-gray-500">{{ __('payment_admin.total_webhooks') }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-2xl font-bold text-blue-600">{{ number_format($stats['today']) }}</div>
                <div class="text-sm text-gray-500">{{ __('payment_admin.today') }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-2xl font-bold text-green-600">{{ number_format($stats['success_rate'], 1) }}%</div>
                <div class="text-sm text-gray-500">{{ __('payment_admin.success_rate') }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-2xl font-bold text-purple-600">{{ $stats['avg_processing_time'] }}ms</div>
                <div class="text-sm text-gray-500">{{ __('payment_admin.avg_processing_time') }}</div>
            </div>
        </div>

        <!-- Status Breakdown -->
        @if(count($stats['by_status']) > 0)
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <h3 class="text-sm font-medium text-gray-700 mb-3">{{ __('payment_admin.status_breakdown') }}</h3>
                <div class="flex flex-wrap gap-4">
                    @foreach($stats['by_status'] as $status => $count)
                        @php
                            $statusColor = match($status) {
                                'success' => 'bg-green-100 text-green-800',
                                'already_processed' => 'bg-blue-100 text-blue-800',
                                'failed' => 'bg-red-100 text-red-800',
                                'error' => 'bg-red-100 text-red-800',
                                'invalid_signature' => 'bg-orange-100 text-orange-800',
                                'acknowledged' => 'bg-yellow-100 text-yellow-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                        @endphp
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                                {{ str_replace('_', ' ', ucfirst($status)) }}
                            </span>
                            <span class="text-sm font-semibold text-gray-700">{{ number_format($count) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- FILTER RESULTS -->
        <x-filter-form :post="route('admin.webhook-logs.index')">
            <x-forms.filter-input-select
                label="{{ __('payment_admin.gateway') }}"
                name="filter_gateway"
                :options="collect($gatewayOptions)->mapWithKeys(fn($opt) => [$opt['id'] => $opt['name']])->prepend(__('All'), '')->toArray()"
            />
            <x-forms.filter-input-select
                label="{{ __('payment_admin.status') }}"
                name="filter_status"
                :options="collect($statusOptions)->mapWithKeys(fn($opt) => [$opt['id'] => $opt['name']])->prepend(__('All'), '')->toArray()"
            />
            <x-forms.filter-input-text
                label="{{ __('payment_admin.from_date') }}"
                name="filter_date_from"
                type="date"
            />
            <x-forms.filter-input-text
                label="{{ __('payment_admin.to_date') }}"
                name="filter_date_to"
                type="date"
            />
        </x-filter-form>

        <!-- Webhook Logs Table -->
        <div class="bg-white rounded-lg shadow">
            <x-dynamic-table :headers="[
                __('payment_admin.request_id'),
                __('payment_admin.gateway'),
                __('payment_admin.status'),
                __('payment_admin.transaction'),
                __('payment_admin.processing_time'),
                __('payment_admin.date'),
                __('Actions')
            ]">
                @forelse($webhookLogs as $log)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <code class="text-xs bg-gray-100 px-2 py-1 rounded">
                                {{ Str::limit($log->request_id ?? $log->id, 12, '...') }}
                            </code>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="font-medium">{{ ucfirst($log->gateway) }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @php
                                $statusColor = match($log->status) {
                                    'success' => 'bg-green-100 text-green-800',
                                    'already_processed' => 'bg-blue-100 text-blue-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                    'error' => 'bg-red-100 text-red-800',
                                    'invalid_signature' => 'bg-orange-100 text-orange-800',
                                    'acknowledged' => 'bg-yellow-100 text-yellow-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                                {{ str_replace('_', ' ', ucfirst($log->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($log->transaction)
                                <a href="{{ route('admin.payment-transactions.show', $log->transaction_id) }}"
                                   class="text-blue-600 hover:text-blue-800 hover:underline text-sm">
                                    {{ Str::limit($log->transaction_id, 8, '...') }}
                                </a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            @if($log->processing_time_ms)
                                <span class="{{ $log->processing_time_ms > 1000 ? 'text-orange-600' : 'text-gray-600' }}">
                                    {{ number_format($log->processing_time_ms) }}ms
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <a href="{{ route('admin.webhook-logs.show', $log->id) }}"
                               class="text-blue-600 hover:text-blue-800">
                                {{ __('View') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            {{ __('payment_admin.no_webhook_logs_found') }}
                        </td>
                    </tr>
                @endforelse
            </x-dynamic-table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $webhookLogs->links() }}
        </div>

    </div>
</x-layout>
