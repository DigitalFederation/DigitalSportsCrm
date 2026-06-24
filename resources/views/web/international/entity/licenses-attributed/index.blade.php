@section('title', __('admin.entity_international_licenses'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- International Header -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6 flex items-center">
            <div>
                <h2 class="text-lg font-semibold text-blue-900 dark:text-blue-100">{{ __('admin.international_licenses') }}</h2>
                <p class="text-sm text-blue-700 dark:text-blue-300">{{ __('admin.official_international_licenses') }}</p>
            </div>
        </div>

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('admin.entity_international_licenses') }}</h1>
                <p class="text-slate-600 dark:text-slate-400">{{ __('admin.manage_entity_international_licenses') }}</p>
            </div>

            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('international.entity.license-purchase.index', ['committee' => $committee ?? 'diving']) }}"
                   class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    {{ __('admin.purchase_license') }}
                </a>
            </div>
        </div>

        <x-information-box
            :title="__('admin.information')"
            :body="__('admin.entity_licenses_information')" />

        @if(!empty($licenses) && $licenses->count() > 0)
            <div class="sm:flex sm:justify-center sm:items-center mb-5 mt-4">
                <x-dynamic-table
                    :headers="[
                        __('admin.license'),
                        __('admin.holder'),
                        __('admin.committee'),
                        __('admin.start_date'),
                        __('admin.expiry_date'),
                        __('admin.status'),
                    ]">
                    @foreach($licenses as $license)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <a href="{{ route('international.entity.licenses-attributed.show', $license->id) }}"
                                   class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ $license->license_name }}
                                </a>
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $license->owner ? $license->owner->name : '-' }}
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                @if($license->license && $license->license->committee)
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $license->license->committee->name }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                @if($license->current_term_starts_at)
                                    {{ $license->current_term_starts_at->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                @if($license->current_term_ends_at)
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

            <div class="mt-8">
                {{ $licenses->links() }}
            </div>
        @else
            <div class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-slate-900 dark:text-slate-100">{{ __('admin.no_licenses') }}</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('admin.no_entity_licenses_description') }}</p>
                <div class="mt-6">
                    <a href="{{ route('international.entity.license-purchase.index') }}" class="btn btn-primary">
                        {{ __('admin.purchase_first_license') }}
                    </a>
                </div>
            </div>
        @endif

    </div>
</x-layout>
