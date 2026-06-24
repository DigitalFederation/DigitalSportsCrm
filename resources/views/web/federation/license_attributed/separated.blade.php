@section('title', $pageTitle)
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ $pageTitle }}</h1>
                @if(!empty($pageSubtitle))
                    <p class="text-sm text-slate-500">{{ $pageSubtitle }}</p>
                @endif
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                @if($isDefaultFederation)
                    @if($holderType === 'individual')
                        <a class="btn btn-primary"
                           href="{{ route('federation.license-attributed.create', ['individual', $committee]) }}">
                            <span class="ml-2">{{ __('licenses.assign_individual_license') }}</span>
                        </a>
                    @else
                        <a class="btn btn-info"
                           href="{{ route('federation.license-attributed.create', ['entity', $committee]) }}">
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path
                                    d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                            </svg>
                            <span class="ml-2">{{ __('licenses.assign_entity_license') }}</span>
                        </a>
                    @endif
                @endif

                <!-- Export Button -->
                <livewire:federation-export-button
                    exportType="licenses_{{ $holderType }}_{{ $committee }}" />
            </div>

        </div>

        <div class="sm:flex flex-row gap-4">
            <x-utility.card-total :title="__('licenses.licenses_title')" :count="$licenses->total()"></x-utility.card-total>

            <!-- FILTER RESULTS -->
            @php
                $currentRouteName = Route::currentRouteName();
            @endphp
            <x-filter-form :post="route($currentRouteName)">

                @if($isDefaultFederation)
                    {{-- Main federation: admin-like context-dependent filters --}}
                    @if($holderType === 'individual' && ($isInternational || $committee === 'SPORT'))
                        <x-forms.filter-input-select label="{{ __('licenses.license') }}" name="filter_license" :options="$availableLicenses" />
                        @if($committee === 'SPORT')
                            <x-forms.filter-input-select label="{{ __('licenses.filters.sport') }}" name="filter_sport" :options="$sports" />
                        @endif
                        <x-forms.filter-input-text label="{{ __('licenses.filters.first_name') }}" name="filter_first_name" />
                        <x-forms.filter-input-text label="{{ __('licenses.filters.surname') }}" name="filter_surname" />
                        <x-forms.filter-input-text label="{{ __('licenses.filters.member_number') }}" name="filter_member_number" />
                        <x-forms.filter-input-date-range label="{{ __('licenses.issue_date') }}" nameStart="filter_emission_start"
                                                         nameEnd="filter_emission_end" />
                        <x-forms.filter-input-date-range label="{{ __('licenses.expiration_date') }}" nameStart="filter_expiration_start"
                                                         nameEnd="filter_expiration_end" />
                        <x-forms.filter-input-select label="{{ __('licenses.status') }}" name="filter_status" :options="$filter_status" />
                        <x-forms.filter-input-select label="{{ __('licenses.payment_status') }}" name="filter_payment_status" :options="$filter_payment_status" />
                    @elseif($holderType === 'entity' && $committee === 'SPORT')
                        <x-forms.filter-input-select label="{{ __('licenses.license') }}" name="filter_license" :options="$availableLicenses" />
                        <x-forms.filter-input-select label="{{ __('licenses.filters.sport') }}" name="filter_sport" :options="$sports" />
                        <x-forms.filter-input-text label="{{ __('licenses.filters.entity_name') }}" name="filter_entity_name" />
                        <x-forms.filter-input-date-range label="{{ __('licenses.issue_date') }}" nameStart="filter_emission_start"
                                                         nameEnd="filter_emission_end" />
                        <x-forms.filter-input-date-range label="{{ __('licenses.expiration_date') }}" nameStart="filter_expiration_start"
                                                         nameEnd="filter_expiration_end" />
                        <x-forms.filter-input-select label="{{ __('licenses.status') }}" name="filter_status" :options="$filter_status" />
                        <x-forms.filter-input-select label="{{ __('licenses.payment_status') }}" name="filter_payment_status" :options="$filter_payment_status" />
                    @elseif($holderType === 'entity' && $isInternational)
                        <x-forms.filter-input-select label="{{ __('licenses.license') }}" name="filter_license" :options="$availableLicenses" />
                        <x-forms.filter-input-text label="{{ __('licenses.filters.entity_name') }}" name="filter_entity_name" />
                        <x-forms.filter-input-date-range label="{{ __('licenses.issue_date') }}" nameStart="filter_emission_start"
                                                         nameEnd="filter_emission_end" />
                        <x-forms.filter-input-date-range label="{{ __('licenses.expiration_date') }}" nameStart="filter_expiration_start"
                                                         nameEnd="filter_expiration_end" />
                        <x-forms.filter-input-select label="{{ __('licenses.status') }}" name="filter_status" :options="$filter_status" />
                        <x-forms.filter-input-select label="{{ __('licenses.payment_status') }}" name="filter_payment_status" :options="$filter_payment_status" />
                    @else
                        {{-- DIVINGSERVICES national: basic filters --}}
                        <x-forms.filter-input-select label="{{ __('licenses.license') }}" name="filter_license" :options="$availableLicenses" />
                        <x-forms.filter-input-text label="{{ __('licenses.name') }}" name="filter_name" />
                        <x-forms.filter-input-date-range label="{{ __('licenses.issue_date') }}" nameStart="filter_emission_start"
                                                         nameEnd="filter_emission_end" />
                        <x-forms.filter-input-date-range label="{{ __('licenses.expiration_date') }}" nameStart="filter_expiration_start"
                                                         nameEnd="filter_expiration_end" />
                        <x-forms.filter-input-select label="{{ __('licenses.status') }}" name="filter_status" :options="$filter_status" />
                        <x-forms.filter-input-select label="{{ __('licenses.payment_status') }}" name="filter_payment_status" :options="$filter_payment_status" />
                    @endif
                @else
                    {{-- Non-main federation: original simpler filters --}}
                    <x-forms.filter-input-text label="{{ __('licenses.name') }}" name="filter_name" />

                    <x-forms.filter-input-text label="{{ __('main.Member Code') }}" name="filter_member_code" />

                    @if($holderType === 'individual' && isset($entities) && $entities->count() > 0)
                        <x-forms.filter-input-select label="{{ __('entities.entity') }}" name="filter_entity" :options="$entities" />
                    @endif

                    @if($committee === 'SPORT')
                        <x-forms.filter-input-select label="{{ __('licenses.sport_commission') }}" name="filter_sport" :options="$sports" />
                        <x-forms.filter-input-select label="{{ __('licenses.sport_categories') }}" name="filter_category"
                                                     :options="$professional_roles" />
                    @endif

                    <x-forms.filter-input-date-range label="{{ __('licenses.issue_date') }}" nameStart="filter_expiration_start"
                                                     nameEnd="filter_expiration_end" />

                    <x-forms.filter-input-select label="{{ __('licenses.status') }}" name="filter_status" :options="$filter_status" />
                @endif

            </x-filter-form>
        </div>

        @if(Request::segment(3) != 'filter')
            <div class="mb-2 sm:mb-0">
                <h2 class="grow font-semibold text-slate-800 truncate">{{ __('Latest entries') }}</h2>
            </div>
        @endif

        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            @if($isDefaultFederation)
                @php
                    $showSport = ($committee === 'SPORT');
                    $showPaymentStatus = true;

                    $headers = [__('licenses.license')];

                    if ($showSport) {
                        $headers[] = __('licenses.filters.sport');
                    }

                    $headers[] = __('main.name');

                    if ($holderType === 'individual' && $committee === 'SPORT') {
                        $headers[] = __('individuals.member_number');
                    }

                    $headers[] = __('licenses.start_date');
                    $headers[] = __('licenses.expiry_date');
                    $headers[] = __('licenses.status');
                    $headers[] = __('licenses.payment_status');
                    $headers[] = __('licenses.actions');
                @endphp

                <x-dynamic-table :headers="$headers">
                    @foreach($licenses as $license)
                        @php
                            $owner = $license->owner;
                            $isIndividual = $license->model_type === 'individual';
                        @endphp
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $license->license_name }}
                            </td>

                            @if($showSport)
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    {{ $license->license?->sport?->translated_name ?? '-' }}
                                </td>
                            @endif

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                @if($isIndividual && $owner)
                                    <a href="{{ route('federation.individual.show', $owner->id) }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                                        {{ $owner->name }} {{ $owner->surname }}
                                    </a>
                                @elseif(!$isIndividual && $owner)
                                    <a href="{{ route('federation.entity.show', $owner->id) }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                                        {{ $owner->name }}
                                    </a>
                                @else
                                    {{ $license->holder_name }}
                                @endif
                            </td>

                            @if($holderType === 'individual' && $committee === 'SPORT')
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    {{ $owner?->member_number ?? '-' }}
                                </td>
                            @endif

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
                                <x-tables.payment-status-badge :status="$license->payment_status" />
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
            @else
                <!-- Non-main federation: original table -->
                <x-dynamic-table
                    :headers="[
                        __('licenses.license'),
                        __('main.name'),
                        $holderType === 'individual' ? __('individuals.member_number') : __('entities.nif'),
                        $holderType === 'individual' ? __('individuals.id_number') : __('entities.entity_type'),
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
                                @elseif(!$isIndividual && $owner)
                                    <a href="{{ route('federation.entity.show', $owner->id) }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                                        {{ $owner->name }}
                                    </a>
                                @else
                                    {{ $license->holder_name }}
                                @endif
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                @if($isIndividual && $owner)
                                    {{ $owner->member_number ?? '-' }}
                                @elseif(!$isIndividual && $owner)
                                    {{ $owner->nif ?? '-' }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                @if($isIndividual && $owner)
                                    {{ $owner->doc_ref ?? '-' }}
                                @elseif(!$isIndividual && $owner)
                                    {{ $owner->entity_type ?? '-' }}
                                @else
                                    -
                                @endif
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
            @endif

        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $licenses->links() }}
        </div>

    </div>
</x-layout>
