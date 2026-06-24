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
                @if($holderType === 'individual')
                    <a class="btn btn-primary"
                       href="{{ route('admin.license-attributed.create', ['individual', $committee]) }}">
                        <span class="ml-2">{{ __('licenses.assign_individual_license') }}</span>
                    </a>
                @else
                    <a class="btn btn-info"
                       href="{{ route('admin.license-attributed.create', ['entity', $committee]) }}">
                        <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                            <path
                                d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                        </svg>
                        <span class="ml-2">{{ __('licenses.assign_entity_license') }}</span>
                    </a>
                @endif
            </div>

        </div>

        <!-- FILTER RESULTS -->
        <div class="sm:flex flex-row gap-4 transition ease-in-out duration-300">
            <x-utility.card-total :title="__('licenses.licenses_title')" :count="$licenses->total()"></x-utility.card-total>

            @php
                $currentRouteName = Route::currentRouteName();
                $showSport = ($committee === 'SPORT');
                $showPaymentStatus = ($isInternational || $committee === 'SPORT');
            @endphp
            <x-filter-form :post="route($currentRouteName)">

                @if($holderType === 'individual' && ($isInternational || $committee === 'SPORT'))
                    {{-- Individual licenses (international or sport): simplified filters --}}
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
                @elseif($holderType === 'entity' && $isInternational)
                    {{-- Entity international licenses (DIVING/SCIENTIFIC): simplified filters --}}
                    <x-forms.filter-input-select label="{{ __('licenses.license') }}" name="filter_license" :options="$availableLicenses" />
                    <x-forms.filter-input-text label="{{ __('licenses.filters.entity_name') }}" name="filter_entity_name" />
                    <x-forms.filter-input-date-range label="{{ __('licenses.issue_date') }}" nameStart="filter_emission_start"
                                                     nameEnd="filter_emission_end" />
                    <x-forms.filter-input-date-range label="{{ __('licenses.expiration_date') }}" nameStart="filter_expiration_start"
                                                     nameEnd="filter_expiration_end" />
                    <x-forms.filter-input-select label="{{ __('licenses.status') }}" name="filter_status" :options="$filter_status" />
                    <x-forms.filter-input-select label="{{ __('licenses.payment_status') }}" name="filter_payment_status" :options="$filter_payment_status" />
                @elseif($holderType === 'entity' && $committee === 'SPORT')
                    {{-- Sport entity licenses: simplified filters --}}
                    <x-forms.filter-input-select label="{{ __('licenses.license') }}" name="filter_license" :options="$availableLicenses" />
                    <x-forms.filter-input-select label="{{ __('licenses.filters.sport') }}" name="filter_sport" :options="$sports" />
                    <x-forms.filter-input-text label="{{ __('licenses.filters.entity_name') }}" name="filter_entity_name" />
                    <x-forms.filter-input-date-range label="{{ __('licenses.issue_date') }}" nameStart="filter_emission_start"
                                                     nameEnd="filter_emission_end" />
                    <x-forms.filter-input-date-range label="{{ __('licenses.expiration_date') }}" nameStart="filter_expiration_start"
                                                     nameEnd="filter_expiration_end" />
                    <x-forms.filter-input-select label="{{ __('licenses.status') }}" name="filter_status" :options="$filter_status" />
                    <x-forms.filter-input-select label="{{ __('licenses.payment_status') }}" name="filter_payment_status" :options="$filter_payment_status" />
                @else
                    {{-- Other entity pages filters (DIVINGSERVICES) --}}
                    <x-forms.filter-input-text label="{{ __('licenses.name') }}" name="filter_name" />

                    <x-forms.filter-input-select label="{{ __('federations.federation') }}" name="filter_federation" :options="$federations" />

                    <x-forms.filter-input-select label="{{ __('main.country') }}" name="filter_country" :options="$countries" />

                    <x-forms.filter-input-text label="{{ __('main.Member Code') }}" name="filter_member_code" />

                    <x-forms.filter-input-date-range label="{{ __('licenses.issue_date') }}" nameStart="filter_expiration_start"
                                                     nameEnd="filter_expiration_end" />

                    <x-forms.filter-input-select label="{{ __('licenses.status') }}" name="filter_status" :options="$filter_status" />
                @endif

            </x-filter-form>
        </div>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">

            @php
                $headers = [__('licenses.license')];

                if ($showSport) {
                    $headers[] = __('licenses.filters.sport');
                }

                $headers[] = __('main.name');

                // Extra columns for the default (DIVINGSERVICES) table
                $isDefaultTable = !($isInternational || $committee === 'SPORT');
                if ($isDefaultTable) {
                    $headers[] = $holderType === 'individual' ? __('individuals.member_number') : __('entities.nif');
                    $headers[] = $holderType === 'individual' ? __('individuals.id_number') : __('entities.entity_type');
                } elseif ($holderType === 'individual' && $committee === 'SPORT') {
                    $headers[] = __('individuals.member_number');
                }

                $headers[] = __('licenses.start_date');
                $headers[] = __('licenses.expiry_date');
                $headers[] = __('licenses.status');

                if ($showPaymentStatus) {
                    $headers[] = __('licenses.payment_status');
                }

                $headers[] = __('licenses.actions');
            @endphp

            <x-dynamic-table :headers="$headers">
                @foreach($licenses as $license)
                    @include('web.admin.license_attributed._license-row', [
                        'license' => $license,
                        'holderType' => $holderType,
                        'showSport' => $showSport,
                        'showPaymentStatus' => $showPaymentStatus,
                        'extraColumns' => $isDefaultTable
                            ? view('web.admin.license_attributed._license-extra-columns', [
                                'license' => $license,
                                'holderType' => $holderType,
                            ])
                            : ($holderType === 'individual' && $committee === 'SPORT'
                                ? view('web.admin.license_attributed._license-member-number-column', [
                                    'license' => $license,
                                ])
                                : ''),
                    ])
                @endforeach
            </x-dynamic-table>

        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $licenses->links() }}
        </div>

    </div>
</x-layout>
