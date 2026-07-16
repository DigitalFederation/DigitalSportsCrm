<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('payment_admin.payment_methods') }}</h1>
            </div>
        </div>

        <!-- Gateway Configuration Status -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            @foreach($gatewayStatuses as $gateway => $status)
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-semibold text-lg">{{ ucfirst($gateway) }}</h3>
                        <div class="flex items-center gap-2">
                            @if(($status['currency_supported'] ?? true) === false)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-800"
                                      title="{{ __('payment_admin.currency_unsupported_hint', ['currency' => currency_code()]) }}">
                                    {{ __('payment_admin.currency_unsupported', ['currency' => currency_code()]) }}
                                </span>
                            @endif
                            @if($status['configured'])
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                    {{ __('payment_admin.configured') }}
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                    {{ __('payment_admin.not_configured') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="text-sm text-gray-600 space-y-1">
                        @if($gateway === 'easypay')
                            <p>
                                <span class="font-medium">{{ __('payment_admin.mode') }}:</span>
                                @if($status['sandbox'])
                                    <span class="text-yellow-600">{{ __('payment_admin.sandbox') }}</span>
                                @else
                                    <span class="text-green-600">{{ __('payment_admin.production') }}</span>
                                @endif
                            </p>
                            <p>
                                <span class="font-medium">{{ __('payment_admin.webhook_secret') }}:</span>
                                @if($status['webhook_configured'])
                                    <span class="text-green-600">{{ __('payment_admin.configured') }}</span>
                                @else
                                    <span class="text-red-600">{{ __('payment_admin.not_configured') }}</span>
                                @endif
                            </p>
                            @if($status['webhook_url'])
                                <p class="mt-2">
                                    <span class="font-medium">{{ __('payment_admin.webhook_url') }}:</span>
                                    <code class="bg-gray-100 px-2 py-1 rounded text-xs break-all">{{ $status['webhook_url'] }}</code>
                                </p>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Payment Methods Table -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b">
                <h2 class="font-semibold text-slate-800">{{ __('payment_admin.available_methods') }}</h2>
            </div>

            <x-dynamic-table :headers="[
                __('payment_admin.name'),
                __('payment_admin.driver'),
                __('payment_admin.status'),
                __('payment_admin.instructions'),
                __('Actions')
            ]">
                @foreach($paymentMethods as $method)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="font-medium">{{ $method->name }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <code class="bg-gray-100 px-2 py-1 rounded text-sm">{{ $method->driver }}</code>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($method->is_enabled)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                    {{ __('payment_admin.enabled') }}
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                    {{ __('payment_admin.disabled') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 max-w-xs truncate">
                            {{ Str::limit(strip_tags($method->instructions), 50) }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="space-x-2 flex items-center">
                                <a href="{{ route('admin.payment-methods.edit', $method->id) }}"
                                   class="text-blue-600 hover:text-blue-800">
                                    {{ __('Edit') }}
                                </a>
                                <form action="{{ route('admin.payment-methods.toggle', $method->id) }}"
                                      method="POST"
                                      class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="{{ $method->is_enabled ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}">
                                        {{ $method->is_enabled ? __('payment_admin.disable') : __('payment_admin.enable') }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-dynamic-table>
        </div>

        <!-- Quick Links -->
        <div class="mt-6 flex gap-4">
            <a href="{{ route('admin.payment-transactions.index') }}"
               class="btn btn-secondary">
                {{ __('payment_admin.view_transactions') }}
            </a>
            <a href="{{ route('admin.webhook-logs.index') }}"
               class="btn btn-secondary">
                {{ __('payment_admin.view_webhook_logs') }}
            </a>
        </div>

    </div>
</x-layout>
