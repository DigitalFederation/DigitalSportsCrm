<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('Membership Plans') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <!-- Create invoice button -->
                <a class="btn btn-primary" href="{{ route('admin.membership-plan.create') }}">
                    <span class=" ml-2">{{ __('Create Membership Plan') }}</span>
                </a>

            </div>

        </div>

        <div class="sm:flex flex-row gap-4 ">

            <x-utility.card-total title="Plans" :count="$plans->total()"></x-utility.card-total>

            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('admin.membership-plan.index')">
                <x-forms.filter-input-text label="Name" name="filter_name" />
                <x-forms.filter-input-select label="Committee" name="filter_committee" :options="$committees" />
                <x-forms.filter-input-select label="Sport" name="filter_sport" :options="$sports" />
            </x-filter-form>

        </div>


        <!-- More actions -->
        @if(Request::segment(3) != 'filter')
            <div class="mb-2 sm:mb-0">
                <h2 class="grow font-semibold text-slate-800 truncate">{{ __('Latest entries') }}</h2>
            </div>
        @endif

        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            <x-dynamic-table
                :headers="['Committee', 'Name', 'Price (' . currency_symbol() . ')', 'Interval', 'Period', 'Actions']">
                @foreach($plans as $plan)
                    <tr>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @if($plan->committee)
                                {{ $plan->committee->name }}
                            @else
                                {{ config('branding.international.short_name', 'IF') }}
                            @endif
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $plan->name }}</td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $plan->price }}</td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $plan->interval }}</td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $plan->interval_unit }}</td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <div class="space-x-1 flex justify-end">

                                <x-dynamic-table-buttons type="show"
                                                         :route="route('admin.membership-plan.show', $plan->id)" />

                                <x-dynamic-table-buttons type="edit"
                                                         :route="route('admin.membership-plan.edit', $plan->id)" />

                                <x-dynamic-table-buttons type="delete"
                                                         method="DELETE"
                                                         :route="route('admin.membership-plan.destroy', $plan->id)" />

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
