@section('title', __('events.admin_reports_title') . ' - ' . $event->name)

<x-layout>
    <style>.scrollbar-hide::-webkit-scrollbar{display:none}.scrollbar-hide{-ms-overflow-style:none;scrollbar-width:none}</style>

    <div x-data="{ loaded: false, activeTab: 'reports', tdExpanded: true, cjExpanded: true, notesModalOpen: false, notesModalTitle: '', notesModalContent: '' }"
         x-init="setTimeout(() => loaded = true, 100)"
         class="space-y-6">

        {{-- [A] HEADER CARD --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
             x-show="loaded"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0">

            <div class="px-6 py-6 sm:px-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-slate-100">
                                <x-heroicon-s-document-text class="w-6 h-6 text-slate-600" />
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-slate-50 text-slate-700 border border-slate-200">
                                {{ __('events.admin_reports_title') }}
                            </span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">{{ $event->name }}</h1>
                        @if($event->description)
                            <p class="mt-1 text-gray-500 text-sm">{{ Str::limit($event->description, 120) }}</p>
                        @endif

                        <div class="flex flex-wrap items-center gap-4 mt-3 text-sm text-gray-500">
                            @if($event->start_date)
                                <span class="inline-flex items-center gap-1.5">
                                    <x-heroicon-m-calendar class="w-4 h-4 text-gray-400" />
                                    {{ $event->start_date->format('d/m/Y') }}
                                    @if($event->end_date && $event->end_date->ne($event->start_date))
                                        - {{ $event->end_date->format('d/m/Y') }}
                                    @endif
                                </span>
                            @endif
                            @if($event->location)
                                <span class="inline-flex items-center gap-1.5">
                                    <x-heroicon-m-map-pin class="w-4 h-4 text-gray-400" />
                                    {{ $event->location }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex-shrink-0">
                        <div class="inline-flex flex-col items-center justify-center px-5 py-3 rounded-xl bg-gray-50 border border-gray-200">
                            <span class="text-3xl sm:text-4xl font-bold text-gray-900 tabular-nums">{{ $refereeEnrollments->count() }}</span>
                            <span class="text-xs font-medium text-gray-500 mt-0.5">{{ __('events.technical_officials') }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-gray-100 flex items-center gap-4">
                    <a href="{{ route('admin.evt-events.events.show', $event) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg font-medium text-sm text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-150">
                        <x-heroicon-m-arrow-left class="w-4 h-4" />
                        {{ __('events.back_to_events') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- [B] SUMMARY CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4"
             x-show="loaded"
             x-transition:enter="transition ease-out duration-500 delay-100"
             x-transition:enter-start="opacity-0 transform translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0">

            {{-- Technical Delegate Card --}}
            <div class="bg-white rounded-xl border-2 border-gray-100 p-4 sm:p-5">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">{{ __('events.technical_delegate') }}</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 truncate">
                            @if($event->technicalDelegate?->individual)
                                {{ $event->technicalDelegate->individual->name }} {{ $event->technicalDelegate->individual->surname }}
                            @else
                                <span class="text-gray-400 font-normal">{{ __('events.not_assigned') }}</span>
                            @endif
                        </p>
                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                            @if($event->technicalDelegateReport?->is_submitted)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-700">
                                    <x-heroicon-m-check class="w-3 h-3 mr-0.5" />
                                    {{ __('events.report_status_submitted') }}
                                </span>
                            @elseif($event->technicalDelegateReport)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">
                                    {{ __('events.report_status_draft') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">
                                    {{ __('events.no_report') }}
                                </span>
                            @endif
                            @if($event->technicalDelegateReport?->documents)
                                <span class="text-xs text-gray-400">
                                    {{ trans_choice('events.documents_count', $event->technicalDelegateReport->documents->count(), ['count' => $event->technicalDelegateReport->documents->count()]) }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="flex-shrink-0 p-2.5 sm:p-3 rounded-xl bg-blue-100">
                        <x-heroicon-o-clipboard-document-check class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" />
                    </div>
                </div>
            </div>

            {{-- Chief Judge Card --}}
            <div class="bg-white rounded-xl border-2 border-gray-100 p-4 sm:p-5">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">{{ __('events.chief_judge') }}</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 truncate">
                            @if($event->chiefJudge?->individual)
                                {{ $event->chiefJudge->individual->name }} {{ $event->chiefJudge->individual->surname }}
                            @else
                                <span class="text-gray-400 font-normal">{{ __('events.not_assigned') }}</span>
                            @endif
                        </p>
                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                            @if($event->chiefJudgeReport?->is_submitted)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-700">
                                    <x-heroicon-m-check class="w-3 h-3 mr-0.5" />
                                    {{ __('events.report_status_submitted') }}
                                </span>
                            @elseif($event->chiefJudgeReport)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">
                                    {{ __('events.report_status_draft') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">
                                    {{ __('events.no_report') }}
                                </span>
                            @endif
                            @if($event->chiefJudgeReport?->documents)
                                <span class="text-xs text-gray-400">
                                    {{ trans_choice('events.documents_count', $event->chiefJudgeReport->documents->count(), ['count' => $event->chiefJudgeReport->documents->count()]) }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="flex-shrink-0 p-2.5 sm:p-3 rounded-xl bg-purple-100">
                        <x-heroicon-o-scale class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600" />
                    </div>
                </div>
            </div>

            {{-- Referees Summary Card --}}
            <div class="bg-white rounded-xl border-2 border-gray-100 p-4 sm:p-5">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">{{ __('events.technical_officials') }}</p>
                        <p class="mt-1 text-2xl sm:text-3xl font-bold text-gray-900 tabular-nums">{{ $refereeEnrollments->count() }}</p>
                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                            @if($refereeEnrollments->count() > 0)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                                    {{ __('events.referees_present', ['count' => $refereePresentCount, 'total' => $refereeEnrollments->count()]) }}
                                </span>
                                @if($refereeEvaluatedCount > 0)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                                        {{ __('events.referees_evaluated', ['count' => $refereeEvaluatedCount]) }}
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="flex-shrink-0 p-2.5 sm:p-3 rounded-xl bg-emerald-100">
                        <x-heroicon-o-user-group class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-600" />
                    </div>
                </div>
            </div>
        </div>

        {{-- [C] MAIN CONTENT WITH TABS --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
             x-show="loaded"
             x-transition:enter="transition ease-out duration-500 delay-200"
             x-transition:enter-start="opacity-0 transform translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0">

            {{-- Tab Navigation --}}
            <div class="border-b border-gray-200 bg-gray-50/50">
                <nav class="flex overflow-x-auto scrollbar-hide -mb-px" aria-label="Tabs">
                    <button @click="activeTab = 'reports'"
                            :class="activeTab === 'reports'
                                ? 'border-slate-500 text-slate-600 bg-white'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 hover:bg-gray-50'"
                            class="relative flex-shrink-0 px-4 sm:px-6 py-4 text-sm font-medium transition-all duration-200 border-b-2 focus:outline-none"
                            role="tab"
                            :aria-selected="activeTab === 'reports' ? 'true' : 'false'">
                        <span class="flex items-center gap-2">
                            <x-heroicon-o-document-text class="w-4 h-4 sm:w-5 sm:h-5" />
                            <span class="hidden sm:inline">{{ __('events.reports_tab') }}</span>
                        </span>
                        <template x-if="activeTab === 'reports'">
                            <span class="absolute bottom-0 inset-x-0 h-0.5 bg-gradient-to-r from-slate-400 via-slate-500 to-slate-400"></span>
                        </template>
                    </button>

                    <button @click="activeTab = 'referees'"
                            :class="activeTab === 'referees'
                                ? 'border-emerald-500 text-emerald-600 bg-white'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 hover:bg-gray-50'"
                            class="relative flex-shrink-0 px-4 sm:px-6 py-4 text-sm font-medium transition-all duration-200 border-b-2 focus:outline-none"
                            role="tab"
                            :aria-selected="activeTab === 'referees' ? 'true' : 'false'">
                        <span class="flex items-center gap-2">
                            <x-heroicon-o-user-group class="w-4 h-4 sm:w-5 sm:h-5" />
                            <span class="hidden sm:inline">{{ __('events.referee_assignments_tab') }}</span>
                            <span class="inline-flex items-center justify-center min-w-[1.5rem] px-1.5 py-0.5 rounded-full text-xs font-semibold transition-colors"
                                  :class="activeTab === 'referees' ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-600'">
                                {{ $refereeEnrollments->count() }}
                            </span>
                        </span>
                        <template x-if="activeTab === 'referees'">
                            <span class="absolute bottom-0 inset-x-0 h-0.5 bg-gradient-to-r from-emerald-400 via-emerald-500 to-emerald-400"></span>
                        </template>
                    </button>
                </nav>
            </div>

            {{-- TAB 1: Reports --}}
            <div x-show="activeTab === 'reports'" x-cloak class="divide-y divide-gray-200">

                {{-- Competition Director --}}
                <div class="px-6 py-5 sm:px-8">
                    <div class="flex items-center gap-3 mb-3">
                        <x-heroicon-o-megaphone class="w-5 h-5 text-gray-400" />
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">{{ __('events.competition_director_label') }}</h3>
                    </div>
                    @if($event->competitionDirector?->individual)
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0 w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                <x-heroicon-s-user class="w-4 h-4 text-gray-500" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $event->competitionDirector->individual->name }} {{ $event->competitionDirector->individual->surname }}</p>
                                @if($event->competitionDirector->individual->email)
                                    <p class="text-xs text-gray-500">{{ $event->competitionDirector->individual->email }}</p>
                                @endif
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-400 italic">{{ __('events.no_director_assigned') }}</p>
                    @endif
                </div>

                {{-- TD Report --}}
                <div class="px-6 py-5 sm:px-8">
                    <button @click="tdExpanded = !tdExpanded"
                            class="flex items-center justify-between w-full text-left group">
                        <div class="flex items-center gap-3">
                            <x-heroicon-o-clipboard-document-check class="w-5 h-5 text-blue-500" />
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">{{ __('events.td_report_title') }}</h3>
                            @if($event->technicalDelegateReport?->is_submitted)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                    <x-heroicon-s-check class="w-3 h-3" />
                                    {{ __('events.report_status_submitted') }}
                                </span>
                            @elseif($event->technicalDelegateReport)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                    {{ __('events.report_status_draft') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                    {{ __('events.no_report') }}
                                </span>
                            @endif
                        </div>
                        <x-heroicon-m-chevron-down class="w-5 h-5 text-gray-400 transition-transform duration-200 group-hover:text-gray-600"
                                                   ::class="tdExpanded ? 'rotate-180' : ''" />
                    </button>

                    <div x-show="tdExpanded" x-collapse>
                        @if($event->technicalDelegateReport)
                            @if($event->technicalDelegateReport->is_submitted && $event->technicalDelegateReport->submitted_at)
                                <p class="mt-3 text-xs text-gray-500">
                                    {{ __('events.submitted_at') }}: {{ $event->technicalDelegateReport->submitted_at->format('d/m/Y H:i') }}
                                </p>
                            @endif

                            @php
                                $reportSections = [
                                    ['key' => 'participants_withdrawals', 'label' => __('events.td_participants_withdrawals')],
                                    ['key' => 'incidents_occurrences', 'label' => __('events.td_incidents_occurrences')],
                                    ['key' => 'officials_performance', 'label' => __('events.td_officials_performance')],
                                    ['key' => 'facilities_evaluation', 'label' => __('events.td_facilities_evaluation')],
                                    ['key' => 'safety_first_aid', 'label' => __('events.td_safety_first_aid')],
                                    ['key' => 'anti_doping_control', 'label' => __('events.td_anti_doping')],
                                    ['key' => 'sports_protests', 'label' => __('events.td_sports_protests')],
                                    ['key' => 'observations_recommendations', 'label' => __('events.td_observations')],
                                ];
                                $hasContent = collect($reportSections)->contains(fn ($s) => $event->technicalDelegateReport->{$s['key']});
                            @endphp

                            @if($hasContent)
                                <div class="mt-4 space-y-4">
                                    @foreach($reportSections as $index => $section)
                                        @if($event->technicalDelegateReport->{$section['key']})
                                            <div class="border-b border-gray-100 pb-4 last:border-0 last:pb-0">
                                                <div class="flex items-start gap-3">
                                                    <div class="flex-shrink-0 w-7 h-7 bg-slate-800 rounded-lg flex items-center justify-center">
                                                        <span class="text-white font-semibold text-xs">{{ $index + 1 }}</span>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <h4 class="text-sm font-medium text-gray-900 mb-1">{{ $section['label'] }}</h4>
                                                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $event->technicalDelegateReport->{$section['key']} }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-4 text-sm text-gray-500 italic">{{ __('events.report_empty') }}</p>
                            @endif

                            {{-- TD Report Documents --}}
                            @if($event->technicalDelegateReport->documents->count() > 0)
                                <div class="mt-4 pt-4 border-t border-gray-100">
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">{{ __('events.attached_documents') }}</h4>
                                    <div class="space-y-1.5">
                                        @foreach($event->technicalDelegateReport->documents as $document)
                                            <div class="flex items-center justify-between p-2.5 bg-gray-50 rounded-lg">
                                                <div class="flex items-center gap-2">
                                                    <x-heroicon-o-document class="w-4 h-4 text-gray-400" />
                                                    <span class="text-sm text-gray-700">{{ $document->original_filename }}</span>
                                                </div>
                                                <span class="text-xs text-gray-400">{{ $document->created_at->format('d/m/Y') }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="mt-4 text-center py-6">
                                <div class="mx-auto flex items-center justify-center w-12 h-12 rounded-full bg-gray-100">
                                    <x-heroicon-o-document class="w-6 h-6 text-gray-400" />
                                </div>
                                <p class="mt-2 text-sm text-gray-500">{{ __('events.no_report') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- CJ Report --}}
                <div class="px-6 py-5 sm:px-8">
                    <button @click="cjExpanded = !cjExpanded"
                            class="flex items-center justify-between w-full text-left group">
                        <div class="flex items-center gap-3">
                            <x-heroicon-o-scale class="w-5 h-5 text-purple-500" />
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">{{ __('events.cj_report_title') }}</h3>
                            @if($event->chiefJudgeReport?->is_submitted)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                    <x-heroicon-s-check class="w-3 h-3" />
                                    {{ __('events.report_status_submitted') }}
                                </span>
                            @elseif($event->chiefJudgeReport)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                    {{ __('events.report_status_draft') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                    {{ __('events.no_report') }}
                                </span>
                            @endif
                        </div>
                        <x-heroicon-m-chevron-down class="w-5 h-5 text-gray-400 transition-transform duration-200 group-hover:text-gray-600"
                                                   ::class="cjExpanded ? 'rotate-180' : ''" />
                    </button>

                    <div x-show="cjExpanded" x-collapse>
                        @if($event->chiefJudgeReport)
                            @if($event->chiefJudgeReport->is_submitted && $event->chiefJudgeReport->submitted_at)
                                <p class="mt-3 text-xs text-gray-500">
                                    {{ __('events.submitted_at') }}: {{ $event->chiefJudgeReport->submitted_at->format('d/m/Y H:i') }}
                                </p>
                            @endif

                            @if($event->chiefJudgeReport->technical_considerations)
                                <div class="mt-4">
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">{{ __('events.cj_technical_considerations') }}</h4>
                                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $event->chiefJudgeReport->technical_considerations }}</p>
                                </div>
                            @else
                                <p class="mt-4 text-sm text-gray-500 italic">{{ __('events.report_empty') }}</p>
                            @endif

                            {{-- CJ Report Documents --}}
                            @if($event->chiefJudgeReport->documents->count() > 0)
                                <div class="mt-4 pt-4 border-t border-gray-100">
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">{{ __('events.attached_documents') }}</h4>
                                    <div class="space-y-1.5">
                                        @foreach($event->chiefJudgeReport->documents as $document)
                                            <div class="flex items-center justify-between p-2.5 bg-gray-50 rounded-lg">
                                                <div class="flex items-center gap-2">
                                                    <x-heroicon-o-document class="w-4 h-4 text-gray-400" />
                                                    <span class="text-sm text-gray-700">{{ $document->original_filename }}</span>
                                                </div>
                                                <span class="text-xs text-gray-400">{{ $document->created_at->format('d/m/Y') }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="mt-4 text-center py-6">
                                <div class="mx-auto flex items-center justify-center w-12 h-12 rounded-full bg-gray-100">
                                    <x-heroicon-o-document class="w-6 h-6 text-gray-400" />
                                </div>
                                <p class="mt-2 text-sm text-gray-500">{{ __('events.no_report') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- TAB 2: Referee Assignments --}}
            <div x-show="activeTab === 'referees'" x-cloak>
                @if($refereeEnrollments->count() > 0)
                    <div class="px-6 py-4 sm:px-8 flex items-center justify-end gap-2 border-b border-gray-100">
                        <form method="POST" action="{{ route('admin.evt-events.events.reports.export-excel', $event) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-150">
                                <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                                {{ __('events.export_report_excel') }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.evt-events.events.reports.export-pdf', $event) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-150">
                                <x-heroicon-o-document-arrow-down class="w-4 h-4" />
                                {{ __('events.export_report_pdf') }}
                            </button>
                        </form>
                    </div>
                    <div class="overflow-x-auto scrollbar-hide">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('events.technical_official') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('events.email') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('events.assigned_functions') }}
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('events.presence') }}
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('events.competition_days') }}
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('events.number_of_games') }}
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('events.evaluation') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('events.evaluation_notes') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('events.notes') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($refereeEnrollments as $enrollment)
                                    @php
                                        $assignments = $enrollment->refereeFunctionAssignments;
                                        $isPresent = $assignments->contains('is_present', true);
                                        $allPresent = $assignments->isNotEmpty() && $assignments->every('is_present', true);
                                        $totalCompetitionDays = $assignments->sum('competition_days');
                                        $totalGames = $assignments->sum('number_of_games');
                                        $assignmentNotes = $assignments->pluck('notes')->filter()->implode('; ');

                                        $evalLabel = match($enrollment->evaluation) {
                                            1 => __('events.evaluation_insufficient'),
                                            2 => __('events.evaluation_regular_flaws'),
                                            3 => __('events.evaluation_regular'),
                                            4 => __('events.evaluation_excellent'),
                                            5 => __('events.evaluation_high_prestige'),
                                            default => null,
                                        };
                                        $evalColor = match($enrollment->evaluation) {
                                            1 => 'bg-red-100 text-red-800',
                                            2 => 'bg-amber-100 text-amber-800',
                                            3 => 'bg-yellow-100 text-yellow-800',
                                            4 => 'bg-emerald-100 text-emerald-800',
                                            5 => 'bg-blue-100 text-blue-800',
                                            default => '',
                                        };
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="font-medium text-sm text-gray-900">
                                                {{ $enrollment->individual?->name }} {{ $enrollment->individual?->surname }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                            {{ $enrollment->individual?->email ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($assignments->isNotEmpty())
                                                <div class="flex flex-col gap-1">
                                                    @foreach($assignments as $assignment)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 w-fit">
                                                            {{ $assignment->function_name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-sm">{{ __('events.no_function_assigned') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            @if($assignments->isNotEmpty())
                                                @if($allPresent)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                                        <x-heroicon-s-check class="w-3.5 h-3.5" />
                                                        {{ __('events.all_present') }}
                                                    </span>
                                                @elseif($isPresent)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                                        <x-heroicon-s-minus class="w-3.5 h-3.5" />
                                                        {{ __('events.partially_present') }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                                        <x-heroicon-s-x-mark class="w-3.5 h-3.5" />
                                                        {{ __('events.not_present') }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-gray-700 tabular-nums">
                                            {{ $totalCompetitionDays ?: '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center text-sm text-gray-700 tabular-nums">
                                            {{ $totalGames ?: '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            @if($evalLabel)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $evalColor }}">
                                                    {{ $enrollment->evaluation }} - {{ $evalLabel }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400">{{ __('events.no_evaluation') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 text-center">
                                            @if($enrollment->evaluation_notes)
                                                <button type="button"
                                                        @click="notesModalTitle = '{{ __('events.evaluation_notes') }}'; notesModalContent = {{ Js::from($enrollment->evaluation_notes) }}; notesModalOpen = true"
                                                        class="inline-flex items-center justify-center p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                                                        title="{{ __('events.view_notes') }}">
                                                    <x-heroicon-o-chat-bubble-bottom-center-text class="w-5 h-5" />
                                                </button>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 text-center">
                                            @if($assignmentNotes)
                                                <button type="button"
                                                        @click="notesModalTitle = '{{ __('events.notes') }}'; notesModalContent = {{ Js::from($assignmentNotes) }}; notesModalOpen = true"
                                                        class="inline-flex items-center justify-center p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                                                        title="{{ __('events.view_notes') }}">
                                                    <x-heroicon-o-chat-bubble-bottom-center-text class="w-5 h-5" />
                                                </button>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="mx-auto flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-gray-100 to-gray-200">
                            <x-heroicon-o-user-group class="w-8 h-8 text-gray-400" />
                        </div>
                        <h3 class="mt-3 text-sm font-semibold text-gray-900">{{ __('events.no_referees_enrolled_in_event') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('events.no_technical_officials_desc') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Notes Modal --}}
    <div x-show="notesModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="notes-modal-title"
         role="dialog"
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="notesModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="notesModalOpen = false"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div x-show="notesModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 @keydown.escape.window="notesModalOpen = false"
                 class="relative inline-block w-full max-w-lg p-6 my-8 text-left align-middle bg-white rounded-xl shadow-xl transform transition-all">
                <div class="flex items-center justify-between mb-4">
                    <h3 id="notes-modal-title" class="text-lg font-semibold text-gray-900" x-text="notesModalTitle"></h3>
                    <button @click="notesModalOpen = false" type="button" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>
                <div class="text-sm text-gray-700 whitespace-pre-wrap max-h-96 overflow-y-auto" x-text="notesModalContent"></div>
                <div class="mt-5 flex justify-end">
                    <button @click="notesModalOpen = false" type="button"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-medium text-sm text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-150">
                        {{ __('events.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layout>
