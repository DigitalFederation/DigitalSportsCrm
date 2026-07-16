<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('payment_admin.transaction_details') }}</h1>
            </div>
            <div>
                <a href="{{ route('admin.payment-transactions.index') }}" class="btn btn-secondary">
                    {{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Transaction Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('payment_admin.transaction_info') }}</h2>

                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">{{ __('payment_admin.id') }}</dt>
                        <dd class="text-sm text-gray-900">
                            <code class="bg-gray-100 px-2 py-1 rounded text-xs">{{ $transaction->id }}</code>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">{{ __('payment_admin.status') }}</dt>
                        <dd>
                            @php
                                $statusColor = match($transaction->status) {
                                    'success' => 'bg-green-100 text-green-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                                {{ ucfirst($transaction->status) }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">{{ __('payment_admin.amount') }}</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ money($transaction->amount, $transaction->currency) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">{{ __('payment_admin.payment_method') }}</dt>
                        <dd class="text-sm text-gray-900">
                            @if($transaction->paymentMethod)
                                {{ $transaction->paymentMethod->name }}
                                <span class="text-gray-500">({{ $transaction->paymentMethod->driver }})</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">{{ __('payment_admin.created_at') }}</dt>
                        <dd class="text-sm text-gray-900">{{ $transaction->created_at->format('d/m/Y H:i:s') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">{{ __('payment_admin.updated_at') }}</dt>
                        <dd class="text-sm text-gray-900">{{ $transaction->updated_at->format('d/m/Y H:i:s') }}</dd>
                    </div>
                    @if($transaction->comment)
                        <div class="pt-3 border-t">
                            <dt class="text-sm font-medium text-gray-500 mb-1">{{ __('payment_admin.comment') }}</dt>
                            <dd class="text-sm text-gray-900">{{ $transaction->comment }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Document Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('payment_admin.document_info') }}</h2>

                @if($transaction->document)
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">{{ __('payment_admin.document_number') }}</dt>
                            <dd class="text-sm text-gray-900">
                                <a href="{{ route('admin.document.show', $transaction->document_id) }}"
                                   class="text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ $transaction->document->number_extended ?? $transaction->document_id }}
                                </a>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">{{ __('payment_admin.document_status') }}</dt>
                            <dd class="text-sm text-gray-900">
                                <x-tables.badge :status="$transaction->document->stateName()"
                                                :color="$transaction->document->stateColor()" />
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">{{ __('payment_admin.document_total') }}</dt>
                            <dd class="text-sm font-semibold text-gray-900">{{ money($transaction->document->total_value, $transaction->document->currency) }}</dd>
                        </div>
                        @if($transaction->document->owner)
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">{{ __('payment_admin.owner') }}</dt>
                                <dd class="text-sm text-gray-900">{{ $transaction->document->getOrganizationName() }}</dd>
                            </div>
                        @endif
                    </dl>

                    <div class="mt-4">
                        <a href="{{ route('admin.document.show', $transaction->document_id) }}"
                           class="btn btn-secondary btn-sm">
                            {{ __('payment_admin.view_document') }}
                        </a>
                    </div>
                @else
                    <p class="text-gray-500 text-sm">{{ __('payment_admin.no_document_associated') }}</p>
                @endif
            </div>
        </div>

        <!-- Payment Data -->
        @if($transaction->payment_data)
            <div class="bg-white rounded-lg shadow p-6 mt-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('payment_admin.payment_data') }}</h2>
                <pre class="bg-gray-50 p-4 rounded-lg overflow-x-auto text-sm text-gray-700">{{ json_encode($transaction->payment_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @endif

    </div>
</x-layout>
