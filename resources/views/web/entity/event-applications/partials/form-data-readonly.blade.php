@php
    $fd = $application->form_data;
    $showAllFields = isset($routeNamespace);
@endphp

{{-- Event Location --}}
@if(!empty($fd['address']) || !empty($fd['postal_code']) || !empty($fd['location']) || $application->event_type || $application->sport || $application->district || $application->municipality || $application->category || $application->target_audience || $application->expected_participants || $showAllFields)
    <div class="card" x-data="{ open: false }">
        <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left pb-3" x-bind:class="open && 'border-b border-slate-200'">
            <div class="flex items-center gap-2">
                <x-heroicon-m-map-pin class="w-5 h-5 text-blue-500" />
                <h2 class="text-lg font-semibold text-slate-800">{{ __('event_applications.sections.event_location') }}</h2>
            </div>
            <x-heroicon-m-chevron-down class="w-5 h-5 text-gray-400 transition-transform" x-bind:class="open && 'rotate-180'" />
        </button>
        <div x-show="open" x-transition class="mt-4 space-y-4">
            {{-- Event classification fields --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @if($application->event_type || $showAllFields)
                    <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                        <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">{{ __('event_applications.labels.event_type') }}</p>
                        <p class="text-sm font-semibold text-blue-700">{{ $application->event_type ? __('event_applications.event_types.' . $application->event_type) : '-' }}</p>
                    </div>
                @endif

                @if(($application->event_type === 'organization' && $application->event_category) || $showAllFields)
                    <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                        <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">{{ __('event_applications.labels.event_category') }}</p>
                        <p class="text-sm font-semibold text-blue-700">{{ $application->event_category ? \App\Enums\EvtEventOrganizationCategoryEnum::toString($application->event_category) : '-' }}</p>
                    </div>
                @endif

                @if($application->sport || $showAllFields)
                    <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                        <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">{{ __('event_applications.labels.sport') }}</p>
                        <p class="text-sm font-semibold text-blue-700 flex items-center gap-2">
                            <x-heroicon-s-bolt class="w-4 h-4" />
                            {{ $application->sport?->name ?? '-' }}
                        </p>
                    </div>
                @endif

                @if($application->category || $showAllFields)
                    <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                        <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">{{ __('event_applications.labels.category') }}</p>
                        <p class="text-sm font-semibold text-blue-700">{{ $application->category ? __('event_applications.categories.' . $application->category) : '-' }}</p>
                    </div>
                @endif

                @if($application->target_audience || $showAllFields)
                    <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                        <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">{{ __('event_applications.labels.target_audience') }}</p>
                        <p class="text-sm font-semibold text-blue-700">{{ $application->target_audience ?: '-' }}</p>
                    </div>
                @endif

                @if($application->expected_participants || $showAllFields)
                    <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                        <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">{{ __('event_applications.labels.expected_participants') }}</p>
                        <p class="text-sm font-semibold text-blue-700">{{ $application->expected_participants ?: '-' }}</p>
                    </div>
                @endif

            </div>

            {{-- Address (full width) --}}
            @if(!empty($fd['address']) || $showAllFields)
                <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                    <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">{{ __('event_applications.wizard.labels.address') }}</p>
                    <p class="text-sm font-semibold text-blue-700">{{ $fd['address'] ?? '-' }}</p>
                </div>
            @endif

            {{-- Postal Code + Location (Localização) --}}
            @if(!empty($fd['postal_code']) || $application->district || $application->municipality || $showAllFields)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @if(!empty($fd['postal_code']) || $showAllFields)
                        <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                            <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">{{ __('event_applications.wizard.labels.postal_code') }}</p>
                            <p class="text-sm font-semibold text-blue-700">{{ $fd['postal_code'] ?? '-' }}</p>
                        </div>
                    @endif
                    @if($application->district || $application->municipality || $showAllFields)
                        <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                            <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">{{ __('event_applications.labels.location') }}</p>
                            <p class="text-sm font-semibold text-blue-700 flex items-center gap-2">
                                <x-heroicon-s-map-pin class="w-4 h-4" />
                                {{ collect([$application->district?->name, $application->municipality])->filter()->implode(', ') ?: '-' }}
                            </p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Local / Recinto --}}
            @if(!empty($fd['location']) || $showAllFields)
                <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                    <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">{{ __('event_applications.wizard.labels.location') }}</p>
                    <p class="text-sm font-semibold text-blue-700">{{ $fd['location'] ?? '-' }}</p>
                </div>
            @endif

            @if(isset($routeNamespace))
                @include('web.admin.event-applications.components.section-comments', [
                    'application' => $application,
                    'routeNamespace' => $routeNamespace,
                    'section' => 'event_location',
                ])
            @else
                @include('web.entity.event-applications.components.section-comments', [
                    'application' => $application,
                    'section' => 'event_location',
                ])
            @endif
        </div>
    </div>
