{{-- Step 10: Summary --}}
<div class="space-y-6">
    {{-- Info Card --}}
    <div class="rounded-lg border border-green-200 bg-green-50 p-4">
        <div class="flex">
            <div class="shrink-0">
                <x-heroicon-o-check-circle class="h-5 w-5 text-green-500" />
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700">
                    {{ __('event_applications.wizard.summary_info') }}
                </p>
            </div>
        </div>
    </div>

    @if ($application)
        <div class="flex justify-end">
            <a href="{{ route('entity.event-applications.pdf', $application) }}" target="_blank" class="btn btn-secondary">
                <x-heroicon-o-arrow-down-tray class="w-4 h-4 mr-1" />
                {{ __('event_applications.actions.download_pdf') }}
            </a>
        </div>
    @endif

    @php
        $fd = $formData;
    @endphp

    {{-- Event Location --}}
    @if ($event_name || $sport_id || $start_date || $district_id || $municipality)
        <div class="card" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left pb-3" x-bind:class="open && 'border-b border-slate-200'">
                <div class="flex items-center gap-2">
                    <x-heroicon-m-map-pin class="w-5 h-5 text-blue-500" />
                    <h2 class="text-lg font-semibold text-slate-800">{{ __('event_applications.sections.event_location') }}</h2>
                </div>
                <x-heroicon-m-chevron-down class="w-5 h-5 text-gray-400 transition-transform" x-bind:class="open && 'rotate-180'" />
            </button>
            <div x-show="open" x-transition class="mt-4 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @if ($event_name)
                        <div class="bg-blue-50 rounded-lg p-3 border border-blue-100 col-span-2">
                            <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">{{ __('event_applications.labels.event_name') }}</p>
                            <p class="text-sm font-semibold text-blue-700">{{ $event_name }}</p>
                        </div>
                    @endif

                    @if ($sport_id && isset($sports[$sport_id]))
                        <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                            <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">{{ __('event_applications.labels.sport') }}</p>
                            <p class="text-sm font-semibold text-blue-700 flex items-center gap-2">
                                <x-heroicon-s-bolt class="w-4 h-4" />
                                {{ $sports[$sport_id] }}
                            </p>
                        </div>
                    @endif

                    @if ($start_date)
                        <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                            <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">{{ __('event_applications.labels.start_date') }}</p>
                            <p class="text-sm font-semibold text-blue-700">{{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }}</p>
                        </div>
                    @endif

                    @if ($end_date)
                        <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                            <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">{{ __('event_applications.labels.end_date') }}</p>
                            <p class="text-sm font-semibold text-blue-700">{{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}</p>
                        </div>
                    @endif
                </div>

                @if (!empty($fd['address']))
                    <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                        <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">{{ __('event_applications.wizard.labels.address') }}</p>
                        <p class="text-sm font-semibold text-blue-700">{{ $fd['address'] }}</p>
                    </div>
                @endif

                @if (!empty($fd['postal_code']) || $district_id || $municipality)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @if (!empty($fd['postal_code']))
                            <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                                <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">{{ __('event_applications.wizard.labels.postal_code') }}</p>
                                <p class="text-sm font-semibold text-blue-700">{{ $fd['postal_code'] }}</p>
                            </div>
                        @endif
                        @if ($district_id || $municipality)
                            <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                                <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">{{ __('event_applications.labels.location') }}</p>
                                <p class="text-sm font-semibold text-blue-700 flex items-center gap-2">
                                    <x-heroicon-s-map-pin class="w-4 h-4" />
                                    {{ collect([isset($districts[$district_id]) ? $districts[$district_id] : null, $municipality])->filter()->implode(', ') }}
                                </p>
                            </div>
                        @endif
                    </div>
                @endif

                @if (!empty($fd['location']))
                    <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                        <p class="text-xs text-blue-600 uppercase tracking-wide mb-1">{{ __('event_applications.wizard.labels.location') }}</p>
                        <p class="text-sm font-semibold text-blue-700">{{ $fd['location'] }}</p>
                    </div>
                @endif

                @if ($target_audience || $expected_participants)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @if ($target_audience)
                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-100">
                                <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.target_audience') }}</p>
                                <p class="text-sm font-semibold text-slate-700">{{ $target_audience }}</p>
                            </div>
                        @endif
                        @if ($expected_participants)
                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-100">
                                <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('event_applications.labels.expected_participants') }}</p>
                                <p class="text-sm font-semibold text-slate-700">{{ $expected_participants }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Promoting Entity --}}
    @if (!empty($fd['entity_name']) || !empty($fd['event_director_name']))
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
                    @foreach (['entity_name', 'national_federation_number', 'entity_address', 'entity_postal_code', 'entity_location', 'entity_nipc', 'entity_phone', 'entity_email'] as $field)
                        @if (!empty($fd[$field]))
                            @if (in_array($field, $indigoFields))
                                <div class="bg-indigo-50 rounded-lg p-3 border border-indigo-100">
                                    <p class="text-xs text-indigo-600 uppercase tracking-wide mb-1">{{ __('event_applications.wizard.labels.' . $field) }}</p>
                                    <p class="text-sm font-semibold text-indigo-700">{{ $fd[$field] }}</p>
                                </div>
                            @else
                                <div class="bg-slate-50 rounded-lg p-3 border border-slate-100">
                                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('event_applications.wizard.labels.' . $field) }}</p>
                                    <p class="text-sm font-semibold text-slate-700">{{ $fd[$field] }}</p>
                                </div>
                            @endif
                        @endif
                    @endforeach
                </div>

                @if (!empty($fd['event_director_name']) || !empty($fd['event_director_phone']) || !empty($fd['event_director_email']))
                    <div class="pt-4 border-t border-slate-100">
                        <div class="flex items-center gap-2 mb-3">
                            <x-heroicon-m-user class="w-4 h-4 text-indigo-400" />
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.sections.event_director') }}</h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @foreach (['event_director_name', 'event_director_phone', 'event_director_email'] as $field)
                                @if (!empty($fd[$field]))
                                    <div class="bg-indigo-50 rounded-lg p-3 border border-indigo-100">
                                        <p class="text-xs text-indigo-600 uppercase tracking-wide mb-1">{{ __('event_applications.wizard.labels.' . $field) }}</p>
                                        <p class="text-sm font-semibold text-indigo-700">{{ $fd[$field] }}</p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Previous Editions --}}
    @if (!empty($fd['previous_editions']) || !empty($fd['previous_actions']))
        <div class="card" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left pb-3" x-bind:class="open && 'border-b border-slate-200'">
                <div class="flex items-center gap-2">
                    <x-heroicon-m-clock class="w-5 h-5 text-amber-500" />
                    <h2 class="text-lg font-semibold text-slate-800">{{ __('event_applications.wizard.sections.previous_editions') }}</h2>
                </div>
                <x-heroicon-m-chevron-down class="w-5 h-5 text-gray-400 transition-transform" x-bind:class="open && 'rotate-180'" />
            </button>
            <div x-show="open" x-transition class="mt-4 space-y-4">
                @if (!empty($fd['previous_editions']))
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
                                @foreach ($fd['previous_editions'] as $ed)
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
                @endif

                @if (!empty($fd['previous_actions']))
                    <div class="pt-4 border-t border-slate-100">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-3">{{ __('event_applications.wizard.labels.previous_actions') }}</h3>
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
                                    @foreach ($fd['previous_actions'] as $action)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-2.5">{{ $action['action'] ?? '-' }}</td>
                                            <td class="px-4 py-2.5">
                                                @if (!empty($action['agents']))
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
                    </div>
                @endif

                @if (!empty($fd['other_support']))
                    <div>
                        <p class="text-xs text-slate-500 uppercase tracking-wide mb-1.5">{{ __('event_applications.wizard.labels.other_support') }}</p>
                        <div class="bg-slate-50 rounded-lg p-3 text-sm text-slate-700 whitespace-pre-line">{{ $fd['other_support'] }}</div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Results Forecast --}}
    @if (!empty($fd['forecast_total_participants']) || !empty($fd['event_objectives_description']) || !empty($fd['planned_actions']))
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
                    @foreach (['forecast_total_participants', 'forecast_female_athletes', 'forecast_male_athletes', 'forecast_technical_officials', 'forecast_coaches', 'forecast_clubs'] as $field)
                        @if (!empty($fd[$field]))
                            <div class="bg-emerald-50 rounded-lg p-3 border border-emerald-100">
                                <p class="text-xs text-emerald-600 uppercase tracking-wide mb-1">{{ __('event_applications.wizard.labels.' . $field) }}</p>
                                <p class="text-2xl font-bold text-emerald-700 tabular-nums">{{ $fd[$field] }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>

                @foreach (['event_link_description', 'event_benefits_description', 'event_objectives_description', 'event_equipment_description'] as $field)
                    @if (!empty($fd[$field]))
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1.5">{{ __('event_applications.wizard.labels.' . $field) }}</p>
                            <div class="bg-slate-50 rounded-lg p-3 text-sm text-slate-700 whitespace-pre-line">{{ $fd[$field] }}</div>
                        </div>
                    @endif
                @endforeach

                @if (!empty($fd['planned_actions']))
                    <div class="pt-4 border-t border-slate-100">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-3">{{ __('event_applications.wizard.labels.planned_actions') }}</h3>
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
                                    @foreach ($fd['planned_actions'] as $action)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-2.5">{{ $action['action'] ?? '-' }}</td>
                                            <td class="px-4 py-2.5">
                                                @if (!empty($action['agents']))
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
                    </div>
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
    @if ($hasFacilities || $hasLogistics)
        <div class="card" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left pb-3" x-bind:class="open && 'border-b border-slate-200'">
                <div class="flex items-center gap-2">
                    <x-heroicon-m-wrench-screwdriver class="w-5 h-5 text-sky-500" />
                    <h2 class="text-lg font-semibold text-slate-800">{{ __('event_applications.wizard.sections.logistics') }}</h2>
                </div>
                <x-heroicon-m-chevron-down class="w-5 h-5 text-gray-400 transition-transform" x-bind:class="open && 'rotate-180'" />
            </button>
            <div x-show="open" x-transition class="mt-4 space-y-4">
                @if ($facilityItems->isNotEmpty())
                    <div class="pt-4 border-t border-slate-100">
                        <div class="flex items-center gap-2 mb-3">
                            <x-heroicon-m-building-office class="w-4 h-4 text-sky-400" />
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.sections.facilities') }}</h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach ($facilityItems as $code)
                                <div class="flex items-center gap-2 p-2.5 bg-slate-50 rounded-lg">
                                    <x-heroicon-s-check-circle class="w-4 h-4 text-green-500 flex-shrink-0" />
                                    <span class="text-sm text-slate-700">{{ __('event_applications.wizard.checklist_items.' . $code) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (!empty($fd['other_facilities']))
                    <div>
                        <p class="text-xs text-slate-500 uppercase tracking-wide mb-1.5">{{ __('event_applications.wizard.labels.other_facilities') }}</p>
                        <div class="bg-slate-50 rounded-lg p-3 text-sm text-slate-700 whitespace-pre-line">{{ $fd['other_facilities'] }}</div>
                    </div>
                @endif

                @if ($logisticsItems->isNotEmpty())
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
                    @foreach ($logisticsGroups as $groupKey => $groupCodes)
                        @php
                            $activeInGroup = $logisticsItems->intersect($groupCodes);
                        @endphp
                        @if ($activeInGroup->isNotEmpty())
                            <div class="pt-4 border-t border-slate-100">
                                <div class="flex items-center gap-2 mb-3">
                                    <x-dynamic-component :component="$logisticsIcons[$groupKey]" class="w-4 h-4 text-sky-400" />
                                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.sections.' . $groupKey) }}</h3>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach ($activeInGroup as $code)
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
            </div>
        </div>
    @endif

    {{-- Safety & Emergency Plan --}}
    @php
        $safetyItems = collect($fd['safety_checklist'] ?? [])->filter()->keys();
        $hasSafety = $safetyItems->isNotEmpty() || !empty($fd['pse_responsible_name']) || !empty($fd['insurances']);
    @endphp
    @if ($hasSafety)
        <div class="card" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left pb-3" x-bind:class="open && 'border-b border-slate-200'">
                <div class="flex items-center gap-2">
                    <x-heroicon-m-shield-check class="w-5 h-5 text-red-500" />
                    <h2 class="text-lg font-semibold text-slate-800">{{ __('event_applications.wizard.sections.safety_plan') }}</h2>
                </div>
                <x-heroicon-m-chevron-down class="w-5 h-5 text-gray-400 transition-transform" x-bind:class="open && 'rotate-180'" />
            </button>
            <div x-show="open" x-transition class="mt-4 space-y-4">
                @if ($safetyItems->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach ($safetyItems as $code)
                            <div class="flex items-center gap-2 p-2.5 bg-slate-50 rounded-lg">
                                <x-heroicon-s-check-circle class="w-4 h-4 text-green-500 flex-shrink-0" />
                                <span class="text-sm text-slate-700">{{ __('event_applications.wizard.checklist_items.' . $code) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if (!empty($fd['pse_responsible_name']) || !empty($fd['pse_responsible_phone']) || !empty($fd['pse_responsible_email']))
                    <div class="pt-4 border-t border-slate-100">
                        <div class="flex items-center gap-2 mb-3">
                            <x-heroicon-m-user-group class="w-4 h-4 text-red-400" />
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('event_applications.wizard.labels.emergency_team') }}</h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @foreach (['pse_responsible_name', 'pse_responsible_phone', 'pse_responsible_email'] as $field)
                                @if (!empty($fd[$field]))
                                    <div class="bg-red-50 rounded-lg p-3 border border-red-100">
                                        <p class="text-xs text-red-600 uppercase tracking-wide mb-1">{{ __('event_applications.wizard.labels.' . $field) }}</p>
                                        <p class="text-sm font-semibold text-red-700">{{ $fd[$field] }}</p>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (!empty($fd['insurances']))
                    <div class="pt-4 border-t border-slate-100">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-3">{{ __('event_applications.wizard.labels.insurances') }}</h3>
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
                                    @foreach ($fd['insurances'] as $ins)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-2.5">{{ $ins['type'] ?? '-' }}</td>
                                            <td class="px-4 py-2.5">{{ $ins['insurer'] ?? '-' }}</td>
                                            <td class="px-4 py-2.5">{{ $ins['policy_number'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Partners & Promotion --}}
    @php
        $promotionItems = collect($fd['promotion_checklist'] ?? [])->filter()->keys();
        $hasPartners = !empty($fd['partners']) || $promotionItems->isNotEmpty() || !empty($fd['financing_description']) || !empty($fd['technical_documents_description']);
    @endphp
    @if ($hasPartners)
        <div class="card" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left pb-3" x-bind:class="open && 'border-b border-slate-200'">
                <div class="flex items-center gap-2">
                    <x-heroicon-m-megaphone class="w-5 h-5 text-purple-500" />
                    <h2 class="text-lg font-semibold text-slate-800">{{ __('event_applications.wizard.sections.partners_norms') }}</h2>
                </div>
                <x-heroicon-m-chevron-down class="w-5 h-5 text-gray-400 transition-transform" x-bind:class="open && 'rotate-180'" />
            </button>
            <div x-show="open" x-transition class="mt-4 space-y-4">
                @if (!empty($fd['partners']))
                    <div class="pt-4 border-t border-slate-100">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-3">{{ __('event_applications.wizard.labels.partners') }}</h3>
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
                                    @foreach ($fd['partners'] as $p)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-2.5">{{ $p['name'] ?? '-' }}</td>
                                            <td class="px-4 py-2.5">{{ $p['partnership_type'] ?? '-' }}</td>
                                            <td class="px-4 py-2.5">{{ $p['email'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if (!empty($fd['financing_description']))
                    <div>
                        <p class="text-xs text-slate-500 uppercase tracking-wide mb-1.5">{{ __('event_applications.wizard.labels.financing_description') }}</p>
                        <div class="bg-slate-50 rounded-lg p-3 text-sm text-slate-700 whitespace-pre-line">{{ $fd['financing_description'] }}</div>
                    </div>
                @endif

                @if ($promotionItems->isNotEmpty())
                    <div class="pt-4 border-t border-slate-100">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-3">{{ __('event_applications.wizard.sections.technical_docs') }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach ($promotionItems as $code)
                                <div class="flex items-center gap-2 p-2.5 bg-slate-50 rounded-lg">
                                    <x-heroicon-s-check-circle class="w-4 h-4 text-green-500 flex-shrink-0" />
                                    <span class="text-sm text-slate-700">{{ __('event_applications.wizard.checklist_items.' . $code) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (!empty($fd['technical_documents_description']))
                    <div>
                        <p class="text-xs text-slate-500 uppercase tracking-wide mb-1.5">{{ __('event_applications.wizard.labels.technical_documents_description') }}</p>
                        <div class="bg-slate-50 rounded-lg p-3 text-sm text-slate-700 whitespace-pre-line">{{ $fd['technical_documents_description'] }}</div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Budget Summary --}}
    @if (!empty($fd['expenses']) || !empty($fd['revenue']))
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
                        <p class="text-xl font-bold text-rose-700 tabular-nums">{{ money($totalExpenses) }}</p>
                    </div>
                    <div class="p-4 bg-emerald-50 rounded-lg border border-emerald-200">
                        <p class="text-xs font-medium text-emerald-600">{{ __('event_applications.wizard.sections.revenue') }}</p>
                        <p class="text-xl font-bold text-emerald-700 tabular-nums">{{ money($totalRevenue) }}</p>
                    </div>
                    <div class="p-4 {{ ($totalRevenue - $totalExpenses) >= 0 ? 'bg-blue-50 border-blue-200' : 'bg-amber-50 border-amber-200' }} rounded-lg border">
                        <p class="text-xs font-medium {{ ($totalRevenue - $totalExpenses) >= 0 ? 'text-blue-600' : 'text-amber-600' }}">{{ __('event_applications.wizard.labels.balance') }}</p>
                        <p class="text-xl font-bold {{ ($totalRevenue - $totalExpenses) >= 0 ? 'text-blue-700' : 'text-amber-700' }} tabular-nums">{{ money($totalRevenue - $totalExpenses) }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Documents --}}
    @if ($application && $application->documents->count() > 0)
        <div class="card">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ __('event_applications.wizard.steps.documents') }}</h3>
            <ul class="divide-y divide-gray-100">
                @foreach ($application->documents as $doc)
                    <li class="py-2 flex items-center gap-2 text-sm text-slate-700">
                        <x-heroicon-o-document class="w-4 h-4 text-gray-400 shrink-0" />
                        <span>{{ $doc->original_filename ?? $doc->name ?? $doc->file_name }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
