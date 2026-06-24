@php
    $tabConfig = [
        'history' => [
            'icon' => 'heroicon-o-clock',
            'color' => 'purple',
            'gradient' => 'from-purple-500 to-purple-600',
            'bg' => 'bg-purple-50',
            'ring' => 'ring-purple-500',
            'border' => 'border-purple-200',
            'iconBg' => 'bg-purple-100',
            'iconColor' => 'text-purple-600',
        ],
        'evaluation' => [
            'icon' => 'heroicon-o-trophy',
            'color' => 'emerald',
            'gradient' => 'from-emerald-500 to-emerald-600',
            'bg' => 'bg-emerald-50',
            'ring' => 'ring-emerald-500',
            'border' => 'border-emerald-200',
            'iconBg' => 'bg-emerald-100',
            'iconColor' => 'text-emerald-600',
        ],
    ];

    $allTabs = ['history', 'evaluation'];
@endphp

<div class="space-y-6">

    {{-- Tab Cards --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4">
        @foreach($allTabs as $tab)
            @php
                $tc = $tabConfig[$tab];
                $isActive = $activeTab === $tab;
            @endphp
            <button wire:click="setActiveTab('{{ $tab }}')"
                    wire:loading.attr="disabled"
                    class="relative group text-left bg-white rounded-xl border-2 p-4 sm:p-5 transition-all duration-300 ease-out focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $tc['ring'] }}
                           {{ $isActive ? $tc['border'] . ' ' . $tc['bg'] . ' shadow-md ring-2 ' . $tc['ring'] : 'border-gray-100 hover:border-gray-200 hover:shadow-md' }}"
                    aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                    aria-label="{{ __('events.admin_referee_' . $tab . '_tab') }}">

                {{-- Active Indicator --}}
                @if($isActive)
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2">
                        <span class="flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $tc['iconBg'] }} opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-gradient-to-r {{ $tc['gradient'] }}"></span>
                        </span>
                    </div>
                @endif

                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-medium truncate {{ $isActive ? $tc['iconColor'] : 'text-gray-500' }}">{{ __('events.admin_referee_' . $tab . '_tab') }}</p>
                        <p class="mt-2 text-sm text-gray-500">
                            @if($tab === 'history')
                                {{ __('events.admin_referee_enrollments_history_description') }}
                            @else
                                {{ __('events.evaluation_ranking_description') }}
                            @endif
                        </p>
                    </div>
                    <div class="flex-shrink-0 p-2.5 sm:p-3 rounded-xl {{ $tc['iconBg'] }} transition-transform duration-200 {{ $isActive ? 'scale-110' : 'group-hover:scale-110' }}">
                        <x-dynamic-component :component="$tc['icon']" class="w-5 h-5 sm:w-6 sm:h-6 {{ $tc['iconColor'] }}" />
                    </div>
                </div>

                {{-- Loading Overlay --}}
                <div wire:loading wire:target="setActiveTab('{{ $tab }}')"
                     class="absolute inset-0 bg-white/50 backdrop-blur-sm rounded-xl flex items-center justify-center">
                    <svg class="animate-spin h-5 w-5 {{ $tc['iconColor'] }}" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </button>
        @endforeach
    </div>

    {{-- Content --}}
    <div wire:loading.class="opacity-50">
        @if($activeTab === 'history')
            <div class="min-w-full inline-block align-middle">
                {{ $this->table }}
            </div>
        @else
            {{-- Evaluation Ranking Table --}}
            <div class="space-y-4">

                {{-- Filters --}}
                <div class="bg-white rounded-xl border border-gray-200 p-4 sm:p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-3 sm:gap-4">
                        {{-- Sport Filter --}}
                        <div>
                            <label for="evalSportFilter" class="block text-xs font-medium text-gray-600 mb-1">{{ __('events.sport') }}</label>
                            <select wire:model.live="evalSportFilter" id="evalSportFilter"
                                    class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">{{ __('events.all') }}</option>
                                @foreach($sportOptions as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Name Filter --}}
                        <div>
                            <label for="evalNameFilter" class="block text-xs font-medium text-gray-600 mb-1">{{ __('events.technical_official') }}</label>
                            <input wire:model.live.debounce.300ms="evalNameFilter" id="evalNameFilter" type="text"
                                   placeholder="{{ __('events.filter_by_name') }}"
                                   class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        {{-- XP Min --}}
                        <div>
                            <label for="evalExpMin" class="block text-xs font-medium text-gray-600 mb-1">{{ __('events.experience_points') }} ({{ __('events.min') }})</label>
                            <input wire:model.live.debounce.300ms="evalExpMin" id="evalExpMin" type="number" min="0"
                                   placeholder="{{ __('events.min') }}"
                                   class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        {{-- XP Max --}}
                        <div>
                            <label for="evalExpMax" class="block text-xs font-medium text-gray-600 mb-1">{{ __('events.experience_points') }} ({{ __('events.max') }})</label>
                            <input wire:model.live.debounce.300ms="evalExpMax" id="evalExpMax" type="number" min="0"
                                   placeholder="{{ __('events.max') }}"
                                   class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        {{-- Level Min --}}
                        <div>
                            <label for="evalLevelMin" class="block text-xs font-medium text-gray-600 mb-1">{{ __('events.average_level') }} ({{ __('events.min') }})</label>
                            <input wire:model.live.debounce.300ms="evalLevelMin" id="evalLevelMin" type="number" min="0" max="5" step="0.1"
                                   placeholder="{{ __('events.min') }}"
                                   class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        {{-- Level Max --}}
                        <div>
                            <label for="evalLevelMax" class="block text-xs font-medium text-gray-600 mb-1">{{ __('events.average_level') }} ({{ __('events.max') }})</label>
                            <input wire:model.live.debounce.300ms="evalLevelMax" id="evalLevelMax" type="number" min="0" max="5" step="0.1"
                                   placeholder="{{ __('events.max') }}"
                                   class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>
                </div>

                {{-- Ranking Table --}}
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="sortEvaluation('sport_name')" class="flex items-center gap-1 hover:text-gray-700">
                                            {{ __('events.sport') }}
                                            @if($evalSortBy === 'sport_name')
                                                <x-heroicon-s-chevron-{{ $evalSortDir === 'asc' ? 'up' : 'down' }} class="w-3 h-3" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="sortEvaluation('individual_name')" class="flex items-center gap-1 hover:text-gray-700">
                                            {{ __('events.technical_official') }}
                                            @if($evalSortBy === 'individual_name')
                                                <x-heroicon-s-chevron-{{ $evalSortDir === 'asc' ? 'up' : 'down' }} class="w-3 h-3" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="sortEvaluation('total_events')" class="flex items-center gap-1 hover:text-gray-700">
                                            {{ __('events.total_events') }}
                                            @if($evalSortBy === 'total_events')
                                                <x-heroicon-s-chevron-{{ $evalSortDir === 'asc' ? 'up' : 'down' }} class="w-3 h-3" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="sortEvaluation('experience_points')" class="flex items-center gap-1 hover:text-gray-700">
                                            {{ __('events.experience_points') }}
                                            @if($evalSortBy === 'experience_points')
                                                <x-heroicon-s-chevron-{{ $evalSortDir === 'asc' ? 'up' : 'down' }} class="w-3 h-3" />
                                            @endif
                                        </button>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <button wire:click="sortEvaluation('average_level')" class="flex items-center gap-1 hover:text-gray-700">
                                            {{ __('events.average_level') }}
                                            @if($evalSortBy === 'average_level')
                                                <x-heroicon-s-chevron-{{ $evalSortDir === 'asc' ? 'up' : 'down' }} class="w-3 h-3" />
                                            @endif
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($evaluationRanking as $index => $row)
                                    <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-gray-100 transition-colors" wire:key="eval-{{ $index }}">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $row->sport_name }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                            {{ $row->individual_name }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 tabular-nums">
                                            {{ $row->total_events }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="text-sm font-bold text-gray-900 tabular-nums">{{ $row->experience_points }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if($row->average_level !== null)
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-semibold text-gray-900 tabular-nums">{{ $row->average_level }}</span>
                                                    <span class="text-xs text-gray-500">/ 5</span>
                                                    <div class="flex items-center gap-0.5">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            @if($i <= round($row->average_level))
                                                                <x-heroicon-s-star class="w-3.5 h-3.5 text-amber-400" />
                                                            @else
                                                                <x-heroicon-o-star class="w-3.5 h-3.5 text-gray-300" />
                                                            @endif
                                                        @endfor
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-400">{{ __('events.no_evaluation') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-12 text-center">
                                            <div class="flex flex-col items-center">
                                                <x-heroicon-o-trophy class="w-12 h-12 text-gray-300 mb-3" />
                                                <p class="text-sm text-gray-500">{{ __('events.no_evaluation_data') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

</div>
