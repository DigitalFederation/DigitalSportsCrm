<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('payment_admin.payment_transactions') }}</h1>
            </div>
            <div>
                <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-secondary">
                    {{ __('payment_admin.manage_payment_methods') }}
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</div>
                <div class="text-sm text-gray-500">{{ __('payment_admin.total_transactions') }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-2xl font-bold text-yellow-600">{{ number_format($stats['pending']) }}</div>
                <div class="text-sm text-gray-500">{{ __('payment_admin.pending') }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-2xl font-bold text-green-600">{{ number_format($stats['success']) }}</div>
                <div class="text-sm text-gray-500">{{ __('payment_admin.successful') }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-2xl font-bold text-red-600">{{ number_format($stats['failed']) }}</div>
                <div class="text-sm text-gray-500">{{ __('payment_admin.failed') }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-2xl font-bold text-blue-600">{{ money($stats['total_amount']) }}</div>
                <div class="text-sm text-gray-500">{{ __('payment_admin.total_amount') }}</div>
            </div>
        </div>

        <!-- FILTER RESULTS -->
        <x-filter-form :post="route('admin.payment-transactions.index')">
            <x-forms.filter-input-select
                label="{{ __('payment_admin.status') }}"
                name="filter_status"
                :options="collect($statusOptions)->mapWithKeys(fn($opt) => [$opt['id'] => $opt['name']])->prepend(__('All'), '')->toArray()"
            />
            <x-forms.filter-input-select
                label="{{ __('payment_admin.payment_method') }}"
                name="filter_payment_method_id"
                :options="$paymentMethods->mapWithKeys(fn($m) => [$m->id => $m->name])->prepend(__('All'), '')->toArray()"
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

        <!-- Transactions Table -->
        <div class="bg-white rounded-lg shadow">
            <x-dynamic-table :headers="[
                __('payment_admin.id'),
                __('payment_admin.document'),
                __('payment_admin.payment_method'),
                __('payment_admin.amount'),
                __('payment_admin.status'),
                __('payment_admin.date'),
                __('Actions')
            ]">
                @forelse($transactions as $transaction)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <code class="text-xs bg-gray-100 px-2 py-1 rounded">
                                {{ Str::limit($transaction->id, 8, '...') }}
                            </code>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($transaction->document)
                                <a href="{{ route('admin.document.show', $transaction->document_id) }}"
                                   class="text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ $transaction->document->number_extended ?? $transaction->document_id }}
                                </a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($transaction->paymentMethod)
                                <span class="font-medium">{{ $transaction->paymentMethod->name }}</span>
                                <div class="text-xs text-gray-500">{{ $transaction->paymentMethod->driver }}</div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap font-medium">
                            {{ money($transaction->amount, $transaction->currency) }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
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
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $transaction->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <a href="{{ route('admin.payment-transactions.show', $transaction->id) }}"
                               class="text-blue-600 hover:text-blue-800">
                                {{ __('View') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            {{ __('payment_admin.no_transactions_found') }}
                        </td>
                    </tr>
                @endforelse
            </x-dynamic-table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $transactions->links() }}
        </div>

    </div>
</x-layout>
