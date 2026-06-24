@php
    $config = [
        'icon' => 'heroicon-o-clock',
        'color' => 'purple',
        'gradient' => 'from-purple-500 to-purple-600',
        'bg' => 'bg-purple-50',
        'ring' => 'ring-purple-500',
        'border' => 'border-purple-200',
        'iconBg' => 'bg-purple-100',
        'iconColor' => 'text-purple-600',
    ];

    $tabConfig = [
        'referees' => [
            'icon' => 'heroicon-o-scale',
            'color' => 'purple',
            'gradient' => 'from-purple-500 to-purple-600',
            'bg' => 'bg-purple-50',
            'ring' => 'ring-purple-500',
            'border' => 'border-purple-200',
            'iconBg' => 'bg-purple-100',
            'iconColor' => 'text-purple-600',
        ],
        'chief_judge' => [
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

    $allTabs = ['referees', 'chief_judge'];
@endphp

<div class="space-y-6" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">

    {{-- Header --}}
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
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-purple-100">
                            <x-heroicon-s-clock class="w-6 h-6 text-purple-600" />
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">
                            {{ __('events.technical_official') }}
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-semibold text-purple-600">{{ __('events.official_history_title') }}</h1>
                    <p class="mt-1 text-gray-500 text-sm">{{ __('events.official_history_description') }}</p>
                </div>

                {{-- Right: Total Count Badge --}}
                <div class="flex-shrink-0">
                    <div class="inline-flex flex-col items-center justify-center px-5 py-3 rounded-xl bg-purple-50 border border-purple-200">
                        <span class="text-3xl sm:text-4xl font-bold text-gray-900 tabular-nums">{{ $this->historyCount }}</span>
                        <span class="text-xs font-medium text-purple-600 mt-0.5">{{ __('events.events_officiated') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sport Summary Cards --}}
    @if($sportSummaries->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4"
             x-show="loaded"
             x-transition:enter="transition ease-out duration-500 delay-100"
             x-transition:enter-start="opacity-0 transform translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0">

            @foreach($sportSummaries as $summary)
                <div class="relative text-left bg-white rounded-xl border-2 {{ $config['border'] }} {{ $config['bg'] }} shadow-md ring-2 {{ $config['ring'] }} p-4 sm:p-5">
                    {{-- Ping Dot --}}
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2">
                        <span class="flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $config['iconBg'] }} opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-gradient-to-r {{ $config['gradient'] }}"></span>
                        </span>
                    </div>

                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $summary->sport_name }}</p>
                            @if($summary->since_year)
                                <p class="text-xs text-gray-500 mt-0.5">{{ __('events.official_since') }} {{ $summary->since_year }}</p>
                            @endif
                        </div>
                        <div class="flex-shrink-0 p-2.5 sm:p-3 rounded-xl {{ $config['iconBg'] }}">
                            <x-heroicon-o-trophy class="w-5 h-5 sm:w-6 sm:h-6 {{ $config['iconColor'] }}" />
                        </div>
                    </div>

                    <div class="mt-3 space-y-3">
                        {{-- Experience Points (Sum) --}}
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('events.experience_points') }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                @if($summary->total_experience_points !== null)
                                    <span class="text-2xl sm:text-3xl font-bold text-gray-900 tabular-nums">{{ $summary->total_experience_points }}</span>
                                @else
                                    <span class="text-2xl sm:text-3xl font-bold text-gray-400">&mdash;</span>
                                @endif
                            </div>
                        </div>

                        {{-- Level (Average) --}}
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('events.average_level') }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                @if($summary->average_evaluation)
                                    <span class="text-2xl sm:text-3xl font-bold text-gray-900 tabular-nums">{{ $summary->average_evaluation }}</span>
                                    <span class="text-sm text-gray-500">/ 5</span>
                                    <div class="flex items-center gap-0.5 ml-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= round($summary->average_evaluation))
                                                <x-heroicon-s-star class="w-3.5 h-3.5 text-amber-400" />
                                            @else
                                                <x-heroicon-o-star class="w-3.5 h-3.5 text-gray-300" />
                                            @endif
                                        @endfor
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">{{ __('events.no_evaluation') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Tab Cards --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4"
         x-show="loaded"
         x-transition:enter="transition ease-out duration-500 delay-150"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0">

        @foreach($allTabs as $tab)
            @php
                $tc = $tabConfig[$tab];
                $count = $tab === 'referees' ? $this->refereesCount : $this->chiefJudgeCount;
                $isActive = $activeTab === $tab;
            @endphp
            <button wire:click="setActiveTab('{{ $tab }}')"
                    wire:loading.attr="disabled"
                    class="relative group text-left bg-white rounded-xl border-2 p-4 sm:p-5 transition-all duration-300 ease-out focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $tc['ring'] }}
                           {{ $isActive ? $tc['border'] . ' ' . $tc['bg'] . ' shadow-md ring-2 ' . $tc['ring'] : 'border-gray-100 hover:border-gray-200 hover:shadow-md' }}"
                    aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                    aria-label="{{ __('events.' . $tab . '_history_tab') }}">

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
                        <p class="text-xs sm:text-sm font-medium truncate {{ $isActive ? $tc['iconColor'] : 'text-gray-500' }}">{{ __('events.' . $tab . '_history_tab') }}</p>
                        <p class="mt-1 text-2xl sm:text-3xl font-bold text-gray-900 tabular-nums transition-transform duration-200 {{ $isActive ? 'scale-105' : 'group-hover:scale-105' }}">
                            {{ $count }}
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

    {{-- Main Content Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
         x-show="loaded"
         x-transition:enter="transition ease-out duration-500 delay-200"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0">

        <div class="p-4 sm:p-6" wire:loading.class="opacity-50">
            @if($this->historyCount === 0)
                {{-- Empty State --}}
                <div class="text-center py-12 sm:py-16 px-4">
                    <div class="relative inline-flex items-center justify-center w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 mb-6">
                        <x-heroicon-o-clock class="w-10 h-10 sm:w-12 sm:h-12 text-gray-400" />
                        <div class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center">
                            <x-heroicon-m-clipboard-document-list class="w-4 h-4 text-amber-600" />
                        </div>
                    </div>
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900">{{ __('events.no_official_history') }}</h3>
                    <p class="mt-2 text-sm sm:text-base text-gray-500 max-w-md mx-auto">{{ __('events.no_official_history_desc') }}</p>
                </div>
            @else
                {{-- Loading Skeleton --}}
                <div wire:loading.delay.long wire:target="setActiveTab" class="space-y-3">
                    @for($i = 0; $i < 5; $i++)
                        <div class="animate-pulse flex items-center gap-4 p-3 rounded-lg bg-gray-50">
                            <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                            </div>
                            <div class="w-16 h-6 bg-gray-200 rounded-full"></div>
                        </div>
                    @endfor
                </div>

                {{-- Actual Table --}}
                <div wire:loading.remove wire:target="setActiveTab">
                    {{ $this->table }}
                </div>
            @endif
        </div>
    </div>
</div>
