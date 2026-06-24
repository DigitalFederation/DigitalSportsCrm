@section('title', __('admin.my_certification_cards'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- International Header -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6 flex items-center">
            <div>
                <h2 class="text-lg font-semibold text-blue-900 dark:text-blue-100">{{ __('admin.international_certifications') }}</h2>
                <p class="text-sm text-blue-700 dark:text-blue-300">{{ __('admin.diving_scientific_certifications') }}</p>
            </div>
        </div>

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('admin.my_certification_cards') }}</h1>
                <p class="text-slate-600 dark:text-slate-400">{{ __('admin.download_certification_cards') }}</p>
            </div>
        </div>

        <x-information-box
            :title="__('admin.information')"
            :body="__('admin.certifications_information')" />

        @if(!empty($certifications_attributed) && $certifications_attributed->count() > 0)
            @foreach($certifications_attributed as $category => $certifications)
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4 border-b pb-2">
                        {{ $category }}
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($certifications as $certification)
                            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-4 hover:shadow-lg transition-shadow">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-slate-900 dark:text-slate-100 text-sm">
                                            {{ $certification->certification->name }}
                                        </h4>
                                        @if($certification->certification->committee)
                                            <span class="inline-block mt-1 px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                {{ $certification->certification->committee->name }}
                                            </span>
                                        @endif
                                    </div>
                                    <x-tables.badge :status="ucwords($certification->stateName())"
                                                    :color="$certification->stateColor()"
                                                    class="text-xs" />
                                </div>

                                <div class="text-xs text-slate-600 dark:text-slate-400 space-y-1 mb-3">
                                    @if($certification->current_term_starts_at)
                                        <p>{{ __('admin.issued') }}: {{ $certification->current_term_starts_at->format('d/m/Y') }}</p>
                                    @endif
                                    @if($certification->current_term_ends_at)
                                        <p>{{ __('admin.expires') }}: {{ $certification->current_term_ends_at->format('d/m/Y') }}</p>
                                    @endif
                                    @if($certification->international_code)
                                        <p>{{ __('admin.code') }}: {{ $certification->international_code }}</p>
                                    @endif
                                </div>

                                <div class="flex gap-2">
                                    <a href="{{ route('international.individual.certification-card.show', $certification->id) }}"
                                       class="flex-1 btn btn-secondary text-xs py-1">
                                        {{ __('admin.view') }}
                                    </a>
                                    <a href="{{ route('international.individual.certification-card.download', $certification->id) }}"
                                       class="flex-1 btn btn-primary text-xs py-1">
                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        {{ __('admin.download') }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            <div class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-slate-900 dark:text-slate-100">{{ __('admin.no_certifications') }}</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('admin.no_certifications_description') }}</p>
            </div>
        @endif

    </div>
</x-layout>
