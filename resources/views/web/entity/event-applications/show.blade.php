@section('title', __('event_applications.titles.view_application'))
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
                                    <x-heroicon-s-document-text class="w-6 h-6 text-primary-600" />
                                </div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                      style="background-color: {{ $application->stateColor() }}20; color: {{ $application->stateColor() }};">
                                    {{ $application->stateName() }}
                                </span>
                            </div>
                            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">{{ $application->event_name }}</h1>

                            <div class="flex flex-wrap items-center gap-4 mt-3 text-sm text-gray-500">
                                <span class="inline-flex items-center gap-1.5">
                                    <x-heroicon-m-tag class="w-4 h-4 text-gray-400" />
                                    {{ __('event_applications.event_types.' . $application->event_type) }}
                                </span>
                                @if($application->sport)
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-heroicon-m-bolt class="w-4 h-4 text-gray-400" />
                                        {{ $application->sport->translated_name }}
                                    </span>
                                @endif
                                <span class="inline-flex items-center gap-1.5">
                                    <x-heroicon-m-building-office-2 class="w-4 h-4 text-gray-400" />
                                    {{ $application->entity?->name ?? '-' }}
                                </span>
                                @if($application->start_date && $application->end_date)
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-heroicon-m-calendar class="w-4 h-4 text-gray-400" />
                                        {{ $application->start_date->format('d/m/Y') }} - {{ $application->end_date->format('d/m/Y') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-5 pt-4 border-t border-gray-100 flex flex-wrap items-center gap-3">
                        @if($application->state->canSubmit())
                            <form action="{{ route('entity.event-applications.submit', $application) }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 border border-emerald-600 rounded-lg font-medium text-sm text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring focus:ring-emerald-200/50 transition-colors duration-150"
                                        onclick="return confirm('{{ __('event_applications.confirmations.submit_application') }}')">
                                    <x-heroicon-m-check class="w-4 h-4" />
                                    {{ __('event_applications.actions.submit_application') }}
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('entity.event-applications.index') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg font-medium text-sm text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring focus:ring-gray-200/50 transition-colors duration-150">
                            <x-heroicon-m-arrow-left class="w-4 h-4" />
                            {{ __('event_applications.actions.back_to_list') }}
                        </a>
                        <a href="{{ route('entity.event-applications.pdf', $application) }}"
                           target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg font-medium text-sm text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring focus:ring-gray-200/50 transition-colors duration-150">
                            <x-heroicon-m-arrow-down-tray class="w-4 h-4" />
                            {{ __('event_applications.actions.download_pdf') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Status Tracker -->
            <livewire:event-applications.entity.application-status-tracker :application="$application" />

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
                            <x-heroicon-o-check-circle class="w-5 h-5" style="color: {{ $application->stateColor() }};" />
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
                            <x-heroicon-o-clock class="w-5 h-5 text-emerald-600" />
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
                            <x-heroicon-o-building-office-2 class="w-5 h-5 text-blue-600" />
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
                            <x-heroicon-o-document-text class="w-5 h-5 text-purple-600" />
                        </div>
                    </div>
                </div>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Event Details (Wizard) -->
                    @if($application->state->canEdit())
                        <livewire:event-applications.entity.application-form-wizard
                            :application="$application"
                            :template="$application->template"
                            :mode="'edit'" />
                    @endif

                    {{-- Form Data Sections (readonly) --}}
                    @if($application->form_data && !$application->state->canEdit())
                        @include('web.entity.event-applications.partials.form-data-readonly', ['application' => $application])
                    @endif

                    <!-- Documents (only shown outside wizard; during editing, documents are in wizard step 9) -->
                    @if(!$application->state->canEdit())
                        <div class="card">
                            <h2 class="text-lg font-semibold text-slate-800 pb-3 mb-4 border-b border-slate-100">
                                {{ __('event_applications.sections.documents') }}
                            </h2>

                            <livewire:event-applications.entity.application-document-uploader
                                :application="$application"
                                :readonly="true" />
                        </div>
                    @endif

                    {{-- General Comments (comments without a section) --}}
                    @php
                        $generalComments = $application->comments
                            ->whereNull('section')
                            ->where('is_internal', false)
                            ->sortByDesc('created_at');
                    @endphp
                    @if($generalComments->isNotEmpty())
                        <div class="card">
                            <h2 class="text-lg font-semibold text-slate-800 pb-3 mb-4 border-b border-slate-100">
                                {{ __('event_applications.sections.general_feedback') }}
                            </h2>
                            <div class="space-y-4">
                                @foreach($generalComments as $comment)
                                    @php
                                        $commentName = $comment->user->name ?? 'System';
                                        $initials = strtoupper(substr($commentName, 0, 2));
                                    @endphp
                                    <div class="flex gap-3 p-4 rounded-lg border-l-4 bg-blue-50 border-l-blue-400">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full flex items-center justify-center text-sm font-bold bg-blue-200 text-blue-800">
                                                {{ $initials }}
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="font-medium text-slate-700">{{ $commentName }}</span>
                                                <span class="text-xs text-slate-500">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-sm text-slate-600 whitespace-pre-wrap">{{ $comment->comment }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>

                <!-- Sidebar -->
                <div class="space-y-6">

                    {{-- Wizard step nav placeholder (teleported from Livewire component) --}}
                    @if($application->state->canEdit())
                        <div id="wizard-step-nav"></div>
                    @endif

                    <!-- Event Details (readonly) -->
                    @if(!$application->state->canEdit())
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="border-b border-gray-200 bg-gray-50/50 px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100">
                                        <x-heroicon-o-clipboard-document-list class="w-4 h-4 text-blue-600" />
                                    </div>
                                    <span class="text-sm font-semibold text-gray-700">{{ __('event_applications.sections.event_details') }}</span>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="col-span-2 bg-blue-50 rounded-lg p-3 border border-blue-100">
                                        <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">{{ __('event_applications.labels.entity') }}</p>
                                        <p class="text-sm font-semibold text-blue-700 flex items-center gap-2">
                                            <x-heroicon-s-building-office class="w-4 h-4" />
                                            {{ $application->entity?->name ?? '-' }}
                                        </p>
                                    </div>

                                    @if($application->template)
                                        <div class="col-span-2 bg-indigo-50 rounded-lg p-3 border border-indigo-100">
                                            <p class="text-xs text-indigo-600 uppercase tracking-wide mb-1">{{ __('event_applications.labels.template') }}</p>
                                            <p class="text-sm font-semibold text-indigo-700 flex items-center gap-2">
                                                <x-heroicon-s-document-text class="w-4 h-4" />
                                                {{ $application->template->name }}
                                            </p>
                                        </div>
                                    @endif

                                    @if($application->template?->registration_type)
                                        <div class="bg-slate-50 rounded-lg p-3">
                                            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.registration_type') }}</p>
                                            <p class="text-sm font-semibold text-slate-700">{{ __('event_applications.registration_types.' . $application->template->registration_type) }}</p>
                                        </div>
                                    @endif

                                    @if($application->category ?? $application->template?->category)
                                        <div class="bg-slate-50 rounded-lg p-3">
                                            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.category') }}</p>
                                            <p class="text-sm font-semibold text-slate-700">{{ __('event_applications.categories.' . ($application->category ?? $application->template->category)) }}</p>
                                        </div>
                                    @endif

                                    @if($application->template?->age_group)
                                        <div class="bg-slate-50 rounded-lg p-3">
                                            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.age_group') }}</p>
                                            <p class="text-sm font-semibold text-slate-700">{{ $application->template->age_group }}</p>
                                        </div>
                                    @endif

                                    @if($application->event_category)
                                        <div class="bg-slate-50 rounded-lg p-3">
                                            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.event_category') }}</p>
                                            <p class="text-sm font-semibold text-slate-700">
                                                {{ \App\Enums\EvtEventOrganizationCategoryEnum::toString($application->event_category) }}
                                            </p>
                                        </div>
                                    @endif

                                    @if($application->district || $application->municipality)
                                        <div class="col-span-2 bg-slate-50 rounded-lg p-3">
                                            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.location') }}</p>
                                            <p class="text-sm font-semibold text-slate-700">
                                                {{ collect([$application->district?->name, $application->municipality])->filter()->implode(', ') }}
                                            </p>
                                        </div>
                                    @endif

                                    @if($application->target_audience)
                                        <div class="bg-slate-50 rounded-lg p-3">
                                            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.target_audience') }}</p>
                                            <p class="text-sm font-semibold text-slate-700">{{ $application->target_audience }}</p>
                                        </div>
                                    @endif

                                    @if($application->expected_participants)
                                        <div class="bg-slate-50 rounded-lg p-3">
                                            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.expected_participants') }}</p>
                                            <p class="text-sm font-semibold text-slate-700">{{ $application->expected_participants }}</p>
                                        </div>
                                    @endif

                                    @if($application->responsible_name)
                                        <div class="col-span-2 bg-slate-50 rounded-lg p-3">
                                            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.responsible_name') }}</p>
                                            <p class="text-sm font-semibold text-slate-700">{{ $application->responsible_name }}</p>
                                        </div>
                                    @endif

                                    @if($application->responsible_phone)
                                        <div class="bg-slate-50 rounded-lg p-3">
                                            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.responsible_phone') }}</p>
                                            <p class="text-sm font-semibold text-slate-700">{{ $application->responsible_phone }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- State History -->
                    @if($application->stateHistory()->exists())
                        <div class="card">
                            <h3 class="grow font-semibold text-slate-800 truncate mb-3">
                                {{ __('event_applications.sections.history') }}
                            </h3>

                            <div class="space-y-3">
                                @foreach($application->stateHistory()->latest()->get() as $history)
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center">
                                                <x-heroicon-s-clock class="h-4 w-4 text-slate-500" />
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

                                @if($application->submitted_at)
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                <x-heroicon-s-bell class="h-4 w-4 text-blue-500" />
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
                                            <x-heroicon-s-clock class="h-4 w-4 text-gray-400" />
                                        </div>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm font-medium text-slate-800">{{ __('common.created_at') }}</p>
                                        <p class="text-xs text-slate-500">{{ $application->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>

            </div>

        </div>

    </div>
</x-layout>
