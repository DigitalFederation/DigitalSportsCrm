<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('certifications.index.title') }}</h1>
            </div>


        </div>

        <div class="sm:flex flex-row gap-4">
            <x-utility.card-total title="Certifications"
                                  :count="$certifications_attributed->total()"></x-utility.card-total>

            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('admin.certification-attributed.index')">
                @if(Request::query('filter.committee'))
                    <input type="hidden" name="filter[committee]" value="{{ Request::query('filter.committee') }}">
                @endif

                <x-forms.filter-input-select label="{{ __('certifications.index.filters.certification') }}" name="filter_certification"
                                             :options="$certifications" />
                <x-forms.filter-input-text label="{{ __('certifications.index.filters.student_name') }}" name="filter_student_name" />
                <x-forms.filter-input-text label="{{ __('certifications.index.filters.student_surname') }}" name="filter_student_surname" />
                <x-forms.filter-input-text label="{{ __('certifications.index.filters.course_director_member_number') }}" name="filter_director_member_number" />
                <x-forms.filter-input-select label="{{ __('certifications.index.filters.status') }}" name="filter_status" :options="$filter_status" />
                <x-forms.filter-input-select label="{{ __('certifications.index.filters.payment_status') }}" name="filter_payment_status" :options="$filter_payment_status" />
                <x-forms.filter-input-select label="{{ __('certifications.index.filters.entity') }}" name="filter_entity" :options="$entities" />
                @if(Request::query('filter.committee') == 'sport')
                    <x-forms.filter-input-select label="{{ __('certifications.index.filters.sport_commission') }}" name="filter_sport" :options="$sports" />
                @endif
                <x-forms.filter-input-date-range label="{{ __('certifications.index.filters.issue_date') }}" nameStart="filter_emission_start"
                                                 nameEnd="filter_emission_end" />
                <x-forms.filter-input-date-range label="{{ __('certifications.index.filters.expiration_date') }}" nameStart="filter_expiration_start"
                                                 nameEnd="filter_expiration_end" />
            </x-filter-form>

        </div>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">

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
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $certification_attributed->certification_name ?: $certification_attributed->certification->name }}</td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $certification_attributed->national_code ?? '---' }}</td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            {{ $certification_attributed->holder_name ?: ($certification_attributed->individual?->name ?? '---') }}
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            {{ $certification_attributed->entity?->name ?? '---' }}
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
                                                         :route="route('admin.certification-attributed.edit', $certification_attributed->id)" />
                                <x-dynamic-table-buttons type="show"
                                                         :route="route('admin.certification-attributed.show', $certification_attributed->id)" />
                                <x-dynamic-table-buttons type="delete"
                                                         method="DELETE"
                                                         :route="route('admin.certification-attributed.delete', $certification_attributed->id)" />


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
