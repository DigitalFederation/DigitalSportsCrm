@section('title', __('event_applications.titles.application_details'))
<x-layout>
    <div class="previous-layout-classes">

        <div class="space-y-6 mt-5">

            {{-- Header Card --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-6 sm:px-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        {{-- Left: Application Info --}}
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-100">
                                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                      style="background-color: {{ $application->stateColor() }}20; color: {{ $application->stateColor() }};">
                                    {{ $application->stateName() }}
                                </span>
                            </div>
                            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">{{ $application->event_name }}</h1>

                            <div class="flex flex-wrap items-center gap-4 mt-3 text-sm text-gray-500">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    {{ __('event_applications.event_types.' . $application->event_type) }}
                                </span>
                                @if($application->sport)
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        {{ $application->sport->name }}
                                    </span>
                                @endif
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    {{ $application->entity?->name ?? '-' }}
                                </span>
                                @if($application->start_date && $application->end_date)
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        {{ $application->start_date->format('d/m/Y') }} - {{ $application->end_date->format('d/m/Y') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-5 pt-4 border-t border-gray-100 flex flex-wrap items-center gap-3">
                        <a href="{{ route('admin.event-applications.index') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg font-medium text-sm text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring focus:ring-gray-200/50 transition-colors duration-150">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            {{ __('event_applications.actions.back_to_list') }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- Summary Cards Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">

                {{-- Application Status Card --}}
                <div class="bg-white rounded-xl border-2 p-4 sm:p-5"
                     style="border-color: {{ $application->stateColor() }}40; background-color: {{ $application->stateColor() }}08;">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-medium text-gray-500">{{ __('event_applications.labels.application_status') }}</p>
                            <p class="mt-1.5 text-sm font-semibold text-gray-900">{{ $application->stateName() }}</p>
                            @if($application->submitted_at)
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ __('event_applications.labels.submitted_at') }}: {{ $application->submitted_at->format('d/m/Y') }}
                                </p>
                            @endif
                        </div>
                        <div class="flex-shrink-0 p-2.5 rounded-xl" style="background-color: {{ $application->stateColor() }}20;">
                            <svg class="w-5 h-5" style="color: {{ $application->stateColor() }};" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Event Period Card --}}
                <div class="bg-white rounded-xl border-2 border-emerald-200 bg-emerald-50/30 p-4 sm:p-5">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-medium text-gray-500">{{ __('event_applications.sections.event_period') }}</p>
                            <p class="mt-1.5 text-sm font-semibold text-gray-900">
                                {{ $application->start_date?->format('d/m/Y') ?? '-' }}
                                <span class="font-normal text-gray-400 mx-1">-</span>
                                {{ $application->end_date?->format('d/m/Y') ?? '-' }}
                            </p>
                        </div>
                        <div class="flex-shrink-0 p-2.5 rounded-xl bg-emerald-100">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Entity Card --}}
                <div class="bg-white rounded-xl border-2 border-blue-200 bg-blue-50/30 p-4 sm:p-5">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-medium text-gray-500">{{ __('event_applications.labels.entity') }}</p>
                            <p class="mt-1.5 text-sm font-semibold text-gray-900">{{ $application->entity?->name ?? '-' }}</p>
                        </div>
                        <div class="flex-shrink-0 p-2.5 rounded-xl bg-blue-100">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Template/Type Card --}}
                <div class="bg-white rounded-xl border-2 border-purple-200 bg-purple-50/30 p-4 sm:p-5">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-medium text-gray-500">{{ __('event_applications.labels.template') }}</p>
                            <p class="mt-1.5 text-sm font-semibold text-gray-900">
                                {{ $application->template?->name ?? __('event_applications.labels.direct_submission') }}
                            </p>
                        </div>
                        <div class="flex-shrink-0 p-2.5 rounded-xl bg-purple-100">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                    </div>
                </div>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Form Data Sections (readonly) --}}
                    @if($application->form_data)
                        @include('web.entity.event-applications.partials.form-data-readonly', ['application' => $application])
                    @endif

                    {{-- Documents --}}
                    @if($application->documents->count() > 0)
                        <div class="card">
                            <h2 class="text-xl leading-snug text-slate-800 font-bold mb-5">{{ __('event_applications.sections.documents') }}</h2>

                            @php
                                        $documentTypeLabels = [
                                            'safety_plan' => __('event_applications.document_types.safety_plan'),
                                            'competition_zones' => __('event_applications.document_types.competition_zones'),
                                            'partner_declaration' => __('event_applications.document_types.partner_declaration'),
                                            'promotion_plan' => __('event_applications.document_types.promotion_plan'),
                                            'insurance_policy' => __('event_applications.document_types.insurance_policy'),
                                            'other' => __('event_applications.document_types.other'),
                                        ];
                                    @endphp

                                    <div class="space-y-3">
                                @foreach($application->documents as $document)
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                        <div class="flex items-center space-x-3">
                                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                            <div>
                                                <p class="text-sm font-medium text-slate-800">{{ $documentTypeLabels[$document->document_type] ?? $document->document_type }}</p>
                                                <p class="text-xs text-slate-500">{{ $document->filename }}</p>
                                            </div>
                                        </div>
                                        <a href="{{ route('admin.application-documents.download', $document) }}" class="text-indigo-500 hover:text-indigo-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                            </svg>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Comments --}}
                    @include('web.admin.event-applications.components.comments', ['application' => $application])

                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">

                    {{-- State Actions --}}
                    @include('web.admin.event-applications.components.state-actions', ['application' => $application])

                    {{-- Conflict Alerts --}}
                    @include('web.admin.event-applications.components.conflict-alerts', ['application' => $application])

                    {{-- Event Details --}}
                    <div class="card">
                        <h3 class="grow font-semibold text-slate-800 truncate mb-3">
                            {{ __('event_applications.sections.event_details') }}
                        </h3>

                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1 text-slate-600">{{ __('event_applications.labels.entity') }}</label>
                                <p class="text-sm text-slate-800 font-medium">{{ $application->entity?->name ?? '-' }}</p>
                            </div>

                            @if($application->template)
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-slate-600">{{ __('event_applications.labels.template') }}</label>
                                    <p class="text-sm text-slate-800">{{ $application->template->name }}</p>
                                </div>
                            @endif

                            @if($application->template?->registration_type)
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-slate-600">{{ __('event_applications.labels.registration_type') }}</label>
                                    <p class="text-sm text-slate-800">{{ __('event_applications.registration_types.' . $application->template->registration_type) }}</p>
                                </div>
                            @endif

                            @if($application->category ?? $application->template?->category)
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-slate-600">{{ __('event_applications.labels.category') }}</label>
                                    <p class="text-sm text-slate-800">{{ __('event_applications.categories.' . ($application->category ?? $application->template->category)) }}</p>
                                </div>
                            @endif

                            @if($application->template?->age_group)
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-slate-600">{{ __('event_applications.labels.age_group') }}</label>
                                    <p class="text-sm text-slate-800">{{ $application->template->age_group }}</p>
                                </div>
                            @endif

                            @if($application->responsible_name)
                                <div class="pt-3 border-t border-slate-100">
                                    <label class="block text-sm font-medium mb-1 text-slate-600">{{ __('event_applications.labels.responsible_name') }}</label>
                                    <p class="text-sm text-slate-800">{{ $application->responsible_name }}</p>
                                </div>
                            @endif

                            @if($application->responsible_phone)
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-slate-600">{{ __('event_applications.labels.responsible_phone') }}</label>
                                    <p class="text-sm text-slate-800">{{ $application->responsible_phone }}</p>
                                </div>
                            @endif

                            @if($application->district || $application->municipality)
                                <div class="pt-3 border-t border-slate-100">
                                    <label class="block text-sm font-medium mb-1 text-slate-600">{{ __('event_applications.labels.location') }}</label>
                                    <p class="text-sm text-slate-800">
                                        {{ collect([$application->district?->name, $application->municipality])->filter()->implode(', ') }}
                                    </p>
                                </div>
                            @endif

                            @if($application->expected_participants)
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-slate-600">{{ __('event_applications.labels.expected_participants') }}</label>
                                    <p class="text-sm text-slate-800">{{ $application->expected_participants }}</p>
                                </div>
                            @endif

                            @if($application->target_audience)
                                <div>
                                    <label class="block text-sm font-medium mb-1 text-slate-600">{{ __('event_applications.labels.target_audience') }}</label>
                                    <p class="text-sm text-slate-800">{{ $application->target_audience }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Timeline History --}}
                    <div class="card">
                        <h3 class="grow font-semibold text-slate-800 truncate mb-3">
                            {{ __('event_applications.sections.history') }}
                        </h3>

                        <div class="space-y-3">
                            {{-- State History entries --}}
                            @foreach($application->stateHistory->sortByDesc('created_at') as $history)
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center">
                                            <svg class="h-4 w-4 text-slate-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm font-medium text-slate-800">{{ $history->stateName() }}</p>
                                        <p class="text-xs text-slate-500">{{ $history->created_at->format('d/m/Y H:i') }}</p>
                                        @if($history->notes)
                                            <p class="text-xs text-slate-500 mt-1">{{ $history->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            {{-- Key dates --}}
                            @if($application->submitted_at)
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                            <svg class="h-4 w-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm font-medium text-slate-800">{{ __('event_applications.labels.submitted_at') }}</p>
                                        <p class="text-xs text-slate-500">{{ $application->submitted_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                            @endif

                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center">
                                        <svg class="h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-slate-800">{{ __('common.created_at') }}</p>
                                    <p class="text-xs text-slate-500">{{ $application->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Admin Notes --}}
                    @if($application->admin_notes)
                        <div class="card">
                            <h3 class="grow font-semibold text-slate-800 truncate mb-3">{{ __('event_applications.labels.admin_notes') }}</h3>
                            <p class="text-sm text-slate-600 whitespace-pre-wrap">{{ $application->admin_notes }}</p>
                        </div>
                    @endif

                </div>

            </div>

        </div>

    </div>
</x-layout>
