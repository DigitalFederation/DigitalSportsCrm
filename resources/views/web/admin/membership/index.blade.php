<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4 ">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('Memberships') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <a class="btn btn-primary" href="{{ route('admin.membership.create') }}">
                    <span>{{ __('Add Membership') }}</span>
                </a>
            </div>
        </div>

        <!-- FILTER RESULTS COUNT -->
        <div class="sm:flex flex-row gap-4">

            <x-utility.card-total title="Memberships" :count="$memberships->total()"></x-utility.card-total>

            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('admin.membership.index')">
                <x-forms.filter-input-date-range label="Expiration date" nameStart="filter_expiration_start"
                                                 nameEnd="filter_expiration_end" />
                <x-forms.filter-input-select label="Federation Name" name="filter_federation" :options="$federations" />
                <x-forms.filter-input-text label="Federation Code" name="filter_federation_code" />
                <x-forms.filter-input-select label="Country" name="filter_country" :options="$countries" />
                <x-forms.filter-input-select label="Committee" name="filter_committee" :options="$committees" />
                <x-forms.filter-input-select label="Sport" name="filter_sport" :options="$sports" />
                <x-forms.filter-input-select label="Status" name="filter_status" :options="$filter_status" />
            </x-filter-form>

        </div>


        @if(Request::segment(3) != 'filter')
            <div class="mb-2 sm:mb-0">
                <h2 class="grow font-semibold text-slate-800 truncate">{{ __('Latest entries') }}</h2>
            </div>
        @endif

        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            <x-dynamic-table
                :headers="['Name', __('main.Member Code'), 'Plans', 'Status', 'Expiration date', 'Actions']">
                @foreach($memberships as $membership)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            {{ $membership->name }}
                        </td>

                        <!--
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    @if(!empty($membership->federation))
                            <a class="hover:text-blue-500"
                               href="{{ route('admin.federation.show', $membership->federation->id) }}">
                                            {{ $membership->federation->name }}
                            </a>




                        @endif
                        </td>
-->

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @if(!empty($membership->federation))
                                <a class="hover:text-blue-500"
                                   href="{{ route('admin.federation.show', $membership->federation->id) }}">
                                    {{ $membership->federation->member_code }}
                                </a>
                            @endif
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @foreach($membership->plans as $plan)
                                <a class="hover:text-blue-500"
                                   href="{{ route('admin.membership-plan.show', $plan->id) }}">
                                    {{ $plan->name }}
                                </a>
                                <br>
                            @endforeach
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ ucfirst($membership->stateName()) }}</td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @if($membership->current_term_ends_at)
                                {{ Carbon\Carbon::parse($membership->current_term_ends_at)->format('d-m-Y') }}
                            @endif
                        </td>


                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <div class="space-x-1 flex items-center">

                                <x-dynamic-table-buttons type="show"
                                                         :route="route('admin.membership.show', $membership->id)" />

                                <x-dynamic-table-buttons type="edit"
                                                         :route="route('admin.membership.edit', $membership->id)" />

                                <x-dynamic-table-buttons type="delete"
                                                         method="DELETE"
                                                         :route="route('admin.membership.delete', $membership->id)" />

                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-dynamic-table>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{$memberships->links()}}
        </div>

    </div>
</x-layout>
