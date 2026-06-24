<x-layout>
    <div>

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-6">
            <!-- Left: Title with count badge -->
            <div class="mb-4 sm:mb-0">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                        {{ !empty(request()->filter) && request()->filter['filter_status'] == 'active' ? __('certifications.validate.issued_certifications') : __('certifications.validate.certifications_requests') }}
                    </h1>
                    @if($certifications_validate->total() > 0)
                        <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300">
                            {{ $certifications_validate->total() }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Right: Export buttons -->
            <div class="flex items-center gap-2">
                <a href="{{ route('individual.certification-validate.export-excel', request()->query()) }}"
                   class="inline-flex items-center gap-2 px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                    {{ __('certifications.validate.export.excel') }}
                </a>
                <a href="{{ route('individual.certification-validate.export-pdf', request()->query()) }}"
                   class="inline-flex items-center gap-2 px-3 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <x-heroicon-o-document-arrow-down class="w-4 h-4" />
                    {{ __('certifications.validate.export.pdf') }}
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Total Certifications -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('certifications.validate.total_certifications') }}</p>
                        <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $totalCertifications }}</p>
                    </div>
                </div>
            </div>

            <!-- Last 5 Years -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('certifications.validate.last_five_years') }}</p>
                        <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $totalLastFiveYears }}</p>
                    </div>
                </div>
            </div>

            <!-- Director / Assistant -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-amber-50 dark:bg-amber-900/30 rounded-lg">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('certifications.validate.course_director_assistant') }}</p>
                        <p class="text-2xl font-semibold text-slate-900 dark:text-white">
                            {{ $totalAsDirector }} <span class="text-sm font-normal text-slate-400 dark:text-slate-500">/</span> {{ $totalAsAssistant }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6">
            <x-filter-form :post="route('individual.certification-validate.index')">
                @if(!empty(Request::query()['filter']['committee']))
                    <input type="hidden" name="filter[committee]" value="{{ Request::query()['filter']['committee'] }}">
                @endif
                <x-forms.filter-input-select :label="__('certifications.index.filters.certification')" name="filter_certification"
                                             :options="$certifications" />
                @if(!empty(Request::query()['filter']['committee']) && Request::query()['filter']['committee'] == 'sport')
                    <x-forms.filter-input-select :label="__('certifications.index.filters.sport_commission')" name="filter_sport" :options="$sports" />
                @endif
                <x-forms.filter-input-select :label="__('certifications.index.filters.status')" name="filter_status" :options="$filter_status" />

                <x-forms.filter-input-date-range :label="__('certifications.index.filters.issue_date')" nameStart="filter_emission_start"
                                                 nameEnd="filter_emission_end" />

                <x-forms.filter-input-text :label="__('certifications.validate.table.student')" name="filter_individual" />
                <x-forms.filter-input-select :label="__('certifications.index.filters.committee')" name="filter_committee" :options="$filter_comittee" />
            </x-filter-form>
        </div>

        <!-- Main Card Container -->
        <div class="bg-white dark:bg-slate-800 shadow-sm rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
            @if($certifications_validate->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-600">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-700/50">
                                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    {{ __('certifications.validate.table.certification') }}
                                </th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    {{ __('certifications.validate.table.entity') }}
                                </th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    {{ __('certifications.validate.table.student') }}
                                </th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    {{ __('certifications.validate.table.issue_date') }}
                                </th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    {{ __('certifications.validate.table.status') }}
                                </th>
                                <th scope="col" class="relative px-5 py-3">
                                    <span class="sr-only">{{ __('main.actions') }}</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-600">
                            @foreach($certifications_validate as $certification)
                                @php
                                    $colorClasses = match($certification->stateColor()) {
                                        'active-state' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                        'pending-state' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                        'canceled-state' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
                                        default => 'bg-slate-50 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="px-5 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-white">
                                        {{ $certification->certification_name }}
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                        {{ $certification->entity_name ?? config('branding.international.name', 'International Federation') }}
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-white">
                                        {{ $certification->holder_name }}
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                        {{ $certification->activated_at ? \Carbon\Carbon::parse($certification->activated_at)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $colorClasses }}">
                                            {{ ucfirst($certification->stateName()) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('individual.certification-attributed.show', $certification->id) }}"
                                           class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors inline-flex"
                                           title="{{ __('Show') }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($certifications_validate->hasPages())
                    <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-700">
                        {{ $certifications_validate->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-700 mb-4">
                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-1">
                        {{ __('certifications.validate.no_certifications') }}
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ __('certifications.validate.no_certifications_description') }}
                    </p>
                </div>
            @endif
        </div>

    </div>
</x-layout>
