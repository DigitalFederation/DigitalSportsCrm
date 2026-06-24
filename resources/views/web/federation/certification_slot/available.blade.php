@section('title', 'Slots Available')
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('Slots Available') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

            </div>

        </div>

        <div class="sm:flex flex-row gap-4">
            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('federation.certification-slot.available')">
                <x-forms.filter-input-select label="Certification" name="filter_certification"
                                             :options="$certifications" />
            </x-filter-form>
        </div>

        <!-- More actions -->
        <div class="sm:flex sm:justify-center sm:items-center my-5">

            @if(!empty($slots_paginate) && $slots_paginate->count() > 0)

                <x-dynamic-table :headers="[
                    ['field'=>'certification.name', 'text'=>'Certification', 'sortable'=>true],
                    ['field'=>'total_quantity','text'=>'Slots Available', 'sortable'=>true],
                   ]">
                    @foreach($slots_paginate as $slot)
                        <tr>
                            <td class="px-2 first pl-5 last pr-5 py-3 whitespace-nowrap w-px">{{ $slot->certification->name }}</td>
                            <td class="px-2 first pl-2 last pr-5 py-3 whitespace-nowrap w-px text-left">{{ $slot->total_quantity }}</td>
                        </tr>
                    @endforeach
                </x-dynamic-table>

            @else
                <x-utility.no-data></x-utility.no-data>
            @endif
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $slots_paginate->links() }}
        </div>

    </div>
</x-layout>
