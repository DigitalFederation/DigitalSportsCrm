<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('Certifications') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-primary flex items-center" href="{{ route('entity.certification-attributed.wizard.create', ['filter' => ['committee' => $currentCommittee]]) }}">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z"/>
                    </svg>
                    <span class="ml-2">{{ __('Assign Certification') }}</span>
                </a>
            </div>
        </div>


        <div class="sm:flex flex-row gap-4">

            <!-- FILTER RESULTS -->
            <x-utility.card-total title="Certifications" :count="$certifications_attributed->total()"></x-utility.card-total>

            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('federation.certification-attributed.index')">
                <input type="hidden" name="filter[committee]" value="{{ request()->filter['committee'] ?? null }}">

                <x-forms.filter-input-select label="Certification" name="filter_certification" :options="$certifications"/>
                @if(!empty(Request::query()['filter']['committee']) && Request::query()['filter']['committee'] == 'sport')
                    <x-forms.filter-input-select label="Sport Comission" name="filter_sport" :options="$sports"/>
                @endif
                <x-forms.filter-input-select label="Status" name="filter_status" :options="$filter_status"/>
                <x-forms.filter-input-date-range label="Expiration date" nameStart="filter_expiration_start" nameEnd="filter_expiration_end"/>
                <x-forms.filter-input-date-range label="Issue Date" nameStart="filter_emission_start" nameEnd="filter_emission_end"/>
                @if((!empty(Request::query()['filter']['committee']) && Request::query()['filter']['committee'] != 'sport') || empty(Request::query()['filter']['committee']))
                    <x-forms.filter-input-text label="Course Director Code" name="filter_director_code"/>
                @endif
            </x-filter-form>
        </div>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            <!-- Table -->

            <!-- Table -->
            <x-dynamic-table
                :headers="[
                        __('certifications.certification'),
                        __('certifications.student'),
                        __('certifications.status'),
                        __('certifications.issue_date'),
                        __('certifications.course_director'),
                        ''
                    ]">
                @foreach($certifications_attributed as $certification_attributed)
                    <tr>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            {{ $certification_attributed->national_code }} | {{ $certification_attributed->certification_name }}
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <a class="hover:text-blue-500" href="{{ route('federation.individual.show', $certification_attributed->individual_id) }}">{{ $certification_attributed->holder_name }}</a>
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <x-tables.badge :status="ucfirst($certification_attributed->stateName())" :color="$certification_attributed->stateColor()" />
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $certification_attributed->current_term_starts_at ? date('d-m-Y', strtotime($certification_attributed->current_term_starts_at)) : '---' }}</td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            {{ $certification_attributed->mainInstructor?->first()?->native_name }}
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end">
                            <div class="space-x-1 flex justify-end items-end">

                                <x-dynamic-table-buttons type="show"
                                                         :route="route(Request::segment(1).'.certification-attributed.show', $certification_attributed->id)"/>
                                @if($certification_attributed->stateName() == 'pending')

                                    <x-dynamic-table-buttons type="delete"
                                                             :route="route(Request::segment(1).'.certification-attributed.delete', $certification_attributed->id)"
                                                             method="DELETE"/>
                                @endif

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
