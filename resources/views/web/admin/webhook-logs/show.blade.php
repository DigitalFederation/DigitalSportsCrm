<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('payment_admin.webhook_log_details') }}</h1>
            </div>
            <div>
                <a href="{{ route('admin.webhook-logs.index') }}" class="btn btn-secondary">
                    {{ __('Back') }}
                </a>
            </div>
        </div>

        <!-- Status Banner -->
        @php
            $statusColor = match($webhookLog->status) {
                'success' => 'bg-green-50 border-green-200',
                'already_processed' => 'bg-blue-50 border-blue-200',
                'failed', 'error' => 'bg-red-50 border-red-200',
                'invalid_signature' => 'bg-orange-50 border-orange-200',
                'acknowledged' => 'bg-yellow-50 border-yellow-200',
                default => 'bg-gray-50 border-gray-200'
            };
            $statusTextColor = match($webhookLog->status) {
                'success' => 'text-green-800',
                'already_processed' => 'text-blue-800',
                'failed', 'error' => 'text-red-800',
                'invalid_signature' => 'text-orange-800',
                'acknowledged' => 'text-yellow-800',
                default => 'text-gray-800'
            };
        @endphp
        <div class="rounded-lg border p-4 mb-6 {{ $statusColor }}">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm font-medium {{ $statusTextColor }}">
                        {{ __('payment_admin.status') }}: {{ str_replace('_', ' ', ucfirst($webhookLog->status)) }}
                    </span>
                    @if($webhookLog->error_message)
                        <p class="text-sm {{ $statusTextColor }} mt-1">{{ $webhookLog->error_message }}</p>
                    @endif
                </div>
                @if($webhookLog->response_code)
                    <span class="text-sm font-mono {{ $statusTextColor }}">
                        HTTP {{ $webhookLog->response_code }}
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Request Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('payment_admin.request_info') }}</h2>

                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">{{ __('payment_admin.request_id') }}</dt>
                        <dd class="text-sm text-gray-900">
                            <code class="bg-gray-100 px-2 py-1 rounded text-xs">{{ $webhookLog->request_id ?? $webhookLog->id }}</code>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">{{ __('payment_admin.gateway') }}</dt>
                        <dd class="text-sm text-gray-900 font-medium">{{ ucfirst($webhookLog->gateway) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">{{ __('payment_admin.ip_address') }}</dt>
                        <dd class="text-sm text-gray-900">
                            <code class="bg-gray-100 px-2 py-1 rounded text-xs">{{ $webhookLog->ip_address ?? '-' }}</code>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">{{ __('payment_admin.processing_time') }}</dt>
                        <dd class="text-sm text-gray-900">
                            @if($webhookLog->processing_time_ms)
                                {{ number_format($webhookLog->processing_time_ms) }}ms
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">{{ __('payment_admin.received_at') }}</dt>
                        <dd class="text-sm text-gray-900">{{ $webhookLog->created_at->format('d/m/Y H:i:s') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Related Records -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('payment_admin.related_records') }}</h2>

                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">{{ __('payment_admin.transaction') }}</dt>
                        <dd>
                            @if($webhookLog->transaction)
                                <a href="{{ route('admin.payment-transactions.show', $webhookLog->transaction_id) }}"
                                   class="text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ $webhookLog->transaction_id }}
                                </a>
                                <span class="text-sm text-gray-500 ml-2">
                                    ({{ number_format($webhookLog->transaction->amount, 2) }}€)
                                </span>
                            @else
                                <span class="text-gray-400">{{ __('payment_admin.no_transaction') }}</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">{{ __('payment_admin.document') }}</dt>
                        <dd>
                            @if($webhookLog->document)
                                <a href="{{ route('admin.document.show', $webhookLog->document_id) }}"
                                   class="text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ $webhookLog->document->number_extended ?? $webhookLog->document_id }}
                                </a>
                            @else
                                <span class="text-gray-400">{{ __('payment_admin.no_document') }}</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Request Headers -->
        @if($webhookLog->headers)
            <div class="bg-white rounded-lg shadow p-6 mt-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('payment_admin.request_headers') }}</h2>
                <pre class="bg-gray-50 p-4 rounded-lg overflow-x-auto text-sm text-gray-700 max-h-64">{{ json_encode($webhookLog->headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        @endif

        <!-- Payload -->
        @if($webhookLog->payload)
            <div class="bg-white rounded-lg shadow p-6 mt-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('payment_admin.webhook_payload') }}</h2>
                <pre class="bg-gray-50 p-4 rounded-lg overflow-x-auto text-sm text-gray-700 max-h-96">{{ json_encode($webhookLog->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @endif

        <!-- Response -->
        @if($webhookLog->response)
            <div class="bg-white rounded-lg shadow p-6 mt-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('payment_admin.response_sent') }}</h2>
                <pre class="bg-gray-50 p-4 rounded-lg overflow-x-auto text-sm text-gray-700 max-h-64">{{ json_encode($webhookLog->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @endif

    </div>
</x-layout>
