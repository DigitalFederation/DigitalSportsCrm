<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('certifications.index.title') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

            </div>

        </div>

        <div class="sm:flex flex-row gap-4">


            <x-utility.card-total :title="__('certifications.index.title')"
                                  :count="$certifications_attributed->total()"></x-utility.card-total>

            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('individual.certification-attributed.index')">
                @if(!empty(Request::query()['filter']['committee']))
                    <input type="hidden" name="filter[committee]" value="{{ Request::query()['filter']['committee'] }}">
                @endif
                <x-forms.filter-input-select :label="__('certifications.index.filters.certification')" name="filter_certification"
                                             :options="$certifications" />
                <x-forms.filter-input-select :label="__('certifications.index.filters.federation')" name="filter_federation" :options="$federations" />
                @if(!empty(Request::query()['filter']['committee']) && Request::query()['filter']['committee'] == 'sport')
                    <x-forms.filter-input-select :label="__('certifications.index.filters.sport_commission')" name="filter_sport" :options="$sports" />
                @endif
                <x-forms.filter-input-select :label="__('certifications.index.filters.status')" name="filter_status" :options="$filter_status" />
                <x-forms.filter-input-date-range :label="__('certifications.index.filters.expiration_date')" nameStart="filter_expiration_start"
                                                 nameEnd="filter_expiration_end" />
                <x-forms.filter-input-date-range :label="__('certifications.index.filters.issue_date')" nameStart="filter_emission_start"
                                                 nameEnd="filter_emission_end" />
                @if((!empty(Request::query()['filter']['committee']) && Request::query()['filter']['committee'] != 'sport') || empty(Request::query()['filter']['committee']))
                    <x-forms.filter-input-text :label="__('certifications.index.filters.course_director_code')" name="filter_director_code" />
                @endif
                <x-forms.filter-input-select :label="__('certifications.index.filters.cmas_zone')" name="filter_zone" :options="$cmas_zones" />
            </x-filter-form>

        </div>

        <!-- More actions -->
        @if(Request::segment(3) != 'filter')
            <div class="mb-2 sm:mb-0">
                <h2 class="grow font-semibold text-slate-800 truncate">{{ __('certifications.index.latest_entries') }}</h2>
            </div>
        @endif

        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            <x-dynamic-table
                :headers="[__('certifications.index.table.certifications'), __('certifications.index.table.federation'), __('certifications.index.table.country'), __('certifications.index.table.status'), __('certifications.index.table.expire_date'), __('certifications.index.table.issue_date'), __('certifications.index.table.actions')]">
                @foreach($certifications_attributed as $certification_attributed)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $certification_attributed->certification_name?:$certification_attributed->certification->name }}</td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <div class="flex items-center"><img
                                    src="{{ asset('img/flags/' . strtolower($certification_attributed->federation?->country->iso ?? '') . '.svg') }}"
                                    alt="{{ $certification_attributed->federation?->country->name }}"
                                    class="w-4 h-4 mr-1">{{ $certification_attributed->federation_name }}</div>
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <div class="flex items-center"><img
                                    src="{{ asset('img/flags/' . strtolower($certification_attributed->federation?->country->iso ?? '') . '.svg') }}"
                                    alt="{{ $certification_attributed->federation?->country->name }}"
                                    class="w-4 h-4 mr-1">{{ $certification_attributed->federation?->country->name }}
                            </div>
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <x-tables.badge :status="ucwords($certification_attributed->stateName())"
                                            :color="$certification_attributed->stateColor()" />
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @if($certification_attributed->current_term_ends_at)
                                {{ date('d-m-Y', strtotime($certification_attributed->current_term_ends_at)) }}
                            @else
                                ---
                            @endif
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @if($certification_attributed->current_term_starts_at)
                                {{ date('d-m-Y', strtotime($certification_attributed->current_term_starts_at)) }}
                            @else
                                ---
                            @endif
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end">
                            <div class="flex justify-end">
                                <x-dynamic-table-buttons type="show"
                                                         :route="route('individual.certification-attributed.show', $certification_attributed->id)" />
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-dynamic-table>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{$certifications_attributed->links()}}
        </div>

    </div>
</x-layout>
