@php
    $roleConfig = [
        'athletes' => [
            'icon' => 'heroicon-o-user-group',
            'color' => 'blue',
            'gradient' => 'from-blue-500 to-blue-600',
            'bg' => 'bg-blue-50',
            'ring' => 'ring-blue-500',
            'border' => 'border-blue-200',
            'iconBg' => 'bg-blue-100',
            'iconColor' => 'text-blue-600',
        ],
        'coaches' => [
            'icon' => 'heroicon-o-academic-cap',
            'color' => 'emerald',
            'gradient' => 'from-emerald-500 to-emerald-600',
            'bg' => 'bg-emerald-50',
            'ring' => 'ring-emerald-500',
            'border' => 'border-emerald-200',
            'iconBg' => 'bg-emerald-100',
            'iconColor' => 'text-emerald-600',
        ],
        'officials' => [
            'icon' => 'heroicon-o-clipboard-document-check',
            'color' => 'amber',
            'gradient' => 'from-amber-500 to-amber-600',
            'bg' => 'bg-amber-50',
            'ring' => 'ring-amber-500',
            'border' => 'border-amber-200',
            'iconBg' => 'bg-amber-100',
            'iconColor' => 'text-amber-600',
        ],
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
        'staff' => [
            'icon' => 'heroicon-o-briefcase',
            'color' => 'slate',
            'gradient' => 'from-slate-500 to-slate-600',
            'bg' => 'bg-slate-50',
            'ring' => 'ring-slate-500',
            'border' => 'border-slate-200',
            'iconBg' => 'bg-slate-100',
            'iconColor' => 'text-slate-600',
        ],
    ];

    $allRoles = ['athletes', 'coaches', 'officials', 'referees', 'staff'];
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
                {{-- Left: Event Info --}}
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-100">
                            <x-heroicon-s-check-badge class="w-6 h-6 text-primary-600" />
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-primary-50 text-primary-700 border border-primary-200">
                            {{ __('events.technical_delegate') }}
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">{{ $event->name }}</h1>
                    <p class="mt-1 text-gray-500 text-sm">{{ __('events.has_view_only_access') }}</p>

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

                {{-- Right: Total Count Badge --}}
                <div class="flex-shrink-0">
                    <div class="inline-flex flex-col items-center justify-center px-5 py-3 rounded-xl bg-gray-50 border border-gray-200">
                        <span class="text-3xl sm:text-4xl font-bold text-gray-900 tabular-nums">{{ $this->totalCount }}</span>
                        <span class="text-xs font-medium text-gray-500 mt-0.5">{{ __('events.confirmed') }}</span>
                    </div>
                </div>
            </div>

            {{-- Navigation --}}
            <div class="mt-5 pt-4 border-t border-gray-100 flex items-center gap-4">
                <a href="{{ route('individual.technical-delegate.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-primary-light rounded-lg font-medium text-sm text-primary tracking-wide shadow-sm hover:bg-secondary-light focus:outline-none focus:border-primary focus:ring focus:ring-primary-light/30 transition-colors duration-150">
                    <x-heroicon-m-arrow-left class="w-4 h-4" />
                    {{ __('events.back_to_events') }}
                </a>
                <a href="{{ route('individual.technical-delegate.td-report', $event) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-emerald-300 rounded-lg font-medium text-sm text-emerald-700 tracking-wide shadow-sm hover:bg-emerald-50 focus:outline-none focus:border-emerald-500 focus:ring focus:ring-emerald-200/30 transition-colors duration-150">
                    <x-heroicon-m-document-text class="w-4 h-4" />
                    {{ __('events.td_report') }}
                </a>
            </div>
        </div>
    </div>

    {{-- Summary Cards Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4"
         x-show="loaded"
         x-transition:enter="transition ease-out duration-500 delay-100"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0">

        @foreach($allRoles as $role)
            @php
                $config = $roleConfig[$role];
                $count = $this->{$role . 'Count'};
                $isActive = $activeTab === $role;
            @endphp
            <button wire:click="setActiveTab('{{ $role }}')"
                    wire:loading.attr="disabled"
                    class="relative group text-left bg-white rounded-xl border-2 p-4 sm:p-5 transition-all duration-300 ease-out focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $config['ring'] }}
                           {{ $isActive ? $config['border'] . ' ' . $config['bg'] . ' shadow-md ring-2 ' . $config['ring'] : 'border-gray-100 hover:border-gray-200 hover:shadow-md' }}"
                    aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                    aria-label="{{ __('events.' . $role . '_tab') }}">

                {{-- Active Indicator --}}
                @if($isActive)
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2">
                        <span class="flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $config['iconBg'] }} opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-gradient-to-r {{ $config['gradient'] }}"></span>
                        </span>
                    </div>
                @endif

                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-medium text-gray-500 truncate">{{ __('events.' . $role . '_tab') }}</p>
                        <p class="mt-1 text-2xl sm:text-3xl font-bold text-gray-900 tabular-nums transition-transform duration-200 {{ $isActive ? 'scale-105' : 'group-hover:scale-105' }}">
                            {{ $count }}
                        </p>
                        @if($count > 0)
                            <div class="mt-2 flex items-center gap-1">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                                    <x-heroicon-m-check class="w-3 h-3 mr-0.5" />
                                    {{ __('events.confirmed') }}
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="flex-shrink-0 p-2.5 sm:p-3 rounded-xl {{ $config['iconBg'] }} transition-transform duration-200 {{ $isActive ? 'scale-110' : 'group-hover:scale-110' }}">
                        <x-dynamic-component :component="$config['icon']" class="w-5 h-5 sm:w-6 sm:h-6 {{ $config['iconColor'] }}" />
                    </div>
                </div>

                {{-- Loading Overlay --}}
                <div wire:loading wire:target="setActiveTab('{{ $role }}')"
                     class="absolute inset-0 bg-white/50 backdrop-blur-sm rounded-xl flex items-center justify-center">
                    <svg class="animate-spin h-5 w-5 {{ $config['iconColor'] }}" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </button>
        @endforeach
    </div>

    {{-- Main Content Card with Tabs --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
         x-show="loaded"
         x-transition:enter="transition ease-out duration-500 delay-200"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0">

        {{-- Tab Navigation Bar --}}
        <div class="border-b border-gray-200 bg-gray-50/50">
            <nav class="flex overflow-x-auto scrollbar-hide -mb-px" aria-label="Tabs">
                @foreach($allRoles as $role)
                    @php
                        $config = $roleConfig[$role];
                        $count = $this->{$role . 'Count'};
                        $isActive = $activeTab === $role;
                    @endphp
                    <button wire:click="setActiveTab('{{ $role }}')"
                            class="relative flex-shrink-0 px-4 sm:px-6 py-4 text-sm font-medium transition-all duration-200 border-b-2 focus:outline-none focus:ring-inset focus:ring-2 focus:ring-primary-500
                                   {{ $isActive
                                       ? 'border-' . $config['color'] . '-500 text-' . $config['color'] . '-600 bg-white'
                                       : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 hover:bg-gray-50' }}"
                            role="tab"
                            aria-selected="{{ $isActive ? 'true' : 'false' }}">
                        <span class="flex items-center gap-2">
                            <x-dynamic-component :component="$config['icon']" class="w-4 h-4 sm:w-5 sm:h-5" />
                            <span class="hidden sm:inline">{{ __('events.' . $role . '_tab') }}</span>
                            <span class="inline-flex items-center justify-center min-w-[1.5rem] px-1.5 py-0.5 rounded-full text-xs font-semibold transition-colors
                                         {{ $isActive ? $config['iconBg'] . ' ' . $config['iconColor'] : 'bg-gray-100 text-gray-600' }}">
                                {{ $count }}
                            </span>
                        </span>
                        @if($isActive)
                            <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r {{ $config['gradient'] }}"></span>
                        @endif
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- Table Content --}}
        <div class="p-4 sm:p-6" wire:loading.class="opacity-50">
            @if ($this->totalCount === 0)
                {{-- Empty State --}}
                <div class="text-center py-12 sm:py-16 px-4">
                    <div class="relative inline-flex items-center justify-center w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 mb-6">
                        <x-heroicon-o-users class="w-10 h-10 sm:w-12 sm:h-12 text-gray-400" />
                        <div class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center">
                            <x-heroicon-m-clock class="w-4 h-4 text-amber-600" />
                        </div>
                    </div>
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900">{{ __('events.no_confirmed_enrollments') }}</h3>
                    <p class="mt-2 text-sm sm:text-base text-gray-500 max-w-md mx-auto">{{ __('events.no_confirmed_enrollments_desc') }}</p>
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

    {{-- Export Actions Panel --}}
    @if ($this->totalCount > 0)
        <div class="bg-gradient-to-r from-slate-50 to-gray-50 rounded-xl border border-gray-200 overflow-hidden"
             x-show="loaded"
             x-transition:enter="transition ease-out duration-500 delay-300"
             x-transition:enter-start="opacity-0 transform translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="p-4 sm:p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    {{-- Info Section --}}
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 p-2 rounded-lg bg-white border border-gray-200 shadow-sm">
                            <x-heroicon-o-document-arrow-down class="w-5 h-5 text-gray-600" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">{{ __('events.export_info') }}</h3>
                            <p class="mt-0.5 text-sm text-gray-500">{{ __('events.export_description') }}</p>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        {{-- Excel Export --}}
                        <button wire:click="exportToExcel('all')"
                                wire:loading.attr="disabled"
                                wire:target="exportToExcel"
                                class="relative inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed group">
                            <x-heroicon-o-table-cells class="w-4 h-4 text-green-600 transition-transform group-hover:scale-110" />
                            <span>{{ __('events.export_all_excel') }}</span>
                            <div wire:loading wire:target="exportToExcel" class="absolute inset-0 flex items-center justify-center bg-white/80 rounded-lg">
                                <svg class="animate-spin h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </div>
                        </button>

                        {{-- PDF Export --}}
                        <button wire:click="generatePdf"
                                wire:loading.attr="disabled"
                                wire:target="generatePdf"
                                class="relative inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-red-600 to-rose-600 rounded-lg shadow-sm hover:from-red-700 hover:to-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed group">
                            <x-heroicon-o-document class="w-4 h-4 transition-transform group-hover:scale-110" />
                            <span>{{ __('events.generate_pdf') }}</span>
                            <div wire:loading wire:target="generatePdf" class="absolute inset-0 flex items-center justify-center bg-red-600/80 rounded-lg">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Quick Stats Footer --}}
            <div class="px-4 sm:px-6 py-3 bg-white/50 border-t border-gray-200/80">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-x-6 gap-y-2 text-xs text-gray-500">
                    @foreach($allRoles as $role)
                        @php $config = $roleConfig[$role]; @endphp
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-{{ $config['color'] }}-500"></span>
                            {{ $this->{$role . 'Count'} }} {{ __('events.' . $role . '_tab') }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <style>
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
    </style>
</div>
