@section('title', __('certifications.slot_orders'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('certifications.slot_orders') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <a class="btn btn-primary" href="{{ route('federation.certification-slot.create') }}">
                    <span>{{ __('certifications.create_slot_request') }}</span>
                </a>

            </div>

        </div>

        <div class="sm:flex flex-row gap-4">

            <x-utility.card-total :title="__('certifications.active_orders')" :count="$slots_paginate->total()"></x-utility.card-total>


            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('federation.certification-slot.index')">
                <x-forms.filter-input-select label="{{ __('certifications.certification') }}" name="filter_certification"
                                             :options="$certifications" />
                <x-forms.filter-input-select label="{{ __('certifications.status') }}" name="filter_status" :options="$statuses" />
                <x-forms.filter-input-select label="{{ __('certifications.slots') }}" name="filter_available_slots" :options="$availableSlots" />
            </x-filter-form>
        </div>

        <!-- More actions -->
        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            <x-dynamic-table :headers="[__('certifications.date'), __('certifications.total_price'), __('certifications.status'), __('certifications.shipped'), __('main.Actions')]" :items="$slots">
                @foreach($slots as $slot)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ \Carbon\Carbon::parse($slot->created_at)->format('d/m/Y') }}</td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ money($slot->total_price_sum) }}
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <x-tables.badge :status="ucfirst($slot->stateName())" :color="$slot->stateColor()" />
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">

                            <x-tables.badge :status="$slot->shipped_date ? __('certifications.shipped') : __('certifications.waiting_shipment')"
                                            :color="$slot->shipped_date ? 'green-500' : 'slate-500'" />
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-right">
                            <div class="flex items-center justify-end text-right gap-x-2">
                                @if(!empty($slot->documentDetail))
                                    <x-dynamic-table-buttons type="document"
                                                             :route="route('federation.document.show', $slot->documentDetail->document_id)"
                                                             :target="'_blank'" />
                                @endif
                                <x-dynamic-table-buttons type="show"
                                                         :route="route('federation.certification-slot.group', $slot->order)" />
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-dynamic-table>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{$slots_paginate->links()}}
        </div>

    </div>
</x-layout>
