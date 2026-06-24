@section('title', __('events.td_report_title') . ' - ' . $event->name)

<x-layout>
    <div class="previous-layout-classes space-y-6" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">

        {{-- Header Card --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
             x-show="loaded"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="px-6 py-6 sm:px-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    {{-- Left: Event Info --}}
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-100">
                                <x-heroicon-s-document-text class="w-6 h-6 text-primary-600" />
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-primary-50 text-primary-700 border border-primary-200">
                                {{ __('events.td_report') }}
                            </span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">{{ $event->name }}</h1>
                        <p class="mt-1 text-gray-500 text-sm">{{ __('events.td_report_subtitle') }}</p>

                        <div class="flex flex-wrap items-center gap-4 mt-3 text-sm text-gray-500">
                            <span class="inline-flex items-center gap-1.5">
                                <x-heroicon-m-calendar class="w-4 h-4 text-gray-400" />
                                {{ $event->start_date->format('d/m/Y') }} - {{ $event->end_date->format('d/m/Y') }}
                            </span>
                            @if($event->location)
                                <span class="inline-flex items-center gap-1.5">
                                    <x-heroicon-m-map-pin class="w-4 h-4 text-gray-400" />
                                    {{ $event->location }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Right: Status Badge --}}
                    <div class="flex-shrink-0">
                        @if($report && $report->is_submitted)
                            <div class="inline-flex flex-col items-center justify-center px-5 py-3 rounded-xl bg-emerald-50 border border-emerald-200">
                                <x-heroicon-m-check-circle class="w-8 h-8 text-emerald-600 mb-1" />
                                <span class="text-xs font-medium text-emerald-700">{{ __('events.report_status_submitted') }}</span>
                                <span class="text-xs text-gray-500 mt-0.5">{{ $report->submitted_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @else
                            <div class="inline-flex flex-col items-center justify-center px-5 py-3 rounded-xl bg-amber-50 border border-amber-200">
                                <x-heroicon-m-pencil-square class="w-8 h-8 text-amber-600 mb-1" />
                                <span class="text-xs font-medium text-amber-700">{{ __('events.report_status_draft') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Navigation --}}
                <div class="mt-5 pt-4 border-t border-gray-100 flex items-center gap-4">
                    <a href="{{ route('individual.technical-delegate.enrollments', $event) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-primary-light rounded-lg font-medium text-sm text-primary tracking-wide shadow-sm hover:bg-secondary-light focus:outline-none focus:border-primary focus:ring focus:ring-primary-light/30 transition-colors duration-150">
                        <x-heroicon-m-arrow-left class="w-4 h-4" />
                        {{ __('events.back_to_event') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        <div x-show="loaded"
             x-transition:enter="transition ease-out duration-500 delay-100"
             x-transition:enter-start="opacity-0 transform translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0">
            @if(session('success'))
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-emerald-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-lg bg-red-50 border border-red-200 p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-lg bg-red-50 border border-red-200 p-4">
                    <ul class="list-disc list-inside text-sm text-red-800 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Report Sections --}}
        <form action="{{ route('individual.technical-delegate.td-report.save', $event) }}" method="POST" id="reportForm">
            @csrf

            @php
                $sections = [
                    ['field' => 'participants_withdrawals', 'label' => 'td_participants_withdrawals', 'help' => 'td_participants_withdrawals_help'],
                    ['field' => 'incidents_occurrences', 'label' => 'td_incidents_occurrences', 'help' => 'td_incidents_occurrences_help'],
                    ['field' => 'officials_performance', 'label' => 'td_officials_performance', 'help' => 'td_officials_performance_help'],
                    ['field' => 'facilities_evaluation', 'label' => 'td_facilities_evaluation', 'help' => 'td_facilities_evaluation_help'],
                    ['field' => 'safety_first_aid', 'label' => 'td_safety_first_aid', 'help' => 'td_safety_first_aid_help'],
                    ['field' => 'anti_doping_control', 'label' => 'td_anti_doping', 'help' => 'td_anti_doping_help'],
                    ['field' => 'sports_protests', 'label' => 'td_sports_protests', 'help' => 'td_sports_protests_help'],
                    ['field' => 'observations_recommendations', 'label' => 'td_observations', 'help' => 'td_observations_help'],
                ];
            @endphp

            <div class="space-y-6">
                @foreach($sections as $index => $section)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                         x-show="loaded"
                         x-transition:enter="transition ease-out duration-500 delay-{{ ($index + 2) * 100 }}"
                         x-transition:enter-start="opacity-0 transform translate-y-4"
                         x-transition:enter-end="opacity-100 transform translate-y-0">
                        <div class="p-4 sm:p-6">
                            <div class="flex items-start gap-3 mb-4">
                                <div class="flex-shrink-0 flex items-center justify-center w-8 h-8 bg-slate-800 rounded-lg">
                                    <span class="text-white font-semibold text-sm">{{ $index + 1 }}</span>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">{{ __('events.' . $section['label']) }}</h2>
                                    <p class="text-sm text-gray-500 mt-0.5">{{ __('events.' . $section['help']) }}</p>
                                </div>
                            </div>
                            <textarea name="{{ $section['field'] }}"
                                      rows="4"
                                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors"
                                      @if($report && $report->is_submitted) disabled @endif
                            >{{ old($section['field'], $report->{$section['field']} ?? '') }}</textarea>
                        </div>
                    </div>
                @endforeach

                {{-- Action Buttons --}}
                @if(!$report || !$report->is_submitted)
                    <div class="flex justify-end gap-3 pt-4"
                         x-show="loaded"
                         x-transition:enter="transition ease-out duration-500 delay-1000"
                         x-transition:enter-start="opacity-0 transform translate-y-4"
                         x-transition:enter-end="opacity-100 transform translate-y-0">
                        <button type="submit" class="btn btn-secondary">
                            {{ __('events.save_draft') }}
                        </button>
                        @if($report)
                            <button type="button"
                                    onclick="if(confirm('{{ __('events.confirm_submit_report') }}')) { document.getElementById('submitReportForm').submit(); }"
                                    class="btn btn-primary">
                                {{ __('events.submit_report') }}
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </form>

        @if($report && !$report->is_submitted)
            <form action="{{ route('individual.technical-delegate.td-report.submit', $event) }}" method="POST" id="submitReportForm" class="hidden">
                @csrf
            </form>
        @endif

        {{-- Document Upload Section --}}
        <div x-show="loaded"
             x-transition:enter="transition ease-out duration-500 delay-1100"
             x-transition:enter-start="opacity-0 transform translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0">
            <x-evt_event.report-document-upload
                :report="$report"
                :event="$event"
                upload-route="individual.technical-delegate.td-report.upload"
                download-route-prefix="individual.technical-delegate.td-report.document.download"
                delete-route-prefix="individual.technical-delegate.td-report.document.delete"
            />
        </div>
    </div>
</x-layout>
