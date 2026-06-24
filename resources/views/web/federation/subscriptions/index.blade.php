@section('title', __('federation.entity_subscriptions'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('federation.entity_subscriptions') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-info" href="{{ route('federation.entity-subscriptions.create') }}">
                    {{ __('federation.create_entity_subscription') }}
                </a>
                <livewire:federation-export-button exportType="entity-subscriptions" />
            </div>

        </div>

        <!-- Filter and Card Total Section -->
        <div class="sm:flex flex-row gap-4">

            <x-utility.card-total title="{{ __('federation.entity_subscriptions') }}" :count="$subscriptions->total()"></x-utility.card-total>

            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('federation.entity-subscriptions.index')">
                <x-forms.filter-input-text label="{{ __('federation.entity_name') }}" name="filter_entity_name" />
                <x-forms.filter-input-text label="{{ __('federation.package_name') }}" name="filter_package_name" />
                <x-forms.filter-input-select label="{{ __('federation.status') }}" name="filter_status" :options="[
                    '' => __('federation.all'),
                    'active' => __('federation.active'),
                    'pending_payment' => __('federation.pending_payment'),
                    'expired' => __('federation.expired')
                ]" />
            </x-filter-form>

        </div>


        <!-- Entity Subscriptions Table -->
        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            <x-dynamic-table
                :headers="[
                    ['text' => __('federation.entity'), 'sortable' => true],
                    ['text' => __('federation.package'), 'sortable' => true],
                    ['text' => __('federation.period')],
                    ['text' => __('federation.status')],
                    ['text' => __('federation.common.actions')]
                 ]">
                @forelse($subscriptions as $subscription)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <div class="font-medium text-slate-800">{{ $subscription->requester->name }}</div>
                            <div class="text-xs text-slate-500">{{ $subscription->requester->code }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <div class="font-medium text-slate-800">{{ $subscription->membershipPackage->name }}</div>
                            <div class="text-xs text-slate-500">{{ $subscription->membershipPackage->description }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <div class="text-slate-800">{{ $subscription->start_date->format('M j, Y') }}</div>
                            <div class="text-xs text-slate-500">{{ __('federation.to') }} {{ $subscription->end_date->format('M j, Y') }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @php
                                $statusClass = class_basename($subscription->status_class);
                                $statusColor = match($statusClass) {
                                    'ActiveMemberSubscriptionState' => 'bg-emerald-100 text-emerald-600',
                                    'PendingPaymentMemberSubscriptionState' => 'bg-amber-100 text-amber-600',
                                    'ExpiredMemberSubscriptionState' => 'bg-slate-100 text-slate-600',
                                    default => 'bg-slate-100 text-slate-600'
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ $subscription->state->name() }}
                            </span>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end">
                            <div class="space-x-1 flex justify-end items-end">
                                <x-dynamic-table-buttons type="show" :route="route('federation.entity-subscriptions.show', $subscription)" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-2 first:pl-5 last:pr-5 py-8 text-center">
                            <div class="text-slate-400">
                                <p class="text-lg font-medium">{{ __('federation.no_entity_subscriptions_found') }}</p>
                                <p class="text-sm mt-2">{{ __('federation.start_by_creating_entity_subscription') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-dynamic-table>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $subscriptions->links() }}
        </div>

    </div>
</x-layout>