@section('title', __('licenses.licenses'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ $title }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                @php
                    // The listing's `committee` query filter uses lowercase aliases;
                    // map them to committee codes, then resolve the individual
                    // purchase route from config (App\Support\Committees) with a
                    // generic fallback so any configured committee works.
                    $filter = request()->query('filter', []);
                    $committeeAlias = $filter['committee'] ?? 'sport';
                    $aliasToCode = [
                        'sport' => 'SPORT',
                        'diving' => 'DIVINGSERVICES',
                        'international-diving' => 'DIVING',
                        'scientific' => 'SCIENTIFIC',
                    ];
                    $committeeCode = $aliasToCode[$committeeAlias] ?? null;
                    $purchaseRoute = ($committeeCode
                            ? \App\Support\Committees::purchaseRouteName($committeeCode, 'individual')
                            : null)
                        ?? \App\Support\Committees::defaultPurchaseRouteName('individual');
                @endphp
                <a href="{{ route($purchaseRoute) }}" class="btn btn-primary">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="ml-2">{{ __('licenses.Purchase License') }}</span>
                </a>
            </div>

        </div>

        @if(!empty($licenses) && $licenses->count() > 0)

            <div class="sm:flex sm:justify-center sm:items-center mb-5 mt-4">

                <x-dynamic-table
                    :headers="[
                    __('licenses.license'),
                    __('licenses.start_date'),
                    __('licenses.expiry_date'),
                    __('licenses.status'),
                ]">
                    @foreach($licenses as $license)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $license->license_name }}
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
                                <x-tables.badge :status="ucwords($license->stateName())"
                                                :color="$license->stateColor()" />
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>


            </div>
        @else
            <x-utility.no-data :inCard="true" />
        @endif

        <!-- Pagination -->
        <div class="mt-8">
            {{$licenses->links()}}
        </div>

    </div>
</x-layout>
