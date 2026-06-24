@section('title', $template->name)
<x-layout>
    <div class="previous-layout-classes">

        <div class="space-y-6 mt-5">

            {{-- Header Card --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-6 sm:px-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        {{-- Left: Template Info --}}
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-100">
                                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $template->state_color }}-100 text-{{ $template->state_color }}-800">
                                    {{ __('event_applications.template_states.' . $template->state) }}
                                </span>
                            </div>
                            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">{{ $template->name }}</h1>
                            @if($template->description)
                                <p class="mt-1 text-gray-500 text-sm">{{ $template->description }}</p>
                            @endif

                            <div class="flex flex-wrap items-center gap-4 mt-3 text-sm text-gray-500">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    {{ __('event_applications.event_types.' . $template->event_type) }}
                                </span>
                                @if($template->sport)
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        {{ $template->sport->translated_name }}
                                    </span>
                                @endif
                                @if($template->createdBy)
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        {{ $template->createdBy->name }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Right: Start Application CTA --}}
                        @if($template->isOpen())
                            <div class="flex-shrink-0">
                                <form action="{{ route(($routeNamespace ?? 'entity') . '.event-applications.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="template_id" value="{{ $template->id }}">
                                    <input type="hidden" name="application_type" value="{{ \App\Enums\EventApplicationTypeEnum::FederationInitiated->value }}">
                                    <input type="hidden" name="event_name" value="{{ $template->name }}">
                                    <input type="hidden" name="event_type" value="{{ $template->event_type }}">
                                    <input type="hidden" name="sport_id" value="{{ $template->sport_id }}">
                                    <input type="hidden" name="event_category" value="{{ $template->event_category }}">
                                    <input type="hidden" name="start_date" value="{{ $template->event_start_date?->format('Y-m-d') }}">
                                    <input type="hidden" name="end_date" value="{{ $template->event_end_date?->format('Y-m-d') }}">

                                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white rounded-xl font-medium text-sm shadow-sm hover:bg-primary-700 focus:outline-none focus:ring focus:ring-primary-light/30 transition-colors duration-150">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        {{ __('event_applications.actions.start_application') }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>

                    {{-- Action Bar --}}
                    <div class="mt-5 pt-4 border-t border-gray-100 flex flex-wrap items-center gap-3">
                        <a href="{{ route(($routeNamespace ?? 'entity') . '.event-applications.available-templates') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg font-medium text-sm text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring focus:ring-gray-200/50 transition-colors duration-150">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            {{ __('event_applications.actions.back_to_templates') }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- Summary Cards Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">

                {{-- Submission Period Card --}}
                <div class="bg-white rounded-xl border-2 border-blue-200 bg-blue-50/30 p-4 sm:p-5">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-medium text-gray-500">{{ __('event_applications.sections.submission_period') }}</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ $template->submission_start_date?->format('d/m/Y') ?? '-' }}
                            </p>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $template->submission_end_date?->format('d/m/Y') ?? '-' }}
                            </p>
                            @if($template->submission_end_date?->isFuture())
                                <p class="mt-1 text-xs text-blue-600 font-medium">
                                    {{ $template->submission_end_date->diffForHumans() }}
                                </p>
                            @endif
                        </div>
                        <div class="flex-shrink-0 p-2.5 rounded-xl bg-blue-100">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Event Period Card --}}
                <div class="bg-white rounded-xl border-2 border-emerald-200 bg-emerald-50/30 p-4 sm:p-5">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-medium text-gray-500">{{ __('event_applications.sections.event_period') }}</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">
                                {{ $template->event_start_date?->format('d/m/Y') ?? '-' }}
                            </p>
                            <p class="text-sm font-semibold text-gray-900">
                                {{ $template->event_end_date?->format('d/m/Y') ?? '-' }}
                            </p>
                        </div>
                        <div class="flex-shrink-0 p-2.5 rounded-xl bg-emerald-100">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Max Applications Card --}}
                <div class="bg-white rounded-xl border-2 border-amber-200 bg-amber-50/30 p-4 sm:p-5">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-medium text-gray-500">{{ __('event_applications.labels.max_applications') }}</p>
                            <p class="mt-1 text-2xl font-bold text-gray-900 tabular-nums">
                                {{ $template->max_applications ?? __('event_applications.labels.unlimited') }}
                            </p>
                        </div>
                        <div class="flex-shrink-0 p-2.5 rounded-xl bg-amber-100">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Event Details Card --}}
                <div class="bg-white rounded-xl border-2 border-purple-200 bg-purple-50/30 p-4 sm:p-5">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-medium text-gray-500">{{ __('event_applications.labels.number_of_applications') }}</p>
                            <p class="mt-1 text-2xl font-bold text-gray-900 tabular-nums">{{ $template->applications_count }}</p>
                            @if($template->event_category)
                                <p class="mt-1 text-sm font-semibold text-gray-900">
                                    {{ \App\Enums\EvtEventOrganizationCategoryEnum::toString($template->event_category) }}
                                </p>
                            @endif
                        </div>
                        <div class="flex-shrink-0 p-2.5 rounded-xl bg-purple-100">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Event Information Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200 bg-gray-50/50">
                    <div class="px-6 py-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="font-semibold text-gray-700">{{ __('event_applications.sections.event_information') }}</span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">
                                {{ __('event_applications.labels.event_name') }}
                            </label>
                            <p class="text-sm font-medium text-gray-900">{{ $template->name }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">
                                {{ __('event_applications.labels.event_type') }}
                            </label>
                            <p class="text-sm font-medium text-gray-900">
                                {{ __('event_applications.event_types.' . $template->event_type) }}
                            </p>
                        </div>

                        @if($template->sport)
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">
                                    {{ __('event_applications.labels.sport') }}
                                </label>
                                <p class="text-sm font-medium text-gray-900">{{ $template->sport->translated_name }}</p>
                            </div>
                        @endif

                        @if($template->event_category)
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">
                                    {{ __('event_applications.labels.event_category') }}
                                </label>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ \App\Enums\EvtEventOrganizationCategoryEnum::toString($template->event_category) }}
                                </p>
                            </div>
                        @endif
                    </div>

                    @if($template->description)
                        <div class="mt-5 pt-5 border-t border-gray-100">
                            <label class="block text-xs font-medium text-gray-500 mb-2">
                                {{ __('event_applications.labels.description') }}
                            </label>
                            <div class="text-sm text-gray-700 prose prose-sm max-w-none">
                                {{ $template->description }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Documents Card --}}
            @if($template->documents->isNotEmpty())
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50/50">
                        <div class="px-6 py-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            <span class="font-semibold text-gray-700">{{ __('event_applications.sections.required_documents') }}</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            @foreach($template->documents as $document)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-gray-800">{{ $document->file_name }}</p>
                                            @if($document->file_size)
                                                <p class="text-xs text-gray-500">{{ number_format($document->file_size / 1024, 2) }} KB</p>
                                            @endif
                                        </div>
                                    </div>
                                    <a href="{{ route(($routeNamespace ?? 'entity') . '.application-documents.download', $document) }}"
                                       class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-300 rounded-lg font-medium text-xs text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring focus:ring-gray-200/50 transition-colors duration-150">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        {{ __('common.download') }}
                                    </a>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">
                                        {{ __('event_applications.help.download_documents') }}
                                    </h3>
                                    <p class="mt-1 text-xs text-blue-700">
                                        {{ __('event_applications.help.review_documents_before_applying') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Bottom CTA Bar --}}
            @if($template->isOpen())
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 sm:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <p class="text-sm text-gray-500">
                            {{ __('event_applications.help.ready_to_apply') }}
                        </p>
                        <form action="{{ route(($routeNamespace ?? 'entity') . '.event-applications.store') }}" method="POST" class="flex-shrink-0">
                            @csrf
                            <input type="hidden" name="template_id" value="{{ $template->id }}">
                            <input type="hidden" name="application_type" value="{{ \App\Enums\EventApplicationTypeEnum::FederationInitiated->value }}">
                            <input type="hidden" name="event_name" value="{{ $template->name }}">
                            <input type="hidden" name="event_type" value="{{ $template->event_type }}">
                            <input type="hidden" name="sport_id" value="{{ $template->sport_id }}">
                            <input type="hidden" name="event_category" value="{{ $template->event_category }}">
                            <input type="hidden" name="start_date" value="{{ $template->event_start_date?->format('Y-m-d') }}">
                            <input type="hidden" name="end_date" value="{{ $template->event_end_date?->format('Y-m-d') }}">

                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white rounded-xl font-medium text-sm shadow-sm hover:bg-primary-700 focus:outline-none focus:ring focus:ring-primary-light/30 transition-colors duration-150">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                {{ __('event_applications.actions.start_application') }}
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="bg-amber-50 rounded-xl border border-amber-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 sm:px-8 flex items-center gap-3">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-amber-800 font-medium">
                            {{ __('event_applications.template_closed_no_applications') }}
                        </p>
                    </div>
                </div>
            @endif

        </div>

    </div>
</x-layout>
