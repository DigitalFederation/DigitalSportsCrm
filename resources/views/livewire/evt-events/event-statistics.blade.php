<div>
    @if ($statistics['summary']['total_entries'] > 0)
        <div class="card w-full mb-4">
            <div class="flex justify-between items-center border-b border-gray-300 pb-2 mb-4">
                <div class="flex gap-x-2 items-center">
                    <x-heroicon-o-chart-bar class="w-6 h-6 text-slate-600" />
                    <span class="font-bold">{{ __('events.enrollment_statistics') }}</span>
                </div>
            </div>

            {{-- Summary Cards Grid --}}
            <div class="grid grid-cols-3 gap-4 mb-6">
                {{-- Row 1: Total Athletes --}}
                {{-- Total Athletes --}}
                <x-filament::card>
                    <div class="flex items-center gap-x-2">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <x-heroicon-o-users class="w-6 h-6 text-green-700" />
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">{{ __('events.total_athletes') }}</div>
                            <div class="text-2xl font-bold text-gray-900">
                                {{ $statistics['summary']['total_athletes'] }}
                            </div>
                        </div>
                    </div>
                </x-filament::card>

                {{-- Total Female Athletes --}}
                <x-filament::card>
                    <div class="flex items-center gap-x-2">
                        <div class="p-2 bg-pink-100 rounded-lg">
                            <x-heroicon-o-users class="w-6 h-6 text-pink-700" />
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">{{ __('events.total_female') }}</div>
                            <div class="text-2xl font-bold text-gray-900">
                                {{ $statistics['summary']['total_unique_female'] }}
                            </div>
                        </div>
                    </div>
                </x-filament::card>

                {{-- Total Male Athletes --}}
                <x-filament::card>
                    <div class="flex items-center gap-x-2">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <x-heroicon-o-users class="w-6 h-6 text-blue-700" />
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">{{ __('events.total_male') }}</div>
                            <div class="text-2xl font-bold text-gray-900">
                                {{ $statistics['summary']['total_unique_male'] }}
                            </div>
                        </div>
                    </div>
                </x-filament::card>

                {{-- Row 2: Entries --}}
                {{-- Total Entries --}}
                <x-filament::card>
                    <div class="flex items-center gap-x-2">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <x-heroicon-o-clipboard-document-list class="w-6 h-6 text-yellow-700" />
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">{{ __('events.total_entries') }}</div>
                            <div class="text-2xl font-bold text-gray-900">
                                {{ $statistics['summary']['total_entries'] }}
                            </div>
                        </div>
                    </div>
                </x-filament::card>

                {{-- Female Entries --}}
                <x-filament::card>
                    <div class="flex items-center gap-x-2">
                        <div class="p-2 bg-pink-100 rounded-lg">
                            <x-heroicon-o-user class="w-6 h-6 text-pink-700" />
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">{{ __('events.entries_female') }}</div>
                            <div class="text-2xl font-bold text-gray-900">
                                {{ $statistics['summary']['individual_female'] }}
                            </div>
                        </div>
                    </div>
                </x-filament::card>

                {{-- Male Entries --}}
                <x-filament::card>
                    <div class="flex items-center gap-x-2">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <x-heroicon-o-user class="w-6 h-6 text-blue-700" />
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">{{ __('events.entries_male') }}</div>
                            <div class="text-2xl font-bold text-gray-900">
                                {{ $statistics['summary']['individual_male'] }}
                            </div>
                        </div>
                    </div>
                </x-filament::card>

                {{-- Row 3: Relay Entries --}}
                {{-- Relay Male --}}
                <x-filament::card>
                    <div class="flex items-center gap-x-2">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <x-heroicon-o-users class="w-6 h-6 text-blue-700" />
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">{{ __('events.entries_relay_male') }}</div>
                            <div class="text-2xl font-bold text-gray-900">
                                {{ $statistics['summary']['relay_stats']['male'] }}
                            </div>
                        </div>
                    </div>
                </x-filament::card>

                {{-- Relay Female --}}
                <x-filament::card>
                    <div class="flex items-center gap-x-2">
                        <div class="p-2 bg-pink-100 rounded-lg">
                            <x-heroicon-o-users class="w-6 h-6 text-pink-700" />
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">{{ __('events.entries_relay_female') }}</div>
                            <div class="text-2xl font-bold text-gray-900">
                                {{ $statistics['summary']['relay_stats']['female'] }}
                            </div>
                        </div>
                    </div>
                </x-filament::card>

                {{-- Relay Mixed --}}
                <x-filament::card>
                    <div class="flex items-center gap-x-2">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <x-heroicon-o-users class="w-6 h-6 text-red-700" />
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">{{ __('events.entries_relay_mixed') }}</div>
                            <div class="text-2xl font-bold text-gray-900">
                                {{ $statistics['summary']['relay_stats']['mixed'] }}
                            </div>
                        </div>
                    </div>
                </x-filament::card>
            </div>

            {{-- Enhanced Tabs Navigation --}}
            <div class="border-t border-gray-200">
                {{-- Compact Tabs --}}
                <div class="bg-white border-b border-gray-200">
                    <nav class="flex px-2" aria-label="Tabs">
                        @foreach ($tabs as $key => $label)
                            <button wire:click="setActiveTab('{{ $key }}')"
                                class="relative px-4 py-2.5 text-sm font-medium transition-colors
                                    {{ $activeTab === $key
                                        ? 'text-blue-600 border-b-2 border-blue-500'
                                        : 'text-gray-500 hover:text-gray-700 hover:border-b-2 hover:border-gray-300' }}"
                                aria-current="{{ $activeTab === $key ? 'page' : 'false' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </nav>
                </div>

                <div class="bg-white mt-4">
                    @if ($activeTab === 'country')
                        <div class="flex justify-end mb-4">
                            <button wire:click="exportCountryStatsCSV"
                                class="inline-flex btn btn-info btn-sm items-center">
                                <x-heroicon-o-arrow-down-tray class="w-4 h-4 mr-2" />
                                {{ __('events.export_csv') }}
                            </button>
                        </div>
                        <div class="overflow-x-auto border border-gray-200 rounded-sm">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th
                                            class="sticky left-0 bg-gray-50 px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider border-r">
                                            {{ __('events.country') }}</th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            {{ __('events.athlete_entries') }}</th>

                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            {{ __('events.total_athletes_unique') }}</th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            {{ __('events.male_athletes') }}</th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            {{ __('events.female_athletes') }}</th>
                                        @if (
                                            $statistics['summary']['relay_stats']['male'] +
                                                $statistics['summary']['relay_stats']['female'] +
                                                $statistics['summary']['relay_stats']['mixed'] >
                                                0)
                                            <th
                                                class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                                {{ __('events.relay_athletes') }}</th>
                                        @endif
                                        @if (
                                            $statistics['summary']['team_stats']['male'] +
                                                $statistics['summary']['team_stats']['female'] +
                                                $statistics['summary']['team_stats']['mixed'] >
                                                0)
                                            <th
                                                class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                                {{ __('events.team_athletes') }}</th>
                                        @endif
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            {{ __('events.total_coaches') }}</th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            {{ __('events.total_officials') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($statistics['byCountry'] as $country => $stats)
                                        <tr class="hover:bg-gray-50">
                                            <td
                                                class="sticky left-0 bg-white px-3 py-1.5 text-sm font-medium text-gray-900 border-r">
                                                <div class="flex items-center gap-1.5">
                                                    <img src="{{ asset('img/flags/' . $stats['country_iso'] . '.svg') }}"
                                                        alt="{{ $country }}" class="w-6 h-6 mr-2">
                                                    {{ $country }}
                                                </div>
                                            </td>
                                            <td class="px-3 py-1.5 text-sm text-gray-900">{{ $stats['total_entries'] }}
                                            </td>

                                            <td class="px-3 py-1.5 text-sm text-gray-900">
                                                {{ $stats['individual_athletes'] }}</td>
                                            <td class="px-3 py-1.5 text-sm text-blue-600">{{ $stats['male_athletes'] }}
                                            </td>
                                            <td class="px-3 py-1.5 text-sm text-pink-600">
                                                {{ $stats['female_athletes'] }}</td>
                                            @if (
                                                $statistics['summary']['relay_stats']['male'] +
                                                    $statistics['summary']['relay_stats']['female'] +
                                                    $statistics['summary']['relay_stats']['mixed'] >
                                                    0)
                                                <td class="px-3 py-1.5 text-sm text-gray-900">
                                                    {{ $stats['relay_teams'] }}</td>
                                            @endif
                                            @if (
                                                $statistics['summary']['team_stats']['male'] +
                                                    $statistics['summary']['team_stats']['female'] +
                                                    $statistics['summary']['team_stats']['mixed'] >
                                                    0)
                                                <td class="px-3 py-1.5 text-sm text-gray-900">
                                                    {{ $stats['team_athletes'] }}</td>
                                            @endif
                                            <td class="px-3 py-1.5 text-sm text-gray-900">{{ $stats['total_coaches'] }}
                                            </td>
                                            <td class="px-3 py-1.5 text-sm text-gray-900">
                                                {{ $stats['total_officials'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif($activeTab === 'discipline')
                        <div class="flex justify-end mb-4">
                            <button wire:click="exportDisciplineStatsCSV"
                                class="inline-flex btn btn-info btn-sm items-center">
                                <x-heroicon-o-arrow-down-tray class="w-4 h-4 mr-2" />
                                {{ __('events.export_csv') }}
                            </button>
                        </div>
                        <div class="overflow-x-auto border border-gray-200 rounded-sm">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th
                                            class="sticky left-0 bg-gray-50 px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider border-r">
                                            {{ __('events.discipline') }}</th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            {{ __('events.entries') }}</th>
                                        @if (
                                            $statistics['summary']['relay_stats']['male'] +
                                                $statistics['summary']['relay_stats']['female'] +
                                                $statistics['summary']['relay_stats']['mixed'] >
                                                0)
                                            <th
                                                class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                                {{ __('events.relay') }}</th>
                                        @endif
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            {{ __('events.clubs') }}</th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            {{ __('events.nations') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($statistics['byDiscipline'] as $discipline => $stats)
                                        <tr class="hover:bg-gray-50">
                                            <td
                                                class="sticky left-0 bg-white px-3 py-1.5 text-sm font-medium text-gray-900 border-r">
                                                <div class="flex items-center gap-1.5">
                                                    {{ $discipline }}
                                                </div>
                                            </td>
                                            <td class="px-3 py-1.5 text-sm text-gray-900">
                                                {{ $stats['entries'] }}
                                                <span
                                                    class="text-xs text-gray-500 ml-1">({{ number_format(($stats['entries'] / $statistics['summary']['total_entries']) * 100, 1) }}%)</span>
                                            </td>
                                            @if (
                                                $statistics['summary']['relay_stats']['male'] +
                                                    $statistics['summary']['relay_stats']['female'] +
                                                    $statistics['summary']['relay_stats']['mixed'] >
                                                    0)
                                                <td class="px-3 py-1.5 text-sm text-gray-900">
                                                    {{ $stats['relay_teams'] }}</td>
                                            @endif
                                            <td class="px-3 py-1.5 text-sm text-gray-900">{{ $stats['clubs'] }}</td>
                                            <td class="px-3 py-1.5 text-sm text-gray-900">
                                                {{ $stats['organizations'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif ($activeTab === 'entity')
                        <div class="flex justify-end mb-4">
                            <button wire:click="exportEntityStatsCSV"
                                class="inline-flex btn btn-info btn-sm items-center">
                                <x-heroicon-o-arrow-down-tray class="w-4 h-4 mr-2" />
                                {{ __('events.export_csv') }}
                            </button>
                        </div>
                        <div class="overflow-x-auto border border-gray-200 rounded-sm">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th
                                            class="sticky left-0 bg-gray-50 px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider border-r">
                                            {{ __('events.name') }}</th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            {{ __('events.type') }}</th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            {{ __('events.nationality') }}</th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            {{ __('events.athlete_entries') }}</th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            {{ __('events.individual_male') }}</th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            {{ __('events.individual_female') }}</th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            {{ __('events.relay_male') }}</th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            {{ __('events.relay_female') }}</th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                            {{ __('events.relay_mixed') }}</th>
                                        @if (
                                            $statistics['summary']['team_stats']['male'] +
                                                $statistics['summary']['team_stats']['female'] +
                                                $statistics['summary']['team_stats']['mixed'] >
                                                0)
                                            <th
                                                class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                                Teams</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($statistics['byEntity'] as $entity => $stats)
                                        <tr class="hover:bg-gray-50">
                                            <td
                                                class="sticky left-0 bg-white px-3 py-1.5 text-sm font-medium text-gray-900 border-r">
                                                <div class="flex items-center gap-1.5">
                                                    {{ $stats['name'] }}
                                                </div>
                                            </td>
                                            <td class="px-3 py-1.5 text-sm text-gray-900">{{ $stats['type'] }}</td>
                                            <td class="px-3 py-1.5 text-sm text-gray-900">
                                                <div class="flex items-center gap-1.5">
                                                    <img src="{{ asset('img/flags/' . $stats['country_iso'] . '.svg') }}"
                                                        alt="{{ $stats['country'] }}" class="w-6 h-6 mr-2">
                                                    {{ $stats['country'] }}
                                                </div>
                                            </td>
                                            <td class="px-3 py-1.5 text-sm text-gray-900">
                                                {{ $stats['total_entries'] }}</td>
                                            <td class="px-3 py-1.5 text-sm text-gray-900">
                                                {{ $stats['individual_male'] }}</td>
                                            <td class="px-3 py-1.5 text-sm text-gray-900">
                                                {{ $stats['individual_female'] }}</td>
                                            <td class="px-3 py-1.5 text-sm text-gray-900">{{ $stats['relay_male'] }}
                                            </td>
                                            <td class="px-3 py-1.5 text-sm text-gray-900">{{ $stats['relay_female'] }}
                                            </td>
                                            <td class="px-3 py-1.5 text-sm text-gray-900">{{ $stats['relay_mixed'] }}
                                            </td>
                                            @if (
                                                $statistics['summary']['team_stats']['male'] +
                                                    $statistics['summary']['team_stats']['female'] +
                                                    $statistics['summary']['team_stats']['mixed'] >
                                                    0)
                                                <td class="px-3 py-1.5 text-sm text-gray-900">
                                                    {{ $stats['team_athletes'] }}</td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    @endif
</div>
