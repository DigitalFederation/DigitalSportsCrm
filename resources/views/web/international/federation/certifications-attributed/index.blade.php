@section('title', __('admin.manage_international_certifications'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- International Header -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6 flex items-center">
            <div>
                <h2 class="text-lg font-semibold text-blue-900 dark:text-blue-100">{{ __('admin.international_certifications_management') }}</h2>
                <p class="text-sm text-blue-700 dark:text-blue-300">{{ __('admin.manage_federation_international_certifications') }}</p>
            </div>
        </div>

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('admin.international_certifications') }}</h1>
                <p class="text-slate-600 dark:text-slate-400">{{ __('admin.federation_certifications_description') }}</p>
            </div>
        </div>

        @if(!empty($certifications_attributed) && $certifications_attributed->count() > 0)
            <div class="sm:flex sm:justify-center sm:items-center mb-5 mt-4">
                <x-dynamic-table
                    :headers="[
                        __('admin.certification'),
                        __('admin.holder'),
                        __('admin.committee'),
                        __('admin.issued_date'),
                        __('admin.expiry_date'),
                        __('admin.member_code'),
                        __('admin.status'),
                        __('admin.actions'),
                    ]">
                    @foreach($certifications_attributed as $certification)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <a href="{{ route('international.federation.certifications-attributed.show', $certification->id) }}"
                                   class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ $certification->certification->name ?? '-' }}
                                </a>
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $certification->holder_name ?? '-' }}
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                @if($certification->certification && $certification->certification->committee)
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $certification->certification->committee->name }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                @if($certification->current_term_starts_at)
                                    {{ $certification->current_term_starts_at->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                @if($certification->current_term_ends_at)
                                    {{ $certification->current_term_ends_at->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                {{ $certification->international_code ?? '-' }}
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <x-tables.badge :status="ucwords($certification->stateName())"
                                                :color="$certification->stateColor()" />
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-right">
                                <a href="{{ route('international.federation.certifications-attributed.show', $certification->id) }}"
                                   class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ __('admin.view') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>
            </div>

            <div class="mt-8">
                {{ $certifications_attributed->links() }}
            </div>
        @else
            <div class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-slate-900 dark:text-slate-100">{{ __('admin.no_certifications_found') }}</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('admin.no_federation_certifications_description') }}</p>
            </div>
        @endif

    </div>
</x-layout>
