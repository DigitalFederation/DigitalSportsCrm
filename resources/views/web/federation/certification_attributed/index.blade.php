@section('title', __('certifications.certifications_title'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                @php
                    $pageTitle = match($filter['committee'] ?? null) {
                        'diving' => __('certifications.index.diving_certifications'),
                        'scientific' => __('certifications.index.scientific_certifications'),
                        default => __('certifications.certifications_title'),
                    };
                @endphp
                <h1 class="page-first-title">{{ $pageTitle }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <a class="btn btn-primary"
                   href="{{ route(Request::segment(1).'.certification-attributed.wizard.create', !empty($filter['committee']) ? ['filter[committee]' => $filter['committee']] : []) }}">
                    <span>{{ __('certifications.assign_certification') }}</span>
                </a>

                @if(!empty($filter['committee']))
                <livewire:federation-export-button
                    exportType="certifications_{{Request::query()['filter']['committee'] }}" />
                @endif
            </div>

        </div>

        <div class="sm:flex flex-row gap-4 w-full flex">


            <!-- FILTER RESULTS -->
            <x-utility.card-total title="Certifications"
                                  :count="$certifications_attributed->total()"></x-utility.card-total>

            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('federation.certification-attributed.index')">
                <input type="hidden" name="filter[committee]" value="{{ $filter['committee'] ?? null }}">

                <x-forms.filter-input-select label="Certification" name="filter_certification"
                                             :options="$certifications" />
                <x-forms.filter-input-text label="{{ __('certifications.index.filters.student_name') }}" name="filter_student_name" />
                <x-forms.filter-input-text label="{{ __('certifications.index.filters.student_surname') }}" name="filter_student_surname" />
                @if(!empty($filter['committee']) && $filter['committee'] == 'sport')
                    <x-forms.filter-input-select label="Sport Comission" name="filter_sport" :options="$sports" />
                @endif
                <x-forms.filter-input-select label="Status" name="filter_status" :options="$filter_status" />
                <x-forms.filter-input-date-range label="Expiration date" nameStart="filter_expiration_start"
                                                 nameEnd="filter_expiration_end" />
                <x-forms.filter-input-date-range label="Issue Date" nameStart="filter_emission_start"
                                                 nameEnd="filter_emission_end" />
                @if((!empty($filter['committee']) && $filter['committee'] != 'sport') || empty($filter['committee']))
                    <x-forms.filter-input-text label="{{ __('certifications.index.filters.course_director_member_number') }}" name="filter_director_member_number" />
                @endif
            </x-filter-form>


        </div>


        <div class="mb-5">

            @if(!empty($certifications_attributed) && $certifications_attributed->count() > 0)

                <!-- Table -->
                <x-dynamic-table
                    :headers="[
                        __('certifications.index.table.certifications'),
                        __('certifications.index.table.certification_number'),
                        __('certifications.index.table.student_name'),
                        __('certifications.index.table.entity'),
                        __('certifications.index.table.course_director'),
                        __('certifications.index.table.issue_date'),
                        __('certifications.index.table.expire_date'),
                        __('certifications.index.table.status'),
                        __('certifications.index.table.payment_status'),
                        __('certifications.index.table.type'),
                        __('certifications.index.table.actions'),
                    ]">
                    @foreach($certifications_attributed as $certification_attributed)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $certification_attributed->certification_name ?: $certification_attributed->certification->name }}
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $certification_attributed->national_code ?? '---' }}</td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <a class="hover:text-blue-500"
                                   href="{{ route('federation.individual.show', $certification_attributed->individual_id) }}">{{ $certification_attributed->holder_name ?: ($certification_attributed->individual?->name ?? '---') }}</a>
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                @if($certification_attributed->entity)
                                    <a class="hover:text-blue-500"
                                       href="{{ route('federation.entity.show', $certification_attributed->entity_id) }}">{{ $certification_attributed->entity->name }}</a>
                                @else
                                    ---
                                @endif
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                @php
                                    $mainInstructor = $certification_attributed->mainInstructor->first();
                                @endphp
                                @if($mainInstructor)
                                    {{ $mainInstructor->name }} {{ $mainInstructor->surname }}
                                @else
                                    ---
                                @endif
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                @if($certification_attributed->current_term_starts_at)
                                    {{ $certification_attributed->current_term_starts_at->format('d/m/Y') }}
                                @else
                                    ---
                                @endif
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                @if($certification_attributed->current_term_ends_at)
                                    {{ $certification_attributed->current_term_ends_at->format('d/m/Y') }}
                                @else
                                    ---
                                @endif
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white bg-{{ $certification_attributed->stateColor() }}">
                                    {{ ucfirst($certification_attributed->stateName()) }}
                                </span>
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <x-tables.payment-status-badge :status="$certification_attributed->payment_status" />
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                @if($certification_attributed->wantsPhysicalCard())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                        {{ __('certifications.index.table.card') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ __('certifications.index.table.digital') }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end">
                                <div class="space-x-1 flex justify-end items-end">
                                    <x-dynamic-table-buttons type="edit"
                                                             :route="route('federation.certification-attributed.edit', $certification_attributed->id)" />
                                    <x-dynamic-table-buttons type="show"
                                                             :route="route(Request::segment(1).'.certification-attributed.show', $certification_attributed->id)" />
                                    @if($certification_attributed->stateName() == 'pending')
                                        <x-dynamic-table-buttons type="delete"
                                                                 :route="route(Request::segment(1).'.certification-attributed.delete', $certification_attributed->id)"
                                                                 method="DELETE" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>
            @else
                <x-utility.no-data></x-utility.no-data>
            @endif
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{$certifications_attributed->links()}}
        </div>

    </div>
</x-layout>
