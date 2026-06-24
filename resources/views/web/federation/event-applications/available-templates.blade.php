@section('title', __('event_applications.titles.available_templates'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('event_applications.titles.available_templates') }}</h1>
                <p class="text-sm text-slate-600 mt-2">
                    {{ __('event_applications.instructions.create_application') }}
                </p>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-secondary" href="{{ route('federation.event-applications.index') }}">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/>
                        <path d="M3.293 14.707a1 1 0 010-1.414L6.586 10 3.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/>
                    </svg>
                    <span class="ml-2">{{ __('event_applications.actions.back_to_list') }}</span>
                </a>

                <a class="btn btn-primary" href="{{ route('federation.event-applications.create-direct') }}">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="ml-2">{{ __('event_applications.actions.create_application') }}</span>
                </a>
            </div>
        </div>

        @if($templates->isEmpty())
            <!-- Empty State -->
            <div class="card">
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">
                        {{ __('event_applications.messages.no_available_templates') }}
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ __('event_applications.help.direct_submission') }}
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('federation.event-applications.create-direct') }}" class="btn btn-primary">
                            {{ __('event_applications.actions.create_application') }}
                        </a>
                    </div>
                </div>
            </div>
        @else
            <!-- Templates Grid -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($templates as $template)
                    <div class="card">
                        <!-- Template Header -->
                        <div class="mb-4">
                            <div class="flex items-start justify-between">
                                <h3 class="text-lg font-semibold text-slate-800">{{ $template->name }}</h3>

                                @if($template->hasEntityApplied)
                                    @include('web.federation.event-applications.components.already-applied-badge')
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $template->state_color }}-100 text-{{ $template->state_color }}-800">
                                        {{ __('event_applications.template_states.' . $template->state) }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-1 flex items-center gap-2 text-sm text-slate-600">
                                <span>{{ __('event_applications.event_types.' . $template->event_type) }}</span>
                                @if($template->sport)
                                    <span>•</span>
                                    <span>{{ $template->sport->name }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Template Description -->
                        @if($template->description)
                            <p class="text-sm text-slate-600 mb-4 line-clamp-3">
                                {{ $template->description }}
                            </p>
                        @endif

                        <!-- Template Details -->
                        <div class="border-t border-slate-200 pt-4 space-y-2">
                            <!-- Event Dates -->
                            <div class="flex items-center text-sm">
                                <svg class="w-4 h-4 text-slate-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-slate-600">
                                    {{ $template->event_start_date->format('d/m/Y') }} - {{ $template->event_end_date->format('d/m/Y') }}
                                </span>
                            </div>

                            <!-- Submission Deadline -->
                            <div class="flex items-center text-sm">
                                <svg class="w-4 h-4 text-slate-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-slate-600">
                                    {{ __('event_applications.labels.submission_deadline') }}: {{ $template->submission_end_date->format('d/m/Y') }}
                                </span>
                            </div>

                            <!-- Applications Count -->
                            <div class="flex items-center text-sm">
                                <svg class="w-4 h-4 text-slate-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span class="text-slate-600">
                                    {{ trans_choice('event_applications.messages.applications_count', $template->applications_count, ['count' => $template->applications_count]) }}
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="border-t border-slate-200 pt-4 mt-4">
                            @if($template->hasEntityApplied)
                                <div class="space-y-2">
                                    <p class="text-sm text-slate-600">
                                        {{ __('event_applications.messages.cannot_apply_already_submitted') }}
                                    </p>
                                    <a href="{{ route('federation.event-applications.show', $template->existingApplication) }}"
                                       class="btn btn-secondary w-full justify-center">
                                        <span>{{ __('event_applications.actions.view_application') }}</span>
                                    </a>
                                </div>
                            @elseif($template->isOpen())
                                <a href="{{ route('federation.event-applications.create-from-template', $template) }}"
                                   class="btn btn-primary w-full justify-center">
                                    <span>{{ __('event_applications.actions.apply_now') }}</span>
                                </a>
                            @else
                                <p class="text-sm text-slate-500">
                                    {{ __('event_applications.template_closed_no_applications') }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</x-layout>
