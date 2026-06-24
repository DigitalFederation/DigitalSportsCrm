<x-layout>
    <div class="space-y-6" x-data="{ activeTab: '{{ ($showTemplatesTab ?? true) ? 'templates' : 'applications' }}', loaded: false }" x-init="setTimeout(() => loaded = true, 100)">

        {{-- Header Card --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
             x-show="loaded"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0">

            <div class="px-6 py-6 sm:px-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    {{-- Left: Info --}}
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-indigo-100">
                                <x-heroicon-s-clipboard-document-list class="w-6 h-6 text-indigo-600" />
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                                {{ __('event_applications.titles.applications') }}
                            </span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">{{ __('event_applications.titles.applications') }}</h1>
                        <p class="mt-1 text-gray-500 text-sm">{{ __('event_applications.header.subtitle') }}</p>
                    </div>

                    {{-- Right: Total Count Badge --}}
                    <div class="flex-shrink-0">
                        <div class="inline-flex flex-col items-center justify-center px-5 py-3 rounded-xl bg-gray-50 border border-gray-200">
                            <span class="text-3xl sm:text-4xl font-bold text-gray-900 tabular-nums">{{ ($showTemplatesTab ?? true ? $templatesList->count() : 0) + $applications->total() }}</span>
                            <span class="text-xs font-medium text-gray-500 mt-0.5">{{ __('event_applications.header.total_records') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Action Bar --}}
                <div class="mt-5 pt-4 border-t border-gray-100 flex items-center gap-4">
                    @if($showTemplatesTab ?? true)
                        <a href="{{ route($routeNamespace . '.application-templates.create') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-primary-light rounded-lg font-medium text-sm text-primary tracking-wide shadow-sm hover:bg-secondary-light focus:outline-none focus:border-primary focus:ring focus:ring-primary-light/30 transition-colors duration-150">
                            <x-heroicon-m-plus class="w-4 h-4" />
                            {{ __('event_applications.actions.create_template') }}
                        </a>
                    @endif
                    @if($showCreateButton ?? false)
                        <a href="{{ route($routeNamespace . '.event-applications.create-direct') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-primary-light rounded-lg font-medium text-sm text-primary tracking-wide shadow-sm hover:bg-secondary-light focus:outline-none focus:border-primary focus:ring focus:ring-primary-light/30 transition-colors duration-150">
                            <x-heroicon-m-plus class="w-4 h-4" />
                            {{ __('event_applications.actions.create_application') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tab Card Buttons --}}
        @php
            $showTemplatesTabFlag = $showTemplatesTab ?? true;
            $tabConfig = [
                'templates' => [
                    'icon' => 'heroicon-o-document-text',
                    'color' => 'indigo',
                    'gradient' => 'from-indigo-500 to-indigo-600',
                    'bg' => 'bg-indigo-50',
                    'ring' => 'ring-indigo-500',
                    'border' => 'border-indigo-200',
                    'iconBg' => 'bg-indigo-100',
                    'iconColor' => 'text-indigo-600',
                    'label' => __('event_applications.tabs.templates'),
                    'description' => __('event_applications.tabs.templates_description'),
                    'count' => $templatesList->count(),
                ],
                'applications' => [
                    'icon' => 'heroicon-o-inbox-stack',
                    'color' => 'emerald',
                    'gradient' => 'from-emerald-500 to-emerald-600',
                    'bg' => 'bg-emerald-50',
                    'ring' => 'ring-emerald-500',
                    'border' => 'border-emerald-200',
                    'iconBg' => 'bg-emerald-100',
                    'iconColor' => 'text-emerald-600',
                    'label' => __('event_applications.tabs.applications'),
                    'description' => __('event_applications.tabs.applications_description'),
                    'count' => $applications->total(),
                ],
            ];
        @endphp

        @if($showTemplatesTabFlag)
        <div class="grid grid-cols-2 gap-3 sm:gap-4"
             x-show="loaded"
             x-transition:enter="transition ease-out duration-500 delay-100"
             x-transition:enter-start="opacity-0 transform translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0">
            @foreach($tabConfig as $tabKey => $tc)
                <button @click="activeTab = '{{ $tabKey }}'"
                        class="relative group text-left bg-white rounded-xl border-2 p-4 sm:p-5 transition-all duration-300 ease-out focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $tc['ring'] }}"
                        :class="activeTab === '{{ $tabKey }}'
                            ? '{{ $tc['border'] }} {{ $tc['bg'] }} shadow-md ring-2 {{ $tc['ring'] }}'
                            : 'border-gray-100 hover:border-gray-200 hover:shadow-md'"
                        :aria-pressed="activeTab === '{{ $tabKey }}' ? 'true' : 'false'"
                        aria-label="{{ $tc['label'] }}">

                    {{-- Active Indicator --}}
                    <div x-show="activeTab === '{{ $tabKey }}'" x-cloak
                         class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2">
                        <span class="flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $tc['iconBg'] }} opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-gradient-to-r {{ $tc['gradient'] }}"></span>
                        </span>
                    </div>

                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-medium truncate"
                               :class="activeTab === '{{ $tabKey }}' ? '{{ $tc['iconColor'] }}' : 'text-gray-500'">
                                {{ $tc['label'] }}
                            </p>
                            <p class="mt-1 text-2xl sm:text-3xl font-bold text-gray-900 tabular-nums transition-transform duration-200"
                               :class="activeTab === '{{ $tabKey }}' ? 'scale-105' : 'group-hover:scale-105'">
                                {{ $tc['count'] }}
                            </p>
                            <p class="mt-2 text-sm text-gray-500">{{ $tc['description'] }}</p>
                        </div>
                        <div class="flex-shrink-0 p-2.5 sm:p-3 rounded-xl {{ $tc['iconBg'] }} transition-transform duration-200"
                             :class="activeTab === '{{ $tabKey }}' ? 'scale-110' : 'group-hover:scale-110'">
                            <x-dynamic-component :component="$tc['icon']" class="w-5 h-5 sm:w-6 sm:h-6 {{ $tc['iconColor'] }}" />
                        </div>
                    </div>
                </button>
            @endforeach
        </div>
        @endif

        {{-- Templates Tab Content --}}
        @if($showTemplatesTabFlag)
        <div x-show="activeTab === 'templates'" x-cloak>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                 x-show="loaded"
                 x-transition:enter="transition ease-out duration-500 delay-200"
                 x-transition:enter-start="opacity-0 transform translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0">

                {{-- Section Header --}}
                <div class="border-b border-gray-200 bg-gray-50/50 px-5 py-3">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100">
                            <x-heroicon-o-document-text class="w-4 h-4 text-indigo-600" />
                        </div>
                        <span class="text-sm font-semibold text-gray-700">{{ __('event_applications.tabs.templates') }}</span>
                        <span class="inline-flex items-center justify-center min-w-[1.5rem] px-1.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-600">
                            {{ $templatesList->count() }}
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-auto w-full divide-y divide-slate-200">
                        <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-slate-200">
                            <tr>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('event_applications.labels.template_name') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('event_applications.labels.event_type') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-center">{{ __('event_applications.labels.state') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-center">{{ __('event_applications.labels.applications_received') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('event_applications.labels.submission_period') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-right">{{ __('common.actions') }}</div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-200">
                            @forelse($templatesList as $template)
                                <tr class="table-row">
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <a href="{{ route($routeNamespace . '.application-templates.show', $template) }}" class="font-medium text-primary hover:underline">
                                            {{ $template->name }}
                                        </a>
                                        @if($template->sport)
                                            <div class="text-xs text-slate-500">{{ $template->sport->name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                            {{ __('event_applications.event_types.' . $template->event_type) }}
                                        </span>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="flex justify-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $template->stateColor }}-100 text-{{ $template->stateColor }}-700">
                                                {{ __('event_applications.template_states.' . $template->state) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="text-center text-sm text-slate-800">
                                            {{ $template->active_applications_count }}
                                        </div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="text-sm text-slate-800">
                                            @if($template->submission_start_date && $template->submission_end_date)
                                                {{ $template->submission_start_date->format('d/m/Y') }} - {{ $template->submission_end_date->format('d/m/Y') }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="space-x-1 flex justify-end items-end">
                                            <x-dynamic-table-buttons type="show" :route="route($routeNamespace . '.application-templates.show', $template)" />
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-2 first:pl-5 last:pr-5 py-12 text-center">
                                        <div class="text-slate-500">{{ __('event_applications.messages.no_templates') }}</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- Applications Tab Content --}}
        <div x-show="activeTab === 'applications'" x-cloak>
            <x-filter-form :post="route($routeNamespace . '.event-applications.index')">
                <x-forms.filter-input-select
                    :label="__('event_applications.filters.filter_by_state')"
                    name="status"
                    :options="[
                        'draft' => __('event_applications.states.draft'),
                        'submitted' => __('event_applications.states.submitted'),
                        'in_validation' => __('event_applications.states.in_validation'),
                        'returned_for_correction' => __('event_applications.states.returned_for_correction'),
                        'approved' => __('event_applications.states.approved'),
                        'rejected' => __('event_applications.states.rejected'),
                        'published' => __('event_applications.states.published'),
                    ]" />

                <x-forms.filter-input-select
                    :label="__('event_applications.filters.filter_by_type')"
                    name="application_type"
                    :options="[
                        \App\Enums\EventApplicationTypeEnum::FederationInitiated->value => __('event_applications.types.federation_initiated'),
                        \App\Enums\EventApplicationTypeEnum::DirectSubmission->value => __('event_applications.types.direct_submission'),
                    ]" />

                <x-forms.filter-input-select
                    :label="__('event_applications.labels.event_type')"
                    name="event_type"
                    :options="[
                        'organization' => __('event_applications.event_types.organization'),
                        'competition' => __('event_applications.event_types.competition'),
                    ]" />

                <x-forms.filter-input-select
                    :label="__('event_applications.filters.filter_by_sport')"
                    name="sport_id"
                    :options="$sports" />

                <x-forms.filter-input-select
                    :label="__('event_applications.filters.filter_by_template')"
                    name="template_id"
                    :options="$templates" />

                <x-forms.filter-input-text
                    :label="__('common.search')"
                    name="event_name"
                    :placeholder="__('event_applications.filters.search_placeholder')"
                />
            </x-filter-form>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                 x-show="loaded"
                 x-transition:enter="transition ease-out duration-500 delay-200"
                 x-transition:enter-start="opacity-0 transform translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0">

                {{-- Section Header --}}
                <div class="border-b border-gray-200 bg-gray-50/50 px-5 py-3">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-100">
                            <x-heroicon-o-inbox-stack class="w-4 h-4 text-emerald-600" />
                        </div>
                        <span class="text-sm font-semibold text-gray-700">{{ __('event_applications.tabs.applications') }}</span>
                        <span class="inline-flex items-center justify-center min-w-[1.5rem] px-1.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-600">
                            {{ $applications->total() }}
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-auto w-full divide-y divide-slate-200">
                        <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-slate-200">
                            <tr>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('event_applications.labels.applicant') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('event_applications.labels.event_name') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('event_applications.labels.submission_date') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-center">{{ __('event_applications.table.state') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-right">{{ __('common.actions') }}</div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-200">
                            @forelse($applications as $application)
                                <tr class="table-row">
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-medium text-slate-800">
                                            {{ $application->entity?->name ?? '-' }}
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            @if($application->entity_type === 'Domain\Entities\Models\Entity')
                                                {{ __('event_applications.labels.entity_type_entity') }}
                                            @else
                                                {{ __('event_applications.labels.entity_type_federation') }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-medium text-slate-800">{{ $application->event_name }}</div>
                                        <div class="text-xs text-slate-500">
                                            {{ __('event_applications.event_types.' . $application->event_type) }}
                                        </div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="text-sm text-slate-800">
                                            {{ ($application->submitted_at ?? $application->created_at)->format('d/m/Y') }}
                                        </div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="flex justify-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                  style="background-color: {{ $application->stateColor() }}20; color: {{ $application->stateColor() }};">
                                                {{ $application->stateName() }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="space-x-1 flex justify-end items-end">
                                            <x-dynamic-table-buttons type="show" :route="route($routeNamespace . '.event-applications.show', $application)" />
                                            <x-dynamic-table-buttons type="delete"
                                                :route="route($routeNamespace . '.event-applications.destroy', $application)"
                                                method="DELETE"
                                                :confirm-text="__('event_applications.confirmations.delete_application')" />
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-2 first:pl-5 last:pr-5 py-12 text-center">
                                        <div class="text-slate-500">{{ __('event_applications.messages.no_applications') }}</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-8">
                {{ $applications->links() }}
            </div>
        </div>

    </div>
</x-layout>
