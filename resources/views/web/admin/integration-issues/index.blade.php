<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('integration_issues.title') }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ __('integration_issues.subtitle') }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.moloni-settings.index') }}" class="btn btn-secondary">
                    {{ __('integration_issues.moloni_settings') }}
                </a>
                <a href="{{ route('admin.webhook-logs.index') }}" class="btn btn-secondary">
                    {{ __('integration_issues.webhook_logs') }}
                </a>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-2xl font-bold {{ $stats['total_errors'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ number_format($stats['total_errors']) }}
                </div>
                <div class="text-sm text-gray-500">{{ __('integration_issues.total_errors') }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ __('integration_issues.last_30_days') }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-2xl font-bold {{ $stats['today_total'] > 0 ? 'text-orange-600' : 'text-green-600' }}">
                    {{ number_format($stats['today_total']) }}
                </div>
                <div class="text-sm text-gray-500">{{ __('integration_issues.errors_today') }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xl font-bold text-gray-900">{{ number_format($stats['moloni']['total_errors']) }}</div>
                        <div class="text-sm text-gray-500">Moloni</div>
                    </div>
                    @if($stats['moloni']['last_error'])
                        <div class="text-xs text-gray-400 text-right">
                            {{ __('integration_issues.last') }}:<br>
                            {{ $stats['moloni']['last_error']->diffForHumans() }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xl font-bold text-gray-900">{{ number_format($stats['easypay']['total_errors']) }}</div>
                        <div class="text-sm text-gray-500">Easypay</div>
                    </div>
                    @if($stats['easypay']['last_error'])
                        <div class="text-xs text-gray-400 text-right">
                            {{ __('integration_issues.last') }}:<br>
                            {{ $stats['easypay']['last_error']->diffForHumans() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Error Type Breakdown -->
        @if(count($moloniErrorTypes) > 0 || count($easypayErrorTypes) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                @if(count($moloniErrorTypes) > 0)
                    <div class="bg-white rounded-lg shadow p-4">
                        <h3 class="text-sm font-medium text-gray-700 mb-3 flex items-center gap-2">
                            <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                            {{ __('integration_issues.moloni_error_types') }}
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($moloniErrorTypes as $type => $count)
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                        {{ str_replace('_', ' ', ucfirst($type)) }}
                                    </span>
                                    <span class="text-sm font-semibold text-gray-700">{{ number_format($count) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(count($easypayErrorTypes) > 0)
                    <div class="bg-white rounded-lg shadow p-4">
                        <h3 class="text-sm font-medium text-gray-700 mb-3 flex items-center gap-2">
                            <span class="w-3 h-3 bg-purple-500 rounded-full"></span>
                            {{ __('integration_issues.easypay_error_types') }}
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($easypayErrorTypes as $type => $count)
                                @php
                                    $statusColor = match($type) {
                                        'failed' => 'bg-red-100 text-red-800',
                                        'error' => 'bg-red-100 text-red-800',
                                        'invalid_signature' => 'bg-orange-100 text-orange-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                @endphp
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                                        {{ str_replace('_', ' ', ucfirst($type)) }}
                                    </span>
                                    <span class="text-sm font-semibold text-gray-700">{{ number_format($count) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Filter Form -->
        <x-filter-form :post="route('admin.integration-issues.index')">
            <x-forms.filter-input-select
                label="{{ __('integration_issues.integration') }}"
                name="filter_integration"
                :options="[
                    '' => __('All'),
                    'moloni' => 'Moloni',
                    'easypay' => 'Easypay'
                ]"
            />
            <x-forms.filter-input-text
                label="{{ __('integration_issues.from_date') }}"
                name="filter_date_from"
                type="date"
                :value="$dateFrom->format('Y-m-d')"
            />
            <x-forms.filter-input-text
                label="{{ __('integration_issues.to_date') }}"
                name="filter_date_to"
                type="date"
                :value="$dateTo->format('Y-m-d')"
            />
        </x-filter-form>

        <!-- Errors Table -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b flex items-center justify-between">
                <h2 class="font-semibold text-slate-800">{{ __('integration_issues.recent_errors') }}</h2>
                <span class="text-sm text-gray-500">
                    {{ __('integration_issues.showing_count', ['count' => $allErrors->count()]) }}
                </span>
            </div>

            @if($allErrors->isEmpty())
                <div class="p-8 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">{{ __('integration_issues.no_errors') }}</h3>
                    <p class="text-gray-500">{{ __('integration_issues.no_errors_description') }}</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('integration_issues.integration') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('integration_issues.type') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('integration_issues.error_message') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('integration_issues.reference') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('integration_issues.date') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($allErrors as $error)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if($error['integration'] === 'moloni')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                                Moloni
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">
                                                Easypay
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <code class="text-xs bg-gray-100 px-2 py-1 rounded">
                                            {{ str_replace('_', ' ', $error['type']) }}
                                        </code>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="max-w-md">
                                            <span class="text-sm text-red-600" title="{{ $error['error_message'] }}">
                                                {{ Str::limit($error['error_message'] ?? '-', 80) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        @if($error['document_id'])
                                            <a href="{{ route('admin.document.show', $error['document_id']) }}"
                                               class="text-blue-600 hover:text-blue-800 hover:underline">
                                                {{ $error['document_number'] ?? Str::limit($error['document_id'], 8) }}
                                            </a>
                                        @elseif(isset($error['transaction_id']) && $error['transaction_id'])
                                            <span class="text-gray-600">
                                                TX: {{ Str::limit($error['transaction_id'], 8) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                        <span title="{{ $error['created_at']->format('Y-m-d H:i:s') }}">
                                            {{ $error['created_at']->diffForHumans() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if($error['integration'] === 'moloni')
                                            @if($error['document_id'])
                                                <form action="{{ route('admin.moloni-settings.retry-invoice') }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="document_id" value="{{ $error['document_id'] }}">
                                                    <button type="submit" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                        {{ __('integration_issues.retry') }}
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <a href="{{ route('admin.webhook-logs.show', $error['id']) }}"
                                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                {{ __('View') }}
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Help Section -->
        <div class="mt-6 bg-amber-50 border border-amber-200 rounded-lg p-4">
            <h3 class="text-sm font-medium text-amber-800 mb-2">{{ __('integration_issues.troubleshooting_title') }}</h3>
            <ul class="text-sm text-amber-700 space-y-1 list-disc list-inside">
                <li>{{ __('integration_issues.troubleshooting_moloni_auth') }}</li>
                <li>{{ __('integration_issues.troubleshooting_moloni_config') }}</li>
                <li>{{ __('integration_issues.troubleshooting_easypay_webhook') }}</li>
                <li>{{ __('integration_issues.troubleshooting_easypay_transaction') }}</li>
            </ul>
        </div>

    </div>
</x-layout>
