<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ $title }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                @if((!empty(Request::query()['filter']['filter_holder_type']) && Request::query()['filter']['filter_holder_type'] == 'individual') || empty(Request::query()['filter']['filter_holder_type']))
                    <a class="btn btn-primary"
                       href="{{ route(Request::segment(1).'.license-attributed.create', !empty(Request::query()['filter']) && !empty(Request::query()['filter']['committee']) ? ['individual', Request::query()['filter']['committee']] : ['individual', 'diving']) }}">
                        {{ __('Assign Individual License') }}
                    </a>
                @endif

                @if((!empty(Request::query()['filter']['filter_holder_type']) && Request::query()['filter']['filter_holder_type'] == 'entity') || empty(Request::query()['filter']['filter_holder_type']))
                    <a class="btn btn-info"
                       href="{{ route(Request::segment(1).'.license-attributed.create', !empty(Request::query()['filter']) && !empty(Request::query()['filter']['committee']) ? ['entity', Request::query()['filter']['committee']] : ['entity', 'diving']) }}">
                        <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                            <path
                                d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                        </svg>
                        <span class="ml-2">{{ __('Assign Entity License') }}</span>
                    </a>
                @endif
            </div>

        </div>


        <!-- FILTER RESULTS -->
        <div class="sm:flex flex-row gap-4 transition ease-in-out duration-300">
            <x-utility.card-total title="Licenses" :count="$licenses->total()"></x-utility.card-total>
            <x-filter-form :post="route('admin.license-attributed.index')">
                @if(!empty(Request::query()['filter']['committee']))
                    <input type="hidden" name="filter[committee]"
                           value="{{ Request::query()['filter']['committee'] }}">
                @endif

                @if(!empty(Request::query()['filter']['filter_holder_type']))
                    <input type="hidden" name="filter[filter_holder_type]"
                           value="{{ Request::query()['filter']['filter_holder_type'] }}">
                @endif

                <x-forms.filter-input-text label="License" name="filter_name" />
                <x-forms.filter-input-select label="Federation" name="filter_federation" :options="$federations" />
                @if((!empty(Request::query()['filter']['committee']) && Request::query()['filter']['committee'] == 'sport') && (!empty(Request::query()['filter']['filter_holder_type']) && Request::query()['filter']['filter_holder_type'] == 'individual'))
                    <x-forms.filter-input-select label="Nationality" name="filter_country" :options="$countries" />
                @else
                    <x-forms.filter-input-select label="Country" name="filter_country" :options="$countries" />
                @endif
                <x-forms.filter-input-text label="{{ __('main.Member Code') }}" name="filter_member_code" />
                @if(!empty(Request::query()['filter']['committee']) && Request::query()['filter']['committee'] == 'sport')
                    <x-forms.filter-input-select label="Sport Commission"
                                                 name="filter_sport"
                                                 :options="$sports" />
                    <x-forms.filter-input-select label="Sport Categories" name="filter_category"
                                                 :options="$professional_roles" />
                @endif
                <x-forms.filter-input-date-range label="Issue date" nameStart="filter_expiration_start"
                                                 nameEnd="filter_expiration_end" />
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
                :headers="[
                    __('licenses.license'),
                    __('main.name'),
                    __('individuals.member_number'),
                    __('individuals.id_number'),
                    __('licenses.start_date'),
                    __('licenses.expiry_date'),
                    __('licenses.status'),
                    __('licenses.actions')
                ]">
                @foreach($licenses as $license)
                    @php
                        $owner = $license->owner;
                        $isIndividual = $license->model_type === 'individual';
                    @endphp
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            {{ $license->license_name }}
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @if($isIndividual && $owner)
                                <a href="{{ route('admin.individual.show', $owner->id) }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ $owner->name }} {{ $owner->surname }}
                                </a>
                            @else
                                {{ $license->holder_name }}
                            @endif
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            {{ $isIndividual && $owner ? ($owner->member_number ?? '-') : '-' }}
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            {{ $isIndividual && $owner ? ($owner->doc_ref ?? '-') : '-' }}
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @if($license->date_begin)
                                {{ $license->date_begin->format('d/m/Y') }}
                            @elseif($license->current_term_starts_at)
                                {{ $license->current_term_starts_at->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @if($license->date_expire)
                                {{ $license->date_expire->format('d/m/Y') }}
                            @elseif($license->current_term_ends_at)
                                {{ $license->current_term_ends_at->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <x-tables.badge :status="ucfirst($license->stateName())" :color="$license->stateColor()" />
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <div class="space-x-1 flex justify-end">
                                <x-dynamic-table-buttons type="show"
                                                         :route="route('admin.license-attributed.show', $license->id)" />
                                <x-dynamic-table-buttons type="delete"
                                                         :route="route('admin.license-attributed.delete', $license->id)"
                                                         method="DELETE" />
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-dynamic-table>

        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{$licenses->links()}}
        </div>

    </div>
</x-layout>
