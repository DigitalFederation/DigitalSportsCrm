@section('title', __('federation.club_licenses'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('federation.club_licenses') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                @php
                    $filterHolderType = Request::query('filter')['filter_holder_type'] ?? 'individual';
                    $committee = Request::query('filter')['committee'] ?? 'diving';
                @endphp
                <livewire:federation-export-button
                    exportType="licenses_{{ $filterHolderType }}_{{ $committee }}" />
            </div>

        </div>

        <div class="sm:flex flex-row gap-4">
            <x-utility.card-total :title="__('licenses.licenses_title')" :count="$licenses->total()"></x-utility.card-total>
            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('federation.license-attributed.index')">
                @if(!empty(Request::query()['filter']['committee']))
                    <input type="hidden" name="filter[committee]" value="{{ Request::query()['filter']['committee'] }}">
                @endif

                @if(!empty(Request::query()['filter']['filter_holder_type']))
                    <input type="hidden" name="filter[filter_holder_type]"
                           value="{{ Request::query()['filter']['filter_holder_type'] }}">
                @endif

                @if(!empty(Request::query()['filter']['filter_professional']))
                    <input type="hidden" name="filter[filter_professional]"
                           value="{{ Request::query()['filter']['filter_professional'] }}">
                @endif

                <x-forms.filter-input-text label="{{ __('licenses.name') }}" name="filter_name" />

                <x-forms.filter-input-text label="{{ __('main.Member Code') }}" name="filter_member_code" />
                @if(!empty(Request::query()['filter']['committee']) && Request::query()['filter']['committee'] == 'sport')
                    <x-forms.filter-input-select label="{{ __('licenses.sport_commission') }}" name="filter_sport" :options="$sports" />
                    <x-forms.filter-input-select label="{{ __('licenses.sport_categories') }}" name="filter_category"
                                                 :options="$professional_roles" />
                @endif
                <x-forms.filter-input-date-range label="{{ __('licenses.issue_date') }}" nameStart="filter_expiration_start"
                                                 nameEnd="filter_expiration_end" />
                <x-forms.filter-input-select label="{{ __('licenses.status') }}" name="filter_status" :options="$filter_status" />
            </x-filter-form>
        </div>

        @if(Request::segment(3) != 'filter')
            <div class="mb-2 sm:mb-0">
                <h2 class="grow font-semibold text-slate-800 truncate">{{ __('Latest entries') }}</h2>
            </div>
        @endif

        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            <!-- Table -->
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
                                <a href="{{ route('federation.individual.show', $owner->id) }}" class="text-blue-600 hover:text-blue-800 hover:underline">
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
                                                         :route="route('federation.license-attributed.show', $license->id)" />
                                <x-dynamic-table-buttons type="delete"
                                                         :route="route('federation.license-attributed.delete', $license->id)" />
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
