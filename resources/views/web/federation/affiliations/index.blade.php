@section('title', __('federation.entity_affiliations'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('federation.entity_affiliations') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <a href="{{ route('federation.entity-affiliations.create') }}"
                   class="btn btn-primary">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z"/>
                    </svg>
                    <span class="ml-2">{{ __('federation.common.add_new') }}</span>
                </a>

                <livewire:federation-export-button exportType="entity-affiliations" />
            </div>

        </div>

        <!-- Filter and Card Total Section -->
        <div class="sm:flex flex-row gap-4">

            <x-utility.card-total title="{{ __('federation.entity_affiliations') }}" :count="$affiliations->total()"></x-utility.card-total>

            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('federation.entity-affiliations.index')">
                <x-forms.filter-input-text label="{{ __('federation.entity_name') }}" name="filter_entity_name" />
                <x-forms.filter-input-select label="{{ __('federation.affiliation_plan') }}" name="filter_affiliation_plan_id" :options="$affiliationPlans" />
                <x-forms.filter-input-select label="{{ __('federation.affiliation_status') }}" name="filter_status_class" :options="[
                    '' => __('common.all'),
                    'Domain\\Memberships\\States\\ActiveAffiliationState' => __('common.active'),
                    'Domain\\Memberships\\States\\InactiveAffiliationState' => __('common.inactive'),
                    'Domain\\Memberships\\States\\PendingPaymentAffiliationState' => __('subscriptions.pending_payment'),
                    'Domain\\Memberships\\States\\SuspendedAffiliationState' => __('federation.suspended'),
                    'Domain\\Memberships\\States\\ExpiredAffiliationState' => __('federation.expired'),
                ]" />
                <x-forms.filter-input-date-range
                    label="federation.activation_date"
                    nameStart="filter_activation_date_start"
                    nameEnd="filter_activation_date_end"
                />
            </x-filter-form>

        </div>

        <!-- Entity Affiliations Table -->
        <div class="card-no-padding mb-8">
            <div class="overflow-x-auto">
                <table class="table-auto w-full divide-y divide-slate-200">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-slate-200">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('federation.entity_logo') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('federation.entity') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('federation.affiliation_plan') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('federation.value') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('federation.activation_date') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('main.expiration_date') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('common.status') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-right">{{ __('common.actions') }}</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200">
                        @forelse($affiliations as $affiliation)
                            <tr class="table-row">
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    @php $logoUrl = $affiliation->member->getFirstMediaUrl('profile', 'thumb'); @endphp
                                    @if($logoUrl)
                                        <img class="h-10 w-10 rounded-full object-cover"
                                             src="{{ $logoUrl }}"
                                             alt="{{ $affiliation->member->name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center">
                                            <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                                            </svg>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <a href="{{ route('federation.entity.show', $affiliation->member->id) }}" class="font-medium text-indigo-500 hover:text-indigo-600">
                                        {{ $affiliation->member->name }}
                                    </a>
                                    <div class="text-xs text-slate-500">{{ $affiliation->member->code ?? '' }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="text-slate-800">{{ $affiliation->affiliationPlan->name ?? '-' }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="text-slate-800">{{ number_format($affiliation->affiliationPlan->entity_fee ?? 0, 2) }}&#8364;</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="text-slate-800">{{ $affiliation->activation_date?->format('d-m-Y') ?? '-' }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="text-slate-800">{{ $affiliation->end_date?->format('d-m-Y') }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    @php
                                        $statusColor = match($affiliation->stateColor()) {
                                            'green' => 'bg-emerald-100 text-emerald-600',
                                            'yellow' => 'bg-amber-100 text-amber-600',
                                            'red' => 'bg-rose-100 text-rose-600',
                                            'gray' => 'bg-slate-100 text-slate-600',
                                            default => 'bg-slate-100 text-slate-600'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                        {{ $affiliation->stateName() }}
                                    </span>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="space-x-1 flex justify-end items-end">
                                        <x-dynamic-table-buttons type="show" :route="route('federation.entity-affiliations.show', $affiliation)" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-2 first:pl-5 last:pr-5 py-12 text-center">
                                    <div class="text-slate-500">{{ __('common.no_results') }}</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $affiliations->links() }}
        </div>

    </div>
</x-layout>
