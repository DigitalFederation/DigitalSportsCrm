<x-layout>
    <div class="previous-layout-classes">

        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('main.affiliation_plans') }}</h1>
            </div>
            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start gap-2">
                <a class="btn btn-primary" href="{{ route('admin.affiliation-plans.create') }}">
                    <span>{{ __('main.create_affiliation_plan') }}</span>
                </a>
            </div>
        </div>

        <x-information-box title="{{ __('main.affiliation_plans_info_title') }}"
            body="{{ __('main.affiliation_plans_info_body') }}" />

        <div class="sm:flex flex-row gap-4">
            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('admin.affiliation-plans.index')">
                <x-forms.filter-input-text label="{{ __('main.name') }}" name="filter_name"/>
                <x-forms.filter-input-select label="{{ __('main.type') }}" name="filter_type" :options="['individual' => __('main.individual_type'), 'entity' => __('main.entity_type'), 'both' => __('main.both')]"/>
            </x-filter-form>
        </div>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            <x-dynamic-table
                    :pagination="$plans"
                    paginationTitle="{{ __('main.affiliation_plans') }}"
                    :headers="[
                        __('main.name'),
                        __('main.duration_months'),
                        __('main.individual_fee'),
                        __('main.entity_fee'),
                        __('VAT Rate'),
                        __('main.type'),
                        ''
                    ]">
                @foreach($plans as $plan)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            {{ $plan->name }}
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            {{ $plan->duration_months }}
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            @if($plan->individual_fee)
                                €{{ number_format($plan->individual_fee, 2) }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            @if($plan->entity_fee)
                                €{{ number_format($plan->entity_fee, 2) }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $plan->getVatRateLabel() }}
                            </span>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            {{ $plan->type }}
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <div class="space-x-1 flex justify-end">
                                <x-dynamic-table-buttons type="show" :route="route('admin.affiliation-plans.show', $plan->id)"/>
                                <x-dynamic-table-buttons type="edit" :route="route('admin.affiliation-plans.edit', $plan->id)"/>
                                <x-dynamic-table-buttons type="delete" :route="route('admin.affiliation-plans.delete', $plan->id)" method="DELETE"/>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-dynamic-table>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{$plans->links()}}
        </div>
    </div>
</x-layout>