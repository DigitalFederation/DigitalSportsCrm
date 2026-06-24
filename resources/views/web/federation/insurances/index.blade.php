@section('title', __('federation.entity_insurances'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('federation.entity_insurances') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-info" href="{{ route('federation.entity-insurances.create') }}">
                    {{ __('federation.create_entity_insurance') }}
                </a>
                <livewire:federation-export-button exportType="entity-insurances" />
            </div>

        </div>

        <!-- Filter and Card Total Section -->
        <div class="sm:flex flex-row gap-4">

            <x-utility.card-total title="{{ __('federation.entity_insurances') }}" :count="$insurances->total()"></x-utility.card-total>

            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('federation.entity-insurances.index')">
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


        <!-- Entity Insurance Subscriptions Table -->
        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            <x-dynamic-table
                :headers="[
                    ['text' => __('federation.entity'), 'sortable' => true],
                    ['text' => __('federation.insurance_plan'), 'sortable' => true],
                    ['text' => __('federation.coverage_period')],
                    ['text' => __('federation.policy_number')],
                    ['text' => __('federation.status')],
                    ['text' => __('federation.common.actions')]
                 ]">
                @forelse($insurances as $insurance)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3">
                            <div class="max-w-xs">
                                <div class="font-medium text-slate-800 truncate" title="{{ $insurance->member->name }}">{{ $insurance->member->name }}</div>
                                <div class="text-xs text-slate-500">{{ $insurance->member->code }}</div>
                            </div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3">
                            <div class="max-w-xs">
                                <div class="font-medium text-slate-800 truncate" title="{{ $insurance->insurancePlan->name }}">{{ $insurance->insurancePlan->name }}</div>
                                <div class="text-xs text-slate-500 truncate">{{ $insurance->insurancePlan->description ?? '' }}</div>
                            </div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="text-slate-800">{{ $insurance->start_date->format('M j, Y') }}</div>
                            <div class="text-xs text-slate-500">{{ __('federation.to') }} {{ $insurance->end_date->format('M j, Y') }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium text-slate-800">
                                {{ $insurance->policy_number }}
                            </div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @php
                                $statusClass = class_basename($insurance->status_class);
                                $statusColor = match($statusClass) {
                                    'ActiveInsuranceState' => 'bg-emerald-100 text-emerald-600',
                                    'InactiveInsuranceState' => 'bg-slate-100 text-slate-600',
                                    'ExpiredInsuranceState' => 'bg-red-100 text-red-600',
                                    default => 'bg-slate-100 text-slate-600'
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ $insurance->stateName() }}
                            </span>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end">
                            <div class="space-x-1 flex justify-end items-end">
                                <x-dynamic-table-buttons type="show" :route="route('federation.entity-insurances.show', $insurance)" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-2 first:pl-5 last:pr-5 py-8 text-center">
                            <div class="text-slate-400">
                                <p class="text-lg font-medium">{{ __('federation.no_entity_insurances_found') }}</p>
                                <p class="text-sm mt-2">{{ __('federation.start_by_creating_entity_insurance') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-dynamic-table>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $insurances->links() }}
        </div>

    </div>
</x-layout>