@endif

{{-- Promoting Entity --}}
@if(!empty($fd['entity_name']) || !empty($fd['event_director_name']) || $showAllFields)
    <div class="card" x-data="{ open: false }">
        <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left pb-3" x-bind:class="open && 'border-b border-slate-200'">
            <div class="flex items-center gap-2">
                <x-heroicon-m-building-office-2 class="w-5 h-5 text-indigo-500" />
                <h2 class="text-lg font-semibold text-slate-800">{{ __('event_applications.wizard.sections.promoting_entity') }}</h2>
            </div>
            <x-heroicon-m-chevron-down class="w-5 h-5 text-gray-400 transition-transform" x-bind:class="open && 'rotate-180'" />
        </button>
        <div x-show="open" x-transition class="mt-4 space-y-4">
            @php
                $indigoFields = ['entity_name', 'entity_nipc'];
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach(['entity_name', 'national_federation_number', 'entity_address', 'entity_postal_code', 'entity_location', 'entity_nipc', 'entity_phone', 'entity_email'] as $field)
                    @if(!empty($fd[$field]) || $showAllFields)
                        @if(in_array($field, $indigoFields))
                            <div class="bg-indigo-50 rounded-lg p-3 border border-indigo-100">
                                <p class="text-xs text-indigo-600 uppercase tracking-wide mb-1">{{ __('event_applications.wizard.labels.' . $field) }}</p>
                                <p class="text-sm font-semibold text-indigo-700">{{ $fd[$field] ?? '-' }}</p>
                            </div>
                        @else
                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-100">
                                <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('event_applications.wizard.labels.' . $field) }}</p>
                                <p class="text-sm font-semibold text-slate-700">{{ $fd[$field] ?? '-' }}</p>
                            </div>
                        @endif
                    @endif
                @endforeach
            </div>

            @if(!empty($fd['event_director_name']) || !empty($fd['event_director_phone']) || !empty($fd['event_director_email']) || $showAllFields)
                <div class="pt-4 border-t border-slate-100">
                    <div class="flex items-center gap-2 mb-3">
                        <x-heroicon-m-user class="w-4 h-4 text-indigo-400" />
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.sections.event_director') }}</h3>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @foreach(['event_director_name', 'event_director_phone', 'event_director_email'] as $field)
                            @if(!empty($fd[$field]) || $showAllFields)
                                <div class="bg-indigo-50 rounded-lg p-3 border border-indigo-100">
                                    <p class="text-xs text-indigo-600 uppercase tracking-wide mb-1">{{ __('event_applications.wizard.labels.' . $field) }}</p>
                                    <p class="text-sm font-semibold text-indigo-700">{{ $fd[$field] ?? '-' }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            @if(isset($routeNamespace))
                @include('web.admin.event-applications.components.section-comments', [
                    'application' => $application,
                    'routeNamespace' => $routeNamespace,
                    'section' => 'promoting_entity',
                ])
            @else
                @include('web.entity.event-applications.components.section-comments', [
                    'application' => $application,
                    'section' => 'promoting_entity',
                ])
            @endif
        </div>
    </div>
@endif

{{-- Previous Editions --}}
@if(!empty($fd['previous_editions']) || !empty($fd['previous_actions']) || $showAllFields)
    <div class="card" x-data="{ open: false }">
        <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left pb-3" x-bind:class="open && 'border-b border-slate-200'">
            <div class="flex items-center gap-2">
                <x-heroicon-m-clock class="w-5 h-5 text-amber-500" />
                <h2 class="text-lg font-semibold text-slate-800">{{ __('event_applications.wizard.sections.previous_editions') }}</h2>
            </div>
            <x-heroicon-m-chevron-down class="w-5 h-5 text-gray-400 transition-transform" x-bind:class="open && 'rotate-180'" />
        </button>
        <div x-show="open" x-transition class="mt-4 space-y-4">
            @if(!empty($fd['previous_editions']))
                <div class="overflow-hidden rounded-lg border border-slate-200">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="text-left px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.year') }}</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.edition_location') }}</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.edition_name') }}</th>
                                <th class="text-right px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.participants_count') }}</th>
                                <th class="text-right px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.clubs_count') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($fd['previous_editions'] as $ed)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-4 py-2.5">{{ $ed['year'] ?? '-' }}</td>
                                    <td class="px-4 py-2.5">{{ $ed['location'] ?? '-' }}</td>
                                    <td class="px-4 py-2.5">{{ $ed['name'] ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-right">{{ $ed['athletes'] ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-right">{{ $ed['clubs'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif($showAllFields)
                <p class="text-sm text-slate-400 italic">{{ __('event_applications.wizard.no_entries_readonly') }}</p>
            @endif

            @if(!empty($fd['previous_actions']) || $showAllFields)
                <div class="pt-4 border-t border-slate-100">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-3">{{ __('event_applications.wizard.labels.previous_actions') }}</h3>
                    @if(!empty($fd['previous_actions']))
                        <div class="overflow-hidden rounded-lg border border-slate-200">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200">
                                        <th class="text-left px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.action') }}</th>
                                        <th class="text-left px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.agents') }}</th>
                                        <th class="text-right px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.participants') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($fd['previous_actions'] as $action)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-2.5">{{ $action['action'] ?? '-' }}</td>
                                            <td class="px-4 py-2.5">
                                                @if(!empty($action['agents']))
                                                    {{ collect($action['agents'])->map(fn($a) => __('event_applications.wizard.agent_options.' . $a))->implode(', ') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-2.5 text-right">{{ $action['participants'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-slate-400 italic">{{ __('event_applications.wizard.no_entries_readonly') }}</p>
                    @endif
                </div>
            @endif

            @if(isset($routeNamespace))
                @include('web.admin.event-applications.components.section-comments', [
                    'application' => $application,
                    'routeNamespace' => $routeNamespace,
                    'section' => 'previous_editions',
                ])
            @else
                @include('web.entity.event-applications.components.section-comments', [
                    'application' => $application,
                    'section' => 'previous_editions',
                ])
            @endif
        </div>
    </div>
@endif

{{-- Results Forecast --}}
@if(!empty($fd['forecast_total_participants']) || !empty($fd['event_objectives_description']) || !empty($fd['planned_actions']) || $showAllFields)
    <div class="card" x-data="{ open: false }">
        <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left pb-3" x-bind:class="open && 'border-b border-slate-200'">
            <div class="flex items-center gap-2">
                <x-heroicon-m-chart-bar class="w-5 h-5 text-emerald-500" />
                <h2 class="text-lg font-semibold text-slate-800">{{ __('event_applications.wizard.sections.results_forecast') }}</h2>
            </div>
            <x-heroicon-m-chevron-down class="w-5 h-5 text-gray-400 transition-transform" x-bind:class="open && 'rotate-180'" />
        </button>
        <div x-show="open" x-transition class="mt-4 space-y-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach(['forecast_total_participants', 'forecast_female_athletes', 'forecast_male_athletes', 'forecast_technical_officials', 'forecast_coaches', 'forecast_clubs'] as $field)
                    @if(!empty($fd[$field]) || $showAllFields)
                        <div class="bg-emerald-50 rounded-lg p-3 border border-emerald-100">
                            <p class="text-xs text-emerald-600 uppercase tracking-wide mb-1">{{ __('event_applications.wizard.labels.' . $field) }}</p>
                            <p class="text-2xl font-bold text-emerald-700 tabular-nums">{{ $fd[$field] ?? '-' }}</p>
                        </div>
                    @endif
                @endforeach
            </div>

            @foreach(['event_link_description', 'event_benefits_description', 'event_objectives_description', 'event_equipment_description'] as $field)
                @if(!empty($fd[$field]) || $showAllFields)
                    <div>
                        <p class="text-xs text-slate-500 uppercase tracking-wide mb-1.5">{{ __('event_applications.wizard.labels.' . $field) }}</p>
                        <div class="bg-slate-50 rounded-lg p-3 text-sm text-slate-700 whitespace-pre-line">{{ $fd[$field] ?? '-' }}</div>
                    </div>
                @endif
            @endforeach

            @if(!empty($fd['planned_actions']) || $showAllFields)
                <div class="pt-4 border-t border-slate-100">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-3">{{ __('event_applications.wizard.labels.planned_actions') }}</h3>
                    @if(!empty($fd['planned_actions']))
                        <div class="overflow-hidden rounded-lg border border-slate-200">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200">
                                        <th class="text-left px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.action') }}</th>
                                        <th class="text-left px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.agents') }}</th>
                                        <th class="text-right px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.participants') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($fd['planned_actions'] as $action)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-2.5">{{ $action['action'] ?? '-' }}</td>
                                            <td class="px-4 py-2.5">
                                                @if(!empty($action['agents']))
                                                    {{ collect($action['agents'])->map(fn($a) => __('event_applications.wizard.agent_options.' . $a))->implode(', ') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-2.5 text-right">{{ $action['participants'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-slate-400 italic">{{ __('event_applications.wizard.no_entries_readonly') }}</p>
                    @endif
                </div>
            @endif

            @if(isset($routeNamespace))
                @include('web.admin.event-applications.components.section-comments', [
                    'application' => $application,
                    'routeNamespace' => $routeNamespace,
                    'section' => 'results_forecast',
                ])
            @else
                @include('web.entity.event-applications.components.section-comments', [
                    'application' => $application,
                    'section' => 'results_forecast',
                ])
            @endif
        </div>
    </div>
@endif

{{-- Facilities & Logistics --}}
@php
    $facilityItems = collect($fd['facilities_checklist'] ?? [])->filter()->keys();
    $logisticsItems = collect($fd['logistics_checklist'] ?? [])->filter()->keys();
    $hasFacilities = $facilityItems->isNotEmpty() || !empty($fd['other_facilities']);
    $hasLogistics = $logisticsItems->isNotEmpty();
@endphp
@if($hasFacilities || $hasLogistics || $showAllFields)
    <div class="card" x-data="{ open: false }">
        <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left pb-3" x-bind:class="open && 'border-b border-slate-200'">
            <div class="flex items-center gap-2">
                <x-heroicon-m-wrench-screwdriver class="w-5 h-5 text-sky-500" />
                <h2 class="text-lg font-semibold text-slate-800">{{ __('event_applications.wizard.sections.logistics') }}</h2>
            </div>
            <x-heroicon-m-chevron-down class="w-5 h-5 text-gray-400 transition-transform" x-bind:class="open && 'rotate-180'" />
        </button>
        <div x-show="open" x-transition class="mt-4 space-y-4">
            @if($facilityItems->isNotEmpty() || $showAllFields)
                <div class="pt-4 border-t border-slate-100">
                    <div class="flex items-center gap-2 mb-3">
                        <x-heroicon-m-building-office class="w-4 h-4 text-sky-400" />
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.sections.facilities') }}</h3>
                    </div>
                    @if($facilityItems->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($facilityItems as $code)
                                <div class="flex items-center gap-2 p-2.5 bg-slate-50 rounded-lg">
                                    <x-heroicon-s-check-circle class="w-4 h-4 text-green-500 flex-shrink-0" />
                                    <span class="text-sm text-slate-700">{{ __('event_applications.wizard.checklist_items.' . $code) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-400 italic">{{ __('event_applications.wizard.no_entries_readonly') }}</p>
                    @endif
                </div>
            @endif

            @if(!empty($fd['other_facilities']) || $showAllFields)
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1.5">{{ __('event_applications.wizard.labels.other_facilities') }}</p>
                    <div class="bg-slate-50 rounded-lg p-3 text-sm text-slate-700 whitespace-pre-line">{{ $fd['other_facilities'] ?? '-' }}</div>
                </div>
            @endif

            @if($logisticsItems->isNotEmpty() || $showAllFields)
                @php
                    $logisticsGroups = [
                        'accommodations' => ['ATA1', 'ATA2', 'ATA3', 'ATA4'],
                        'transport' => ['TRA1', 'TRA2', 'TRA3', 'TRA4'],
                        'food' => ['ALI1', 'ALI2'],
                    ];
                    $logisticsIcons = [
                        'accommodations' => 'heroicon-m-home-modern',
                        'transport' => 'heroicon-m-truck',
                        'food' => 'heroicon-m-cake',
                    ];
                @endphp
                @foreach($logisticsGroups as $groupKey => $groupCodes)
                    @php
                        $activeInGroup = $logisticsItems->intersect($groupCodes);
                    @endphp
                    @if($activeInGroup->isNotEmpty())
                        <div class="pt-4 border-t border-slate-100">
                            <div class="flex items-center gap-2 mb-3">
                                <x-dynamic-component :component="$logisticsIcons[$groupKey]" class="w-4 h-4 text-sky-400" />
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.sections.' . $groupKey) }}</h3>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($activeInGroup as $code)
                                    <div class="flex items-center gap-2 p-2.5 bg-slate-50 rounded-lg">
                                        <x-heroicon-s-check-circle class="w-4 h-4 text-green-500 flex-shrink-0" />
                                        <span class="text-sm text-slate-700">{{ __('event_applications.wizard.checklist_items.' . $code) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif

            @if(isset($routeNamespace))
                @include('web.admin.event-applications.components.section-comments', [
                    'application' => $application,
                    'routeNamespace' => $routeNamespace,
                    'section' => 'logistics',
                ])
            @else
                @include('web.entity.event-applications.components.section-comments', [
                    'application' => $application,
                    'section' => 'logistics',
                ])
            @endif
        </div>
    </div>
@endif

{{-- Safety & Emergency Plan --}}
@php
    $safetyItems = collect($fd['safety_checklist'] ?? [])->filter()->keys();
    $hasSafety = $safetyItems->isNotEmpty() || !empty($fd['pse_responsible_name']) || !empty($fd['insurances']);
@endphp
@if($hasSafety || $showAllFields)
    <div class="card" x-data="{ open: false }">
        <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left pb-3" x-bind:class="open && 'border-b border-slate-200'">
            <div class="flex items-center gap-2">
                <x-heroicon-m-shield-check class="w-5 h-5 text-red-500" />
                <h2 class="text-lg font-semibold text-slate-800">{{ __('event_applications.wizard.sections.safety_plan') }}</h2>
            </div>
            <x-heroicon-m-chevron-down class="w-5 h-5 text-gray-400 transition-transform" x-bind:class="open && 'rotate-180'" />
        </button>
        <div x-show="open" x-transition class="mt-4 space-y-4">
            @if($safetyItems->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($safetyItems as $code)
                        <div class="flex items-center gap-2 p-2.5 bg-slate-50 rounded-lg">
                            <x-heroicon-s-check-circle class="w-4 h-4 text-green-500 flex-shrink-0" />
                            <span class="text-sm text-slate-700">{{ __('event_applications.wizard.checklist_items.' . $code) }}</span>
                        </div>
                    @endforeach
                </div>
            @elseif($showAllFields)
                <p class="text-sm text-slate-400 italic">{{ __('event_applications.wizard.no_entries_readonly') }}</p>
            @endif

            @if(!empty($fd['pse_responsible_name']) || !empty($fd['pse_responsible_phone']) || !empty($fd['pse_responsible_email']) || $showAllFields)
                <div class="pt-4 border-t border-slate-100">
                    <div class="flex items-center gap-2 mb-3">
                        <x-heroicon-m-user-group class="w-4 h-4 text-red-400" />
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.emergency_team') }}</h3>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @foreach(['pse_responsible_name', 'pse_responsible_phone', 'pse_responsible_email'] as $field)
                            @if(!empty($fd[$field]) || $showAllFields)
                                <div class="bg-red-50 rounded-lg p-3 border border-red-100">
                                    <p class="text-xs text-red-600 uppercase tracking-wide mb-1">{{ __('event_applications.wizard.labels.' . $field) }}</p>
                                    <p class="text-sm font-semibold text-red-700">{{ $fd[$field] ?? '-' }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!empty($fd['insurances']) || $showAllFields)
                <div class="pt-4 border-t border-slate-100">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-3">{{ __('event_applications.wizard.labels.insurances') }}</h3>
                    @if(!empty($fd['insurances']))
                        <div class="overflow-hidden rounded-lg border border-slate-200">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200">
                                        <th class="text-left px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.insurance_type') }}</th>
                                        <th class="text-left px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.insurer') }}</th>
                                        <th class="text-left px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.policy_number') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($fd['insurances'] as $ins)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-2.5">{{ $ins['type'] ?? '-' }}</td>
                                            <td class="px-4 py-2.5">{{ $ins['insurer'] ?? '-' }}</td>
                                            <td class="px-4 py-2.5">{{ $ins['policy_number'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-slate-400 italic">{{ __('event_applications.wizard.no_entries_readonly') }}</p>
                    @endif
                </div>
            @endif

            @if(isset($routeNamespace))
                @include('web.admin.event-applications.components.section-comments', [
                    'application' => $application,
                    'routeNamespace' => $routeNamespace,
                    'section' => 'safety',
                ])
            @else
                @include('web.entity.event-applications.components.section-comments', [
                    'application' => $application,
                    'section' => 'safety',
                ])
            @endif
        </div>
    </div>
@endif

{{-- Partners & Promotion --}}
@php
    $promotionItems = collect($fd['promotion_checklist'] ?? [])->filter()->keys();
    $hasPartners = !empty($fd['partners']) || $promotionItems->isNotEmpty() || !empty($fd['financing_description']) || !empty($fd['technical_documents_description']);
@endphp
@if($hasPartners || $showAllFields)
    <div class="card" x-data="{ open: false }">
        <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left pb-3" x-bind:class="open && 'border-b border-slate-200'">
            <div class="flex items-center gap-2">
                <x-heroicon-m-megaphone class="w-5 h-5 text-purple-500" />
                <h2 class="text-lg font-semibold text-slate-800">{{ __('event_applications.wizard.sections.partners_norms') }}</h2>
            </div>
            <x-heroicon-m-chevron-down class="w-5 h-5 text-gray-400 transition-transform" x-bind:class="open && 'rotate-180'" />
        </button>
        <div x-show="open" x-transition class="mt-4 space-y-4">
            @if(!empty($fd['partners']) || $showAllFields)
                <div class="pt-4 border-t border-slate-100">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-3">{{ __('event_applications.wizard.labels.partners') }}</h3>
                    @if(!empty($fd['partners']))
                        <div class="overflow-hidden rounded-lg border border-slate-200">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200">
                                        <th class="text-left px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.partner_name') }}</th>
                                        <th class="text-left px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.partnership_type') }}</th>
                                        <th class="text-left px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.partner_email') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($fd['partners'] as $p)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-2.5">{{ $p['name'] ?? '-' }}</td>
                                            <td class="px-4 py-2.5">{{ $p['partnership_type'] ?? '-' }}</td>
                                            <td class="px-4 py-2.5">{{ $p['email'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-slate-400 italic">{{ __('event_applications.wizard.no_entries_readonly') }}</p>
                    @endif
                </div>
            @endif

            @if(!empty($fd['financing_description']) || $showAllFields)
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1.5">{{ __('event_applications.wizard.labels.financing_description') }}</p>
                    <div class="bg-slate-50 rounded-lg p-3 text-sm text-slate-700 whitespace-pre-line">{{ $fd['financing_description'] ?? '-' }}</div>
                </div>
            @endif

            @if($promotionItems->isNotEmpty() || $showAllFields)
                <div class="pt-4 border-t border-slate-100">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-3">{{ __('event_applications.wizard.sections.technical_docs') }}</h3>
                    @if($promotionItems->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($promotionItems as $code)
                                <div class="flex items-center gap-2 p-2.5 bg-slate-50 rounded-lg">
                                    <x-heroicon-s-check-circle class="w-4 h-4 text-green-500 flex-shrink-0" />
                                    <span class="text-sm text-slate-700">{{ __('event_applications.wizard.checklist_items.' . $code) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-400 italic">{{ __('event_applications.wizard.no_entries_readonly') }}</p>
                    @endif
                </div>
            @endif

            @if(!empty($fd['technical_documents_description']) || $showAllFields)
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1.5">{{ __('event_applications.wizard.labels.technical_documents_description') }}</p>
                    <div class="bg-slate-50 rounded-lg p-3 text-sm text-slate-700 whitespace-pre-line">{{ $fd['technical_documents_description'] ?? '-' }}</div>
                </div>
            @endif

            @if(isset($routeNamespace))
                @include('web.admin.event-applications.components.section-comments', [
                    'application' => $application,
                    'routeNamespace' => $routeNamespace,
                    'section' => 'partners',
                ])
            @else
                @include('web.entity.event-applications.components.section-comments', [
                    'application' => $application,
                    'section' => 'partners',
                ])
            @endif
        </div>
    </div>
@endif

{{-- Budget Summary --}}
@if(!empty($fd['expenses']) || !empty($fd['revenue']) || $showAllFields)
    <div class="card" x-data="{ open: false }">
        <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left pb-3" x-bind:class="open && 'border-b border-slate-200'">
            <div class="flex items-center gap-2">
                <x-heroicon-m-banknotes class="w-5 h-5 text-slate-500" />
                <h2 class="text-lg font-semibold text-slate-800">{{ __('event_applications.wizard.sections.budget') }}</h2>
            </div>
            <x-heroicon-m-chevron-down class="w-5 h-5 text-gray-400 transition-transform" x-bind:class="open && 'rotate-180'" />
        </button>
        <div x-show="open" x-transition class="mt-4 space-y-4">
            @php
                $totalExpenses = 0;
                $totalRevenue = 0;
                if (!empty($fd['expenses'])) {
                    foreach ($fd['expenses'] as $group) {
                        foreach ($group as $item) {
                            $totalExpenses += ((float) ($item['qty'] ?? 0) * (float) ($item['value'] ?? 0));
                        }
                    }
                }
                if (!empty($fd['revenue'])) {
                    foreach ($fd['revenue'] as $key => $group) {
                        if ($key === 'partners') {
                            foreach ($group as $p) {
                                $totalRevenue += ((float) ($p['qty'] ?? 0) * (float) ($p['value'] ?? 0));
                            }
                        } else {
                            foreach ($group as $item) {
                                $totalRevenue += ((float) ($item['qty'] ?? 0) * (float) ($item['value'] ?? 0));
                            }
                        }
                    }
                }
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-4 bg-rose-50 rounded-lg border border-rose-200">
                    <p class="text-xs font-medium text-rose-600">{{ __('event_applications.wizard.sections.expenses') }}</p>
                    <p class="text-xl font-bold text-rose-700 tabular-nums">{{ number_format($totalExpenses, 2) }} EUR</p>
                </div>
                <div class="p-4 bg-emerald-50 rounded-lg border border-emerald-200">
                    <p class="text-xs font-medium text-emerald-600">{{ __('event_applications.wizard.sections.revenue') }}</p>
                    <p class="text-xl font-bold text-emerald-700 tabular-nums">{{ number_format($totalRevenue, 2) }} EUR</p>
                </div>
                <div class="p-4 {{ ($totalRevenue - $totalExpenses) >= 0 ? 'bg-blue-50 border-blue-200' : 'bg-amber-50 border-amber-200' }} rounded-lg border">
                    <p class="text-xs font-medium {{ ($totalRevenue - $totalExpenses) >= 0 ? 'text-blue-600' : 'text-amber-600' }}">{{ __('event_applications.wizard.labels.balance') }}</p>
                    <p class="text-xl font-bold {{ ($totalRevenue - $totalExpenses) >= 0 ? 'text-blue-700' : 'text-amber-700' }} tabular-nums">{{ number_format($totalRevenue - $totalExpenses, 2) }} EUR</p>
                </div>
            </div>

            {{-- Expense Details --}}
            @if(!empty($fd['expenses']) || $showAllFields)
                @php
                    $expenseGroups = [
                        'infrastructure' => [
                            'title' => 'event_applications.wizard.expense_groups.infrastructure',
                            'items' => ['installations', 'licenses', 'audiovisual', 'other'],
                        ],
                        'human_resources' => [
                            'title' => 'event_applications.wizard.expense_groups.human_resources',
                            'items' => ['technical_delegate', 'technical_officials', 'chief_technical_officials', 'event_director', 'safety_emergency_manager', 'specialized_technicians', 'other'],
                        ],
                        'travel' => [
                            'title' => 'event_applications.wizard.expense_groups.travel',
                            'items' => ['fuel', 'tolls', 'other'],
                        ],
                        'prizes' => [
                            'title' => 'event_applications.wizard.expense_groups.prizes',
                            'items' => ['medals', 'trophies', 'diplomas', 'other'],
                        ],
                        'accommodation_food' => [
                            'title' => 'event_applications.wizard.expense_groups.accommodation_food',
                            'items' => ['food', 'accommodation'],
                        ],
                        'other_expenses' => [
                            'title' => 'event_applications.wizard.expense_groups.other_expenses',
                            'items' => ['consumables', 'merchandise', 'streaming', 'promotion_plan'],
                        ],
                    ];
                @endphp

                <div class="pt-5 mt-2 border-t-2 border-slate-200">
                    <div class="flex items-center gap-2 mb-4">
                        <x-heroicon-m-arrow-trending-down class="w-5 h-5 text-rose-500" />
                        <h3 class="text-base font-bold uppercase tracking-wide text-slate-700">{{ __('event_applications.wizard.sections.expenses') }}</h3>
                    </div>

                    @foreach($expenseGroups as $groupKey => $group)
                        @php
                            $groupData = $fd['expenses'][$groupKey] ?? [];
                            $groupTotal = 0;
                            $hasRows = false;
                            foreach ($groupData as $item) {
                                $qty = (float) ($item['qty'] ?? 0);
                                $val = (float) ($item['value'] ?? 0);
                                if ($qty > 0 || $val > 0) {
                                    $hasRows = true;
                                }
                                $groupTotal += $qty * $val;
                            }
                        @endphp
                        @if($hasRows || $showAllFields)
                            <div class="mb-4">
                                <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">{{ __($group['title']) }}</h4>
                                <div class="overflow-hidden rounded-lg border border-slate-200">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="bg-slate-50 border-b border-slate-200">
                                                <th class="text-left px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.item') }}</th>
                                                <th class="text-right px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.quantity') }}</th>
                                                <th class="text-right px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.unit_value') }}</th>
                                                <th class="text-right px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.subtotal') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @foreach($groupData as $itemKey => $item)
                                                @php
                                                    $qty = (float) ($item['qty'] ?? 0);
                                                    $val = (float) ($item['value'] ?? 0);
                                                    $subtotal = $qty * $val;
                                                @endphp
                                                @if($qty > 0 || $val > 0 || $showAllFields)
                                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                                        <td class="px-4 py-2.5">{{ __('event_applications.wizard.expense_items.' . $itemKey) }}</td>
                                                        <td class="px-4 py-2.5 text-right tabular-nums">{{ $qty ?: '-' }}</td>
                                                        <td class="px-4 py-2.5 text-right tabular-nums">{{ $val ? number_format($val, 2) : '-' }}</td>
                                                        <td class="px-4 py-2.5 text-right tabular-nums">{{ $subtotal ? number_format($subtotal, 2) : '-' }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="border-t-2 border-slate-300 bg-slate-50">
                                                <td colspan="3" class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600">{{ __('event_applications.wizard.labels.group_total') }}</td>
                                                <td class="px-4 py-2.5 text-right font-semibold text-slate-700 tabular-nums">{{ $groupTotal ? number_format($groupTotal, 2) : '-' }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            {{-- Revenue Details --}}
            @if(!empty($fd['revenue']) || $showAllFields)
                <div class="pt-5 mt-2 border-t-2 border-slate-200">
                    <div class="flex items-center gap-2 mb-4">
                        <x-heroicon-m-arrow-trending-up class="w-5 h-5 text-emerald-500" />
                        <h3 class="text-base font-bold uppercase tracking-wide text-slate-700">{{ __('event_applications.wizard.sections.revenue') }}</h3>
                    </div>

                    {{-- Partners --}}
                    @if(!empty($fd['revenue']['partners']))
                        @php
                            $partnersHasRows = false;
                            $partnersTotal = 0;
                            foreach ($fd['revenue']['partners'] as $p) {
                                $qty = (float) ($p['qty'] ?? 0);
                                $val = (float) ($p['value'] ?? 0);
                                if ($qty > 0 || $val > 0) {
                                    $partnersHasRows = true;
                                }
                                $partnersTotal += $qty * $val;
                            }
                        @endphp
                        @if($partnersHasRows || $showAllFields)
                            <div class="mb-4">
                                <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">{{ __('event_applications.wizard.revenue_groups.partners') }}</h4>
                                <div class="overflow-hidden rounded-lg border border-slate-200">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="bg-slate-50 border-b border-slate-200">
                                                <th class="text-left px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.partner_entity') }}</th>
                                                <th class="text-right px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.quantity') }}</th>
                                                <th class="text-right px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.unit_value') }}</th>
                                                <th class="text-right px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.subtotal') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @foreach($fd['revenue']['partners'] as $p)
                                                @php
                                                    $qty = (float) ($p['qty'] ?? 0);
                                                    $val = (float) ($p['value'] ?? 0);
                                                    $subtotal = $qty * $val;
                                                @endphp
                                                @if($qty > 0 || $val > 0 || $showAllFields)
                                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                                        <td class="px-4 py-2.5">{{ $p['entity'] ?? '-' }}</td>
                                                        <td class="px-4 py-2.5 text-right tabular-nums">{{ $qty ?: '-' }}</td>
                                                        <td class="px-4 py-2.5 text-right tabular-nums">{{ $val ? number_format($val, 2) : '-' }}</td>
                                                        <td class="px-4 py-2.5 text-right tabular-nums">{{ $subtotal ? number_format($subtotal, 2) : '-' }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="border-t-2 border-slate-300 bg-slate-50">
                                                <td colspan="3" class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600">{{ __('event_applications.wizard.labels.group_total') }}</td>
                                                <td class="px-4 py-2.5 text-right font-semibold text-slate-700 tabular-nums">{{ $partnersTotal ? number_format($partnersTotal, 2) : '-' }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- Revenue groups --}}
                    @php
                        $revenueGroups = [
                            'registrations' => [
                                'title' => 'event_applications.wizard.revenue_groups.registrations',
                                'items' => ['clubs', 'participants'],
                            ],
                            'sales' => [
                                'title' => 'event_applications.wizard.revenue_groups.sales',
                                'items' => ['equipment', 'merch', 'stand_rental', 'other'],
                            ],
                            'other_revenue' => [
                                'title' => 'event_applications.wizard.revenue_groups.other_revenue',
                                'items' => ['meals', 'accommodation', 'equipment_rental', 'other'],
                            ],
                        ];
                    @endphp

                    @foreach($revenueGroups as $groupKey => $group)
                        @php
                            $groupData = $fd['revenue'][$groupKey] ?? [];
                            $groupTotal = 0;
                            $hasRows = false;
                            foreach ($groupData as $item) {
                                $qty = (float) ($item['qty'] ?? 0);
                                $val = (float) ($item['value'] ?? 0);
                                if ($qty > 0 || $val > 0) {
                                    $hasRows = true;
                                }
                                $groupTotal += $qty * $val;
                            }
                        @endphp
                        @if($hasRows || $showAllFields)
                            <div class="mb-4">
                                <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">{{ __($group['title']) }}</h4>
                                <div class="overflow-hidden rounded-lg border border-slate-200">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="bg-slate-50 border-b border-slate-200">
                                                <th class="text-left px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.item') }}</th>
                                                <th class="text-right px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.quantity') }}</th>
                                                <th class="text-right px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.unit_value') }}</th>
                                                <th class="text-right px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.subtotal') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @foreach($groupData as $itemKey => $item)
                                                @php
                                                    $qty = (float) ($item['qty'] ?? 0);
                                                    $val = (float) ($item['value'] ?? 0);
                                                    $subtotal = $qty * $val;
                                                @endphp
                                                @if($qty > 0 || $val > 0 || $showAllFields)
                                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                                        <td class="px-4 py-2.5">{{ __('event_applications.wizard.revenue_items.' . $itemKey) }}</td>
                                                        <td class="px-4 py-2.5 text-right tabular-nums">{{ $qty ?: '-' }}</td>
                                                        <td class="px-4 py-2.5 text-right tabular-nums">{{ $val ? number_format($val, 2) : '-' }}</td>
                                                        <td class="px-4 py-2.5 text-right tabular-nums">{{ $subtotal ? number_format($subtotal, 2) : '-' }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="border-t-2 border-slate-300 bg-slate-50">
                                                <td colspan="3" class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600">{{ __('event_applications.wizard.labels.group_total') }}</td>
                                                <td class="px-4 py-2.5 text-right font-semibold text-slate-700 tabular-nums">{{ $groupTotal ? number_format($groupTotal, 2) : '-' }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            @if(isset($routeNamespace))
                @include('web.admin.event-applications.components.section-comments', [
                    'application' => $application,
                    'routeNamespace' => $routeNamespace,
                    'section' => 'budget',
                ])
            @else
                @include('web.entity.event-applications.components.section-comments', [
                    'application' => $application,
                    'section' => 'budget',
                ])
            @endif
        </div>
    </div>
@endif
