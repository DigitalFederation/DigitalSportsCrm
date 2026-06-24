@section('title', __('event_applications.titles.view_application'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ $application->event_name }}</h1>
                <p class="text-sm text-slate-600 mt-1">
                    {{ __('event_applications.event_types.' . $application->event_type) }}
                    @if($application->template)
                        • {{ $application->template->name }}
                    @endif
                </p>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-secondary" href="{{ route('entity.event-applications.index') }}">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/>
                    </svg>
                    <span class="hidden xs:block ml-2">{{ __('event_applications.actions.back_to_list') }}</span>
                </a>

                @if($application->state->canEdit())
                    <a class="btn btn-primary" href="{{ route('entity.event-applications.edit', $application) }}">
                        <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                            <path d="M11.7.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM4.6 14H2v-2.6l6-6L10.6 8l-6 6zM12 6.6L9.4 4 11 2.4 13.6 5 12 6.6z" />
                        </svg>
                        <span class="hidden xs:block ml-2">{{ __('common.edit') }}</span>
                    </a>
                @endif

                @if($application->state->canSubmit())
                    <form action="{{ route('entity.event-applications.submit', $application) }}" method="POST" class="inline-block">
                        @csrf
                        <button type="submit" class="btn btn-success"
                                onclick="return confirm('{{ __('event_applications.confirmations.submit_application') }}')">
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path d="M14.3 2.3L5 11.6 1.7 8.3c-.4-.4-1-.4-1.4 0-.4.4-.4 1 0 1.4l4 4c.2.2.4.3.7.3.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4-.4-.4-1-.4-1.4 0z" />
                            </svg>
                            <span class="hidden xs:block ml-2">{{ __('event_applications.actions.submit_application') }}</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Status Tracker -->
        <div class="mb-8">
            <livewire:event-applications.entity.application-status-tracker :application="$application" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Event Information -->
                <div class="card">
                    <h2 class="text-xl leading-snug text-slate-800 font-bold mb-5">
                        {{ __('event_applications.sections.event_information') }}
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-600">
                                {{ __('event_applications.labels.event_name') }}
                            </label>
                            <p class="text-sm text-slate-800">{{ $application->event_name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-600">
                                {{ __('event_applications.labels.event_type') }}
                            </label>
                            <p class="text-sm text-slate-800">
                                {{ __('event_applications.event_types.' . $application->event_type) }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-600">
                                {{ __('event_applications.labels.start_date') }}
                            </label>
                            <p class="text-sm text-slate-800">{{ $application->start_date->format('d/m/Y') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-600">
                                {{ __('event_applications.labels.end_date') }}
                            </label>
                            <p class="text-sm text-slate-800">{{ $application->end_date->format('d/m/Y') }}</p>
                        </div>

                        @if($application->district)
                            <div>
                                <label class="block text-sm font-medium mb-1 text-slate-600">
                                    {{ __('event_applications.labels.district') }}
                                </label>
                                <p class="text-sm text-slate-800">{{ $application->district->name }}</p>
                            </div>
                        @endif

                        @if($application->municipality)
                            <div>
                                <label class="block text-sm font-medium mb-1 text-slate-600">
                                    {{ __('event_applications.labels.municipality') }}
                                </label>
                                <p class="text-sm text-slate-800">{{ $application->municipality }}</p>
                            </div>
                        @endif>

                        @if($application->target_audience)
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium mb-1 text-slate-600">
                                    {{ __('event_applications.labels.target_audience') }}
                                </label>
                                <p class="text-sm text-slate-800">{{ $application->target_audience }}</p>
                            </div>
                        @endif

                        @if($application->expected_participants)
                            <div>
                                <label class="block text-sm font-medium mb-1 text-slate-600">
                                    {{ __('event_applications.labels.expected_participants') }}
                                </label>
                                <p class="text-sm text-slate-800">{{ $application->expected_participants }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Responsible Contact -->
                <div class="card">
                    <h2 class="text-xl leading-snug text-slate-800 font-bold mb-5">
                        {{ __('event_applications.sections.responsible_contact') }}
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-600">
                                {{ __('event_applications.labels.responsible_name') }}
                            </label>
                            <p class="text-sm text-slate-800">{{ $application->responsible_name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-600">
                                {{ __('event_applications.labels.responsible_phone') }}
                            </label>
                            <p class="text-sm text-slate-800">{{ $application->responsible_phone }}</p>
                        </div>
                    </div>
                </div>

                <!-- Documents (only shown outside wizard; during editing, documents are in wizard step 9) -->
                @if(!$application->state->canEdit())
                    <div class="card">
                        <h2 class="text-xl leading-snug text-slate-800 font-bold mb-5">
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
                        <h2 class="text-xl leading-snug text-slate-800 font-bold mb-5">
                            {{ __('event_applications.sections.general_feedback') }}
                        </h2>
                        <div class="space-y-2">
                            @foreach($generalComments as $comment)
                                <div class="p-3 rounded-lg text-sm bg-blue-50 border border-blue-200">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-medium text-slate-700">{{ $comment->user->name ?? 'System' }}</span>
                                        <span class="text-xs text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-slate-600 whitespace-pre-wrap">{{ $comment->comment }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            <!-- Sidebar -->
            <div class="space-y-6">

                <!-- Status Card -->
                <div class="card">
                    <h3 class="grow font-semibold text-slate-800 truncate mb-3">
                        {{ __('event_applications.labels.current_state') }}
                    </h3>

                    <div class="mb-4">
                        @include('web.entity.event_applications.components.status-badge', ['application' => $application])
                    </div>

                    <p class="text-sm text-slate-600">
                        {{ __('event_applications.state_descriptions.' . $application->state->name()) }}
                    </p>

                    @if($application->submitted_at)
                        <div class="mt-4 pt-4 border-t border-slate-200 text-sm">
                            <span class="text-slate-600">{{ __('event_applications.labels.submitted_at') }}:</span>
                            <span class="text-slate-800">{{ $application->submitted_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                </div>

                <!-- Application Info -->
                <div class="card">
                    <h3 class="grow font-semibold text-slate-800 truncate mb-3">
                        {{ __('event_applications.sections.organization_details') }}
                    </h3>

                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="text-slate-600">{{ __('event_applications.labels.entity') }}:</span>
                            <span class="text-slate-800 font-medium block">{{ $application->entity->name }}</span>
                        </div>

                        @if($application->template)
                            <div>
                                <span class="text-slate-600">{{ __('event_applications.labels.template_name') }}:</span>
                                <span class="text-slate-800 font-medium block">{{ $application->template->name }}</span>
                            </div>
                        @endif

                        <div>
                            <span class="text-slate-600">{{ __('common.created_at') }}:</span>
                            <span class="text-slate-800 block">{{ $application->created_at->format('d/m/Y H:i') }}</span>
                        </div>

                        @if($application->updated_at->ne($application->created_at))
                            <div>
                                <span class="text-slate-600">{{ __('common.updated_at') }}:</span>
                                <span class="text-slate-800 block">{{ $application->updated_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

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
                                            <svg class="h-4 w-4 text-slate-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm font-medium text-slate-800">
                                            {{ $history->stateName() }}
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            {{ $history->created_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

        </div>

    </div>
</x-layout>
