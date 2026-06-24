@section('title', __('admin.my_international_licenses'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('admin.my_international_licenses') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('international.individual.license-purchase.index', ['committee' => $committee ?? 'diving']) }}"
                   class="btn btn-primary">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="ml-2">{{ __('admin.purchase_license') }}</span>
                </a>
            </div>

        </div>

        @if(!empty($licenses) && $licenses->count() > 0)
            <div class="sm:flex sm:justify-center sm:items-center mb-5 mt-4">
                <x-dynamic-table
                    :headers="[
                        __('admin.license'),
                        __('admin.committee'),
                        __('admin.start_date'),
                        __('admin.expiry_date'),
                        __('admin.status'),
                        __('common.actions'),
                    ]">
                    @foreach($licenses as $license)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $license->license_name }}
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                @if($license->license && $license->license->committee)
                                    {{ $license->license->committee->name }}
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
                                <x-tables.badge :status="ucwords($license->stateName())"
                                                :color="$license->stateColor()" />
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="flex justify-end">
                                    <x-dynamic-table-buttons type="show"
                                                             :route="route('international.individual.licenses-attributed.show', $license->id)" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $licenses->links() }}
            </div>
        @else
            <x-utility.no-data :title="__('admin.no_licenses')" :inCard="true" />
        @endif

    </div>
</x-layout>
