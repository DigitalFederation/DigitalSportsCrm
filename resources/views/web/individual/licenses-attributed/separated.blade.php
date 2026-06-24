@section('title', $pageTitle)
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ $pageTitle }}</h1>
                @if(isset($pageSubtitle))
                    <p class="text-slate-600">{{ $pageSubtitle }}</p>
                @endif
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                @php
                    // Resolve the individual purchase route for this committee from
                    // config/committees.php (see App\Support\Committees), with a
                    // generic fallback so any configured committee works.
                    $purchaseRoute = \App\Support\Committees::purchaseRouteName($committee, 'individual')
                        ?? \App\Support\Committees::defaultPurchaseRouteName('individual');
                @endphp
                <a href="{{ route($purchaseRoute) }}" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    {{ __('licenses.Purchase License') }}
                </a>
            </div>

        </div>

        @php
            // Determine the correct info text based on committee
            $infoText = match(true) {
                $committee === 'DIVINGSERVICES' && !$isInternational => __('licenses.individual_national_diving_licenses_info'),
                $committee === 'DIVING' && $isInternational => __('licenses.individual_cmas_diving_licenses_info'),
                $committee === 'SCIENTIFIC' && $isInternational => __('licenses.individual_scientific_licenses_info'),
                default => __('licenses.individual_licenses_info'),
            };
        @endphp
        <x-information-box
            title="{{ __('Information') }}"
            :body="$infoText" />

        @if(!empty($licenses) && $licenses->count() > 0)

            <div class="sm:flex sm:justify-center sm:items-center mb-5 mt-4">

                <x-dynamic-table
                    :headers="array_values(array_filter([
                    __('licenses.license'),
                    (($committee !== 'DIVING' || !$isInternational) && $committee !== 'DIVINGSERVICES') ? __('licenses.sport') : null,
                    $committee === 'SPORT' ? __('licenses.license_number') : __('licenses.category'),
                    __('licenses.start_date'),
                    __('licenses.expiry_date'),
                    __('licenses.status'),
                ]))">
                    @foreach($licenses as $license)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                {{ $license->license_name }}
                            </td>

                            @if(($committee !== 'DIVING' || !$isInternational) && $committee !== 'DIVINGSERVICES')
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    {{ $license->license?->sport?->translated_name ?? '-' }}
                                </td>
                            @endif

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                @if($committee === 'SPORT')
                                    {{ $license->license_number ?? '-' }}
                                @else
                                    {{ $license->license?->professionalRole?->name ?? '-' }}
                                @endif
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                @if($license->current_term_starts_at)
                                    {{ $license->current_term_starts_at->format('d/m/Y') }}
                                @elseif($license->date_begin)
                                    {{ $license->date_begin->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                @if($license->current_term_ends_at)
                                    {{ $license->current_term_ends_at->format('d/m/Y') }}
                                @elseif($license->date_expire)
                                    {{ $license->date_expire->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <x-tables.badge :status="ucwords($license->stateName())"
                                                :color="$license->stateColor()" />
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>


            </div>
        @else
            <x-utility.no-data></x-utility.no-data>
        @endif

        <!-- Pagination -->
        <div class="mt-8">
            {{ $licenses->links() }}
        </div>

    </div>
</x-layout>
