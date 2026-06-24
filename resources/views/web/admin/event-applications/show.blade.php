@section('title', __('event_applications.titles.application_details'))
<x-layout>
    <div class="previous-layout-classes">

        <div class="space-y-6 mt-5" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">

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
                                {{ $application->entity?->name ?? '-' }} &middot; {{ __('event_applications.event_types.' . $application->event_type) }}
                            </p>

                            <div class="flex flex-wrap items-center gap-4 mt-3 text-sm text-gray-500">
                                @if($application->sport)
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-heroicon-m-bolt class="w-4 h-4 text-gray-400" />
                                        {{ $application->sport->name }}
                                    </span>
                                @endif
                                @if($application->start_date && $application->end_date)
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-heroicon-m-calendar class="w-4 h-4 text-gray-400" />
                                        {{ $application->start_date->format('d/m/Y') }} - {{ $application->end_date->format('d/m/Y') }}
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
                        <a href="{{ route($routeNamespace . '.event-applications.index') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg font-medium text-sm text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring focus:ring-gray-200/50 transition-colors duration-150">
                            <x-heroicon-m-arrow-left class="w-4 h-4" />
                            {{ __('event_applications.actions.back_to_list') }}
                        </a>
                        <a href="{{ route($routeNamespace . '.event-applications.pdf', $application) }}"
                           target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg font-medium text-sm text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring focus:ring-gray-200/50 transition-colors duration-150">
                            <x-heroicon-m-arrow-down-tray class="w-4 h-4" />
                            {{ __('event_applications.actions.download_pdf') }}
                        </a>
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
                     style="border-color: {{ $application->stateColor() }}40; background-color: {{ $application->stateColor() }}20;">
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
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $application->submitted_at->diffForHumans() }}
                                </p>
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

                {{-- Entity Card --}}
                <div class="group bg-white rounded-xl border-2 border-blue-200 bg-blue-50/30 p-4 sm:p-5 transition-all duration-300 ease-out hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-medium text-gray-500">{{ __('event_applications.labels.entity') }}</p>
                            <p class="mt-1.5 text-base font-bold text-gray-900">{{ $application->entity?->name ?? '-' }}</p>
                            @if($application->entity?->member_number)
                                <p class="mt-0.5 text-xs text-gray-500">{{ __('main.member_number') }}: {{ $application->entity->member_number }}</p>
                            @endif
                        </div>
                        <div class="flex-shrink-0 p-2.5 rounded-xl bg-blue-100 transition-transform duration-200 group-hover:scale-110">
                            <x-heroicon-o-building-office class="w-5 h-5 text-blue-600" />
                        </div>
                    </div>
                </div>

                {{-- Template/Type Card --}}
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6"
                 x-show="loaded"
                 x-transition:enter="transition ease-out duration-500 delay-200"
                 x-transition:enter-start="opacity-0 transform translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0">

                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Form Data Sections (readonly) --}}
                    @if($application->form_data)
                        @include('web.entity.event-applications.partials.form-data-readonly', ['application' => $application])
                    @endif

                    {{-- Documents --}}
                    @if($application->documents->count() > 0)
                        <div class="card">
                            <h2 class="text-lg font-semibold text-slate-800 pb-3 mb-4 border-b border-slate-100">{{ __('event_applications.sections.documents') }}</h2>

                            @php
                                $documentTypeLabels = [
                                    'safety_plan' => __('event_applications.document_types.safety_plan'),
                                    'competition_zones' => __('event_applications.document_types.competition_zones'),
                                    'partner_declaration' => __('event_applications.document_types.partner_declaration'),
                                    'promotion_plan' => __('event_applications.document_types.promotion_plan'),
                                    'insurance_policy' => __('event_applications.document_types.insurance_policy'),
                                    'other' => __('event_applications.document_types.other'),
                                ];

                                $documentTypeColors = [
                                    'safety_plan' => ['border' => 'border-l-red-500', 'bg' => 'bg-red-50/50', 'icon' => 'text-red-500'],
                                    'competition_zones' => ['border' => 'border-l-blue-500', 'bg' => 'bg-blue-50/50', 'icon' => 'text-blue-500'],
                                    'partner_declaration' => ['border' => 'border-l-green-500', 'bg' => 'bg-green-50/50', 'icon' => 'text-green-500'],
                                    'promotion_plan' => ['border' => 'border-l-purple-500', 'bg' => 'bg-purple-50/50', 'icon' => 'text-purple-500'],
                                    'insurance_policy' => ['border' => 'border-l-amber-500', 'bg' => 'bg-amber-50/50', 'icon' => 'text-amber-500'],
                                    'other' => ['border' => 'border-l-slate-400', 'bg' => 'bg-slate-50', 'icon' => 'text-slate-500'],
                                ];
                            @endphp

                            <div class="space-y-3">
                                @foreach($application->documents as $document)
                                    @php
                                        $docColor = $documentTypeColors[$document->document_type] ?? $documentTypeColors['other'];
                                    @endphp
                                    <div class="flex items-center justify-between p-4 rounded-lg border-l-4 {{ $docColor['border'] }} {{ $docColor['bg'] }} border border-slate-200">
                                        <div class="flex items-center flex-1">
                                            <x-heroicon-o-document class="h-8 w-8 mr-3 {{ $docColor['icon'] }}" />
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-slate-800">
                                                    {{ $documentTypeLabels[$document->document_type] ?? __('event_applications.document_types.other') }}
                                                </p>
                                                <p class="text-xs text-slate-500 truncate">{{ $document->file_name }}</p>
                                                <p class="text-xs text-slate-500">
                                                    {{ __('common.uploaded_at') }}: {{ $document->created_at->format('d/m/Y H:i') }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route($routeNamespace . '.application-documents.download', $document) }}"
                                               target="_blank"
                                               class="inline-flex items-center px-3 py-1.5 border border-slate-300 shadow-sm text-xs font-medium rounded-lg text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                                <x-heroicon-m-arrow-down-tray class="w-4 h-4 mr-1" />
                                                {{ __('event_applications.actions.download_document') }}
                                            </a>
                                            <form action="{{ route($routeNamespace . '.application-documents.destroy', $document) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('{{ __('event_applications.confirmations.delete_document') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 border border-rose-300 shadow-sm text-xs font-medium rounded-lg text-rose-700 bg-white hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-colors duration-150">
                                                    <x-heroicon-m-trash class="w-4 h-4 mr-1" />
                                                    {{ __('common.delete') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- General Comments (comments without a section) --}}
                    <div class="card" x-data="{ showCommentForm: false }">
                        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                            <h2 class="text-lg font-semibold text-slate-800">{{ __('event_applications.sections.general_feedback') }}</h2>
                            <button type="button"
                                    @click="showCommentForm = !showCommentForm"
                                    class="btn btn-sm btn-primary">
                                <x-heroicon-m-plus class="w-4 h-4 opacity-50 shrink-0" />
                                <span class="ml-2">{{ __('event_applications.actions.add_comment') }}</span>
                            </button>
                        </div>

                        <div x-show="showCommentForm"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="mb-6 p-4 bg-slate-50 rounded-lg">

                            <form action="{{ route($routeNamespace . '.event-applications.comment', ['application' => $application->id]) }}" method="POST">
                                @csrf

                                <div class="mb-4">
                                    <label class="block text-sm font-medium mb-2" for="comment">
                                        {{ __('event_applications.labels.comment') }} <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea id="comment"
                                              name="comment"
                                              rows="3"
                                              class="form-textarea w-full"
                                              required></textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="flex items-center">
                                        <input type="checkbox"
                                               name="is_internal"
                                               value="1"
                                               class="form-checkbox">
                                        <span class="text-sm ml-2">{{ __('event_applications.comment_types.internal') }}</span>
                                        <span class="text-xs text-slate-500 ml-2">({{ __('event_applications.labels.not_visible_to_applicant') }})</span>
                                    </label>
                                </div>

                                <div class="flex justify-end space-x-2">
                                    <button type="button"
                                            @click="showCommentForm = false"
                                            class="btn btn-sm btn-secondary">
                                        {{ __('common.cancel') }}
                                    </button>
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        {{ __('event_applications.actions.add_comment') }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        @php
                            $generalComments = $application->comments
                                ->whereNull('section')
                                ->sortByDesc('created_at');
                        @endphp

                        <div class="space-y-4">
                            @forelse($generalComments as $comment)
                                @php
                                    $commentName = $comment->user->name ?? 'System';
                                    $initials = strtoupper(substr($commentName, 0, 2));
                                @endphp
                                <div class="flex gap-3 p-4 rounded-lg border-l-4 {{ $comment->is_internal ? 'bg-yellow-50 border-l-yellow-400' : 'bg-slate-50 border-l-blue-400' }}">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full flex items-center justify-center text-sm font-bold {{ $comment->is_internal ? 'bg-yellow-200 text-yellow-800' : 'bg-blue-200 text-blue-800' }}">
                                            {{ $initials }}
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-1">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium text-slate-800">{{ $commentName }}</span>
                                                @if($comment->is_internal)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-yellow-200 text-yellow-800">
                                                        <x-heroicon-m-lock-closed class="w-3 h-3" />
                                                        {{ __('event_applications.comment_types.internal') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <span class="text-xs text-slate-500">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-sm text-slate-600 whitespace-pre-wrap">{{ $comment->comment }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8">
                                    <x-heroicon-o-chat-bubble-left-right class="w-10 h-10 text-slate-300 mx-auto mb-2" />
                                    <p class="text-sm text-slate-500">{{ __('event_applications.messages.no_comments') }}</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">

                    {{-- State Actions --}}
                    @if($canManageState ?? true)
                        @include('web.admin.event-applications.components.state-actions', ['application' => $application, 'routeNamespace' => $routeNamespace])
                    @endif

                    {{-- Conflict Alerts --}}
                    @include('web.admin.event-applications.components.conflict-alerts', ['application' => $application])

                    {{-- Event Details --}}
                    <div class="card">
                        <h3 class="text-base font-semibold text-slate-800 pb-3 mb-4 border-b border-slate-100">
                            {{ __('event_applications.sections.event_details') }}
                        </h3>

                        <div class="grid grid-cols-2 gap-3">
                            {{-- Entity -- Blue highlight (full width) --}}
                            <div class="col-span-2 bg-blue-50 rounded-lg p-3 border border-blue-100">
                                <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">{{ __('event_applications.labels.entity') }}</p>
                                <p class="text-sm font-semibold text-blue-700 flex items-center gap-2">
                                    <x-heroicon-s-building-office class="w-4 h-4" />
                                    {{ $application->entity?->name ?? '-' }}
                                </p>
                            </div>

                            {{-- Template -- Indigo highlight (full width) --}}
                            @if($application->template)
                                <div class="col-span-2 bg-indigo-50 rounded-lg p-3 border border-indigo-100">
                                    <p class="text-xs text-indigo-600 uppercase tracking-wide mb-1">{{ __('event_applications.labels.template') }}</p>
                                    <p class="text-sm font-semibold text-indigo-700 flex items-center gap-2">
                                        <x-heroicon-s-document-text class="w-4 h-4" />
                                        {{ $application->template->name }}
                                    </p>
                                </div>
                            @endif

                            {{-- Registration Type --}}
                            @if($application->template?->registration_type)
                                <div class="bg-slate-50 rounded-lg p-3">
                                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.registration_type') }}</p>
                                    <p class="text-sm font-semibold text-slate-700">{{ __('event_applications.registration_types.' . $application->template->registration_type) }}</p>
                                </div>
                            @endif

                            {{-- Category --}}
                            @if($application->category ?? $application->template?->category)
                                <div class="bg-slate-50 rounded-lg p-3">
                                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.category') }}</p>
                                    <p class="text-sm font-semibold text-slate-700">{{ __('event_applications.categories.' . ($application->category ?? $application->template->category)) }}</p>
                                </div>
                            @endif

                            {{-- Age Group --}}
                            @if($application->template?->age_group)
                                <div class="bg-slate-50 rounded-lg p-3">
                                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.age_group') }}</p>
                                    <p class="text-sm font-semibold text-slate-700">{{ $application->template->age_group }}</p>
                                </div>
                            @endif

                            {{-- Expected Participants --}}
                            @if($application->expected_participants)
                                <div class="bg-slate-50 rounded-lg p-3">
                                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.expected_participants') }}</p>
                                    <p class="text-sm font-semibold text-slate-700">{{ $application->expected_participants }}</p>
                                </div>
                            @endif

                            {{-- Target Audience --}}
                            @if($application->target_audience)
                                <div class="col-span-2 bg-slate-50 rounded-lg p-3">
                                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.target_audience') }}</p>
                                    <p class="text-sm font-semibold text-slate-700">{{ $application->target_audience }}</p>
                                </div>
                            @endif

                            {{-- Contact Section --}}
                            @if($application->responsible_name || $application->responsible_phone)
                                <div class="col-span-2 border-t border-slate-200 pt-3 mt-1">
                                    <p class="text-xs text-slate-400 uppercase tracking-wide mb-2">{{ __('event_applications.sections.responsible_contact') }}</p>
                                </div>

                                @if($application->responsible_name)
                                    <div class="bg-slate-50 rounded-lg p-3">
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
                            @endif

                        </div>
                    </div>

                    {{-- Timeline History --}}
                    <div class="card">
                        <h3 class="text-base font-semibold text-slate-800 pb-3 mb-4 border-b border-slate-100">
                            {{ __('event_applications.sections.history') }}
                        </h3>

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
                            {{-- Vertical connecting line --}}
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

                                {{-- Key dates --}}
                                @if($application->submitted_at)
                                    <div class="flex items-start relative">
                                        <div class="flex-shrink-0 z-10">
                                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center ring-4 ring-white">
                                                <x-heroicon-s-bell class="h-4 w-4 text-blue-500" />
                                            </div>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <p class="text-sm font-medium text-slate-800">{{ __('event_applications.labels.submitted_at') }}</p>
                                            <p class="text-xs text-slate-500">
                                                {{ $application->submitted_at->format('d/m/Y H:i') }}
                                                <span class="text-slate-400">&middot;</span>
                                                {{ $application->submitted_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                <div class="flex items-start relative">
                                    <div class="flex-shrink-0 z-10">
                                        <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center ring-4 ring-white">
                                            <x-heroicon-s-clock class="h-4 w-4 text-gray-400" />
                                        </div>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm font-medium text-slate-800">{{ __('common.created_at') }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ $application->created_at->format('d/m/Y H:i') }}
                                            <span class="text-slate-400">&middot;</span>
                                            {{ $application->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Admin Notes --}}
                    @if($application->admin_notes)
                        <div class="card">
                            <h3 class="text-base font-semibold text-slate-800 pb-3 mb-4 border-b border-slate-100">{{ __('event_applications.labels.admin_notes') }}</h3>
                            <p class="text-sm text-slate-600 whitespace-pre-wrap">{{ $application->admin_notes }}</p>
                        </div>
                    @endif

                </div>

            </div>

        </div>

    </div>
</x-layout>
