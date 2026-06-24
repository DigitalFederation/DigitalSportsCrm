@section('title', __('event_applications.titles.view_application'))
<x-layout>
    <div class="space-y-6" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">

        {{-- Header Card --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
             x-show="loaded"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="px-6 py-6 sm:px-8">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    {{-- Left: Application Info --}}
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-100">
                                <x-heroicon-s-document-text class="w-6 h-6 text-primary-600" />
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium border"
                                  style="background-color: {{ $application->stateColor() }}10; color: {{ $application->stateColor() }}; border-color: {{ $application->stateColor() }}40;">
                                {{ $application->stateName() }}
                            </span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">{{ $application->event_name }}</h1>
                        <p class="mt-1 text-gray-500 text-sm">
                            @if($application->template)
                                {{ $application->template->name }}
                            @else
                                {{ __('event_applications.types.direct_submission') }}
                            @endif
                        </p>

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
                        </div>
                    </div>

                    {{-- Right: Application ID Badge --}}
                    <div class="flex-shrink-0">
                        <div class="inline-flex flex-col items-center justify-center px-5 py-3 rounded-xl bg-gray-50 border border-gray-200">
                            <span class="text-3xl sm:text-4xl font-bold text-gray-900 tabular-nums">#{{ $application->id }}</span>
                            <span class="text-xs font-medium text-gray-500 mt-0.5">{{ __('event_applications.labels.application_id') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="mt-5 pt-4 border-t border-gray-100 flex flex-wrap items-center gap-3">
                    <a href="{{ route('federation.event-applications.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg font-medium text-sm text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring focus:ring-gray-200/50 transition-colors duration-150">
                        <x-heroicon-m-arrow-left class="w-4 h-4" />
                        {{ __('event_applications.actions.back_to_list') }}
                    </a>

                    @if($application->state->canEdit())
                        <a href="{{ route('federation.event-applications.edit', $application) }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-primary-light rounded-lg font-medium text-sm text-primary tracking-wide shadow-sm hover:bg-secondary-light focus:outline-none focus:border-primary focus:ring focus:ring-primary-light/30 transition-colors duration-150">
                            <x-heroicon-m-pencil-square class="w-4 h-4" />
                            {{ __('common.edit') }}
                        </a>
                    @endif

                    @if($application->state->canSubmit())
                        <form action="{{ route('federation.event-applications.submit', $application) }}" method="POST" class="inline-block">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-emerald-600 to-emerald-700 rounded-lg shadow-sm hover:from-emerald-700 hover:to-emerald-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-200"
                                    onclick="return confirm('{{ __('event_applications.confirmations.submit_application') }}')">
                                <x-heroicon-m-paper-airplane class="w-4 h-4" />
                                {{ __('event_applications.actions.submit_application') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Summary Cards Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4"
             x-show="loaded"
             x-transition:enter="transition ease-out duration-500 delay-100"
             x-transition:enter-start="opacity-0 transform translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0">

            {{-- Application Status Card --}}
            <div class="group bg-white rounded-xl border-2 p-4 sm:p-5 transition-all duration-300 ease-out hover:shadow-md"
                 style="border-color: {{ $application->stateColor() }}40; background-color: {{ $application->stateColor() }}08;">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-medium text-gray-500">{{ __('event_applications.labels.application_status') }}</p>
                        <div class="mt-1.5 flex items-center gap-2">
                            <span class="relative flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" style="background-color: {{ $application->stateColor() }};"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3" style="background-color: {{ $application->stateColor() }};"></span>
                            </span>
                            <p class="text-lg font-bold text-gray-900">{{ $application->stateName() }}</p>
                        </div>
                        @if($application->submitted_at)
                            <p class="mt-1 text-xs text-gray-500">{{ $application->submitted_at->diffForHumans() }}</p>
                        @endif
                    </div>
                    <div class="flex-shrink-0 p-2.5 rounded-xl transition-transform duration-200 group-hover:scale-110" style="background-color: {{ $application->stateColor() }}20;">
                        <x-heroicon-o-check-circle class="w-5 h-5" style="color: {{ $application->stateColor() }};" />
                    </div>
                </div>
            </div>

            {{-- Event Period Card --}}
            <div class="group bg-white rounded-xl border-2 border-emerald-200 bg-emerald-50/30 p-4 sm:p-5 transition-all duration-300 ease-out hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-medium text-gray-500">{{ __('event_applications.sections.event_period') }}</p>
                        <p class="mt-1.5 text-base font-bold text-gray-900 inline-flex items-center gap-2">
                            {{ $application->start_date?->format('d/m/Y') ?? '-' }}
                            <x-heroicon-m-arrow-right class="w-4 h-4 text-gray-400" />
                            {{ $application->end_date?->format('d/m/Y') ?? '-' }}
                        </p>
                    </div>
                    <div class="flex-shrink-0 p-2.5 rounded-xl bg-emerald-100 transition-transform duration-200 group-hover:scale-110">
                        <x-heroicon-o-clock class="w-5 h-5 text-emerald-600" />
                    </div>
                </div>
            </div>

            {{-- Federation Card --}}
            <div class="group bg-white rounded-xl border-2 border-blue-200 bg-blue-50/30 p-4 sm:p-5 transition-all duration-300 ease-out hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-medium text-gray-500">{{ __('event_applications.labels.entity') }}</p>
                        <p class="mt-1.5 text-base font-bold text-gray-900">{{ $application->entity?->name ?? '-' }}</p>
                    </div>
                    <div class="flex-shrink-0 p-2.5 rounded-xl bg-blue-100 transition-transform duration-200 group-hover:scale-110">
                        <x-heroicon-o-building-office class="w-5 h-5 text-blue-600" />
                    </div>
                </div>
            </div>

            {{-- Template Card --}}
            <div class="group bg-white rounded-xl border-2 border-purple-200 bg-purple-50/30 p-4 sm:p-5 transition-all duration-300 ease-out hover:shadow-md">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-medium text-gray-500">{{ __('event_applications.labels.template') }}</p>
                        <p class="mt-1.5 text-base font-bold text-gray-900">
                            {{ $application->template?->name ?? __('event_applications.labels.direct_submission') }}
                        </p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 mt-1.5">
                            {{ __('event_applications.event_types.' . $application->event_type) }}
                        </span>
                    </div>
                    <div class="flex-shrink-0 p-2.5 rounded-xl bg-purple-100 transition-transform duration-200 group-hover:scale-110">
                        <x-heroicon-o-document-text class="w-5 h-5 text-purple-600" />
                    </div>
                </div>
            </div>

        </div>

        {{-- Status Tracker --}}
        <div x-show="loaded"
             x-transition:enter="transition ease-out duration-500 delay-150"
             x-transition:enter-start="opacity-0 transform translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0">
            <livewire:event-applications.federation.application-status-tracker :application="$application" />
        </div>

        {{-- 2-Column Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6"
             x-show="loaded"
             x-transition:enter="transition ease-out duration-500 delay-200"
             x-transition:enter-start="opacity-0 transform translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0">

            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Event Information Card --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50/50 px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100">
                                <x-heroicon-o-information-circle class="w-4 h-4 text-indigo-600" />
                            </div>
                            <span class="text-sm font-semibold text-gray-700">{{ __('event_applications.sections.event_information') }}</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.event_name') }}</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $application->event_name }}</p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.event_type') }}</p>
                                <p class="text-sm font-semibold text-gray-900">{{ __('event_applications.event_types.' . $application->event_type) }}</p>
                            </div>

                            @if($application->event_category)
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.event_category') }}</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ \App\Enums\EvtEventOrganizationCategoryEnum::toString($application->event_category) }}</p>
                                </div>
                            @endif

                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.start_date') }}</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $application->start_date->format('d/m/Y') }}</p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.end_date') }}</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $application->end_date->format('d/m/Y') }}</p>
                            </div>

                            @if($application->district)
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.district') }}</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $application->district->name }}</p>
                                </div>
                            @endif

                            @if($application->municipality)
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.municipality') }}</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $application->municipality }}</p>
                                </div>
                            @endif

                            @if($application->target_audience)
                                <div class="sm:col-span-2">
                                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.target_audience') }}</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $application->target_audience }}</p>
                                </div>
                            @endif

                            @if($application->expected_participants)
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.expected_participants') }}</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $application->expected_participants }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Responsible Contact Card --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50/50 px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100">
                                <x-heroicon-o-user class="w-4 h-4 text-blue-600" />
                            </div>
                            <span class="text-sm font-semibold text-gray-700">{{ __('event_applications.sections.responsible_contact') }}</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.responsible_name') }}</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $application->responsible_name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.responsible_phone') }}</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $application->responsible_phone }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Documents Card --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50/50 px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-amber-100">
                                <x-heroicon-o-paper-clip class="w-4 h-4 text-amber-600" />
                            </div>
                            <span class="text-sm font-semibold text-gray-700">{{ __('event_applications.sections.documents') }}</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <livewire:event-applications.federation.application-document-uploader
                            :application="$application"
                            :readonly="!$application->state->canEdit()" />
                    </div>
                </div>

                {{-- Comments (if returned for correction or rejected) --}}
                @if($application->state instanceof \Domain\EventApplications\States\ReturnedForCorrectionApplicationState ||
                    $application->state instanceof \Domain\EventApplications\States\RejectedApplicationState)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="border-b border-gray-200 bg-gray-50/50 px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-yellow-100">
                                    <x-heroicon-o-chat-bubble-left-right class="w-4 h-4 text-yellow-600" />
                                </div>
                                <span class="text-sm font-semibold text-gray-700">{{ __('event_applications.sections.comments') }}</span>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            @foreach($application->comments()->latest()->get() as $comment)
                                @php
                                    $commentName = $comment->user->name ?? 'System';
                                    $initials = strtoupper(substr($commentName, 0, 2));
                                @endphp
                                <div class="flex gap-3 p-4 rounded-lg border-l-4 bg-yellow-50 border-l-yellow-400">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full flex items-center justify-center text-sm font-bold bg-yellow-200 text-yellow-800">
                                            {{ $initials }}
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-sm font-medium text-slate-800">{{ $commentName }}</span>
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

            {{-- Sidebar --}}
            <div class="space-y-6">

                {{-- Status Card --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50/50 px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg" style="background-color: {{ $application->stateColor() }}20;">
                                <x-heroicon-o-signal class="w-4 h-4" style="color: {{ $application->stateColor() }};" />
                            </div>
                            <span class="text-sm font-semibold text-gray-700">{{ __('event_applications.labels.current_state') }}</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="relative flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" style="background-color: {{ $application->stateColor() }};"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3" style="background-color: {{ $application->stateColor() }};"></span>
                            </span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium border"
                                  style="background-color: {{ $application->stateColor() }}10; color: {{ $application->stateColor() }}; border-color: {{ $application->stateColor() }}40;">
                                {{ $application->stateName() }}
                            </span>
                        </div>

                        <p class="text-sm text-gray-600">
                            {{ __('event_applications.state_descriptions.' . $application->state->name()) }}
                        </p>

                        @if($application->submitted_at)
                            <div class="mt-4 pt-4 border-t border-gray-200 text-sm">
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.submitted_at') }}</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $application->submitted_at->format('d/m/Y H:i') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Organization Details Card --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="border-b border-gray-200 bg-gray-50/50 px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100">
                                <x-heroicon-o-building-office class="w-4 h-4 text-blue-600" />
                            </div>
                            <span class="text-sm font-semibold text-gray-700">{{ __('event_applications.sections.organization_details') }}</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="col-span-2 bg-blue-50 rounded-lg p-3 border border-blue-100">
                                <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">{{ __('event_applications.labels.entity') }}</p>
                                <p class="text-sm font-semibold text-blue-700 flex items-center gap-2">
                                    <x-heroicon-s-building-office class="w-4 h-4" />
                                    {{ $application->entity->name }}
                                </p>
                            </div>

                            @if($application->template)
                                <div class="col-span-2 bg-indigo-50 rounded-lg p-3 border border-indigo-100">
                                    <p class="text-xs text-indigo-600 uppercase tracking-wide mb-1">{{ __('event_applications.labels.template_name') }}</p>
                                    <p class="text-sm font-semibold text-indigo-700 flex items-center gap-2">
                                        <x-heroicon-s-document-text class="w-4 h-4" />
                                        {{ $application->template->name }}
                                    </p>
                                </div>
                            @endif

                            <div class="bg-slate-50 rounded-lg p-3">
                                <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('common.created_at') }}</p>
                                <p class="text-sm font-semibold text-slate-700">{{ $application->created_at->format('d/m/Y H:i') }}</p>
                            </div>

                            @if($application->updated_at->ne($application->created_at))
                                <div class="bg-slate-50 rounded-lg p-3">
                                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('common.updated_at') }}</p>
                                    <p class="text-sm font-semibold text-slate-700">{{ $application->updated_at->format('d/m/Y H:i') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- State History Card --}}
                @if($application->stateHistory()->exists())
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="border-b border-gray-200 bg-gray-50/50 px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100">
                                    <x-heroicon-o-clock class="w-4 h-4 text-slate-600" />
                                </div>
                                <span class="text-sm font-semibold text-gray-700">{{ __('event_applications.sections.history') }}</span>
                            </div>
                        </div>
                        <div class="p-6">
                            @php
                                $stateStyles = [
                                    'DraftApplicationState' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-500', 'icon' => 'heroicon-s-pencil'],
                                    'SubmittedApplicationState' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-500', 'icon' => 'heroicon-s-paper-airplane'],
                                    'InValidationApplicationState' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600', 'icon' => 'heroicon-s-magnifying-glass'],
                                    'ApprovedApplicationState' => ['bg' => 'bg-green-100', 'text' => 'text-green-500', 'icon' => 'heroicon-s-check-circle'],
                                    'RejectedApplicationState' => ['bg' => 'bg-red-100', 'text' => 'text-red-500', 'icon' => 'heroicon-s-x-circle'],
                                    'ReturnedForCorrectionApplicationState' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-500', 'icon' => 'heroicon-s-arrow-uturn-left'],
                                    'PublishedApplicationState' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-500', 'icon' => 'heroicon-s-megaphone'],
                                ];

                                $sortedHistory = $application->stateHistory->sortByDesc('created_at');
                            @endphp

                            <div class="relative">
                                <div class="absolute left-4 top-4 bottom-4 w-0.5 bg-slate-200"></div>
                                <div class="space-y-4">
                                    @foreach($sortedHistory as $history)
                                        @php
                                            $stateClass = class_basename($history->to_state ?? $history->state ?? '');
                                            $style = $stateStyles[$stateClass] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-500', 'icon' => 'heroicon-s-clock'];
                                        @endphp
                                        <div class="flex items-start relative">
                                            <div class="flex-shrink-0 z-10">
                                                <div class="h-8 w-8 rounded-full {{ $style['bg'] }} flex items-center justify-center ring-4 ring-white">
                                                    <x-dynamic-component :component="$style['icon']" class="h-4 w-4 {{ $style['text'] }}" />
                                                </div>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm font-medium text-slate-800">{{ $history->stateName() }}</p>
                                                <p class="text-xs text-slate-500">
                                                    {{ $history->created_at->format('d/m/Y H:i') }}
                                                    <span class="text-slate-400">&middot;</span>
                                                    {{ $history->created_at->diffForHumans() }}
                                                </p>
                                                @if($history->notes)
                                                    <div class="mt-1.5 p-2 bg-slate-50 rounded text-xs text-slate-600">{{ $history->notes }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

        </div>

    </div>
</x-layout>
