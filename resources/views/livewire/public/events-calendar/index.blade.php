<div class="min-h-screen bg-gray-50">
    {{-- Hero --}}
    <section class="relative bg-blue-900 text-white overflow-hidden">
        <div class="absolute inset-0"
            style="background-image: url('{{ asset('img/placeholder_event_organization.png') }}'); background-size: cover; background-position: center; opacity: 0.30;">
        </div>
        <div class="absolute inset-0"
            style="background: linear-gradient(135deg, rgba(15, 39, 89, 0.92) 0%, rgba(30, 64, 175, 0.78) 60%, rgba(37, 99, 235, 0.65) 100%);">
        </div>

        <div class="relative w-full pt-14 pb-20 sm:pt-20 sm:pb-28"
            style="padding-left: clamp(1.5rem, 5vw, 6rem); padding-right: clamp(1.5rem, 5vw, 6rem);">
            <p class="text-xs sm:text-sm font-semibold tracking-widest uppercase text-blue-200 mb-3">
                {{ __('public.events.eyebrow') }}
            </p>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight mb-4 text-white">
                {{ __('public.events.title') }}
            </h1>
            <p class="max-w-3xl text-base sm:text-lg text-blue-100 mb-8">
                {{ __('public.events.subtitle') }}
            </p>

            {{-- Stats: 3 cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 max-w-3xl">
                <div class="bg-white/10 rounded-xl border border-white/20 px-5 py-4 backdrop-blur-sm text-white">
                    <p class="text-3xl sm:text-4xl font-bold leading-none">{{ $this->stats['events'] }}</p>
                    <p class="mt-2 text-xs sm:text-sm uppercase tracking-wider text-blue-100">
                        {{ __('public.events.stats.events') }}
                    </p>
                </div>
                <div class="bg-white/10 rounded-xl border border-white/20 px-5 py-4 backdrop-blur-sm text-white">
                    <p class="text-3xl sm:text-4xl font-bold leading-none">{{ $this->stats['organizers'] }}</p>
                    <p class="mt-2 text-xs sm:text-sm uppercase tracking-wider text-blue-100">
                        {{ __('public.events.stats.organizers') }}
                    </p>
                </div>
                <div class="bg-white/10 rounded-xl border border-white/20 px-5 py-4 backdrop-blur-sm text-white">
                    <p class="text-3xl sm:text-4xl font-bold leading-none">{{ $this->stats['event_days'] }}</p>
                    <p class="mt-2 text-xs sm:text-sm uppercase tracking-wider text-blue-100">
                        {{ __('public.events.stats.event_days') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Filters + Toolbar --}}
    <section class="w-full mt-8 sm:mt-12 relative z-10"
        style="padding-left: clamp(1.5rem, 5vw, 6rem); padding-right: clamp(1.5rem, 5vw, 6rem);">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
            <div x-data="{ open: window.innerWidth >= 768 }"
                x-init="window.addEventListener('resize', () => { if (window.innerWidth >= 768) open = true; })">
                {{-- Header bar --}}
                <div class="flex items-center justify-between gap-3 px-5 sm:px-8 py-4 border-b border-gray-100">
                    <button type="button" @click="open = !open"
                        class="md:hidden inline-flex items-center text-sm font-semibold text-gray-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        {{ __('public.events.filters.heading') }}
                    </button>
                    <h2 class="hidden md:block text-sm font-semibold uppercase tracking-wider text-gray-500">
                        {{ __('public.events.filters.heading') }}
                    </h2>

                    {{-- View toggle --}}
                    <div class="inline-flex bg-gray-100 rounded-full p-1" role="tablist">
                        <button type="button" wire:click="setView('list')"
                            class="px-3 sm:px-4 py-1.5 text-sm font-medium rounded-full transition-colors duration-150
                                {{ $view === 'list' ? 'bg-white text-blue-700 shadow' : 'text-gray-600 hover:text-gray-900' }}">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                                <span class="hidden sm:inline">{{ __('public.events.view.list') }}</span>
                            </span>
                        </button>
                        <button type="button" wire:click="setView('grid')"
                            class="px-3 sm:px-4 py-1.5 text-sm font-medium rounded-full transition-colors duration-150
                                {{ $view === 'grid' ? 'bg-white text-blue-700 shadow' : 'text-gray-600 hover:text-gray-900' }}">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                                </svg>
                                <span class="hidden sm:inline">{{ __('public.events.view.grid') }}</span>
                            </span>
                        </button>
                        <button type="button" wire:click="setView('calendar')"
                            class="px-3 sm:px-4 py-1.5 text-sm font-medium rounded-full transition-colors duration-150
                                {{ $view === 'calendar' ? 'bg-white text-blue-700 shadow' : 'text-gray-600 hover:text-gray-900' }}">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="hidden sm:inline">{{ __('public.events.view.calendar') }}</span>
                            </span>
                        </button>
                    </div>
                </div>

                {{-- Filter inputs --}}
                <div x-show="open" x-collapse class="px-5 sm:px-8 py-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        {{-- Type --}}
                        <div>
                            <label for="filter-type" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">
                                {{ __('public.events.filters.type') }}
                            </label>
                            <select id="filter-type" wire:model.live="type"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">{{ __('public.events.filters.all') }}</option>
                                @foreach ($this->typeOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Sport --}}
                        <div>
                            <label for="filter-sport" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">
                                {{ __('public.events.filters.sport') }}
                            </label>
                            <select id="filter-sport" wire:model.live="sportId"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">{{ __('public.events.filters.all') }}</option>
                                @foreach ($this->sportOptions as $sport)
                                    <option value="{{ $sport['id'] }}">{{ $sport['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Date from --}}
                        <div>
                            <label for="filter-date-from" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">
                                {{ __('public.events.filters.date_from') }}
                            </label>
                            <input id="filter-date-from" type="date" wire:model.live="dateFrom"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>

                        {{-- Date to --}}
                        <div>
                            <label for="filter-date-to" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">
                                {{ __('public.events.filters.date_to') }}
                            </label>
                            <input id="filter-date-to" type="date" wire:model.live="dateTo"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>

                        {{-- Past + Clear --}}
                        <div class="flex flex-col justify-end gap-2">
                            <label class="inline-flex items-center text-sm text-gray-700">
                                <input type="checkbox" wire:model.live="includePast"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2">{{ __('public.events.filters.include_past') }}</span>
                            </label>
                            <button type="button" wire:click="clearFilters"
                                class="text-xs font-medium text-blue-600 hover:text-blue-800 self-start">
                                {{ __('public.events.filters.clear') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Loading overlay --}}
    <div wire:loading.delay wire:target="sportId, type, dateFrom, dateTo, includePast, previousPeriod, nextPeriod, setView, setCalendarMode, focusMonth"
        class="fixed inset-0 bg-white/60 flex items-center justify-center z-40 pointer-events-none">
        <div class="flex items-center gap-3 bg-white rounded-lg p-4 shadow-xl">
            <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <span class="text-sm font-medium text-gray-900">{{ __('Loading...') }}</span>
        </div>
    </div>

    {{-- Content --}}
    <section class="w-full mt-8 sm:mt-10 pb-14 sm:pb-20"
        style="padding-left: clamp(1.5rem, 5vw, 6rem); padding-right: clamp(1.5rem, 5vw, 6rem);">
        @if ($view === 'calendar')
            @include('livewire.public.events-calendar._calendar', [
                'events' => $this->calendarEvents,
                'periodLabel' => $this->calendarPeriodLabel,
            ])
        @elseif ($view === 'grid')
            @if ($this->events->total() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
                    @foreach ($this->events as $event)
                        @include('livewire.public.events-calendar._event-grid-card', ['event' => $event])
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $this->events->links() }}
                </div>
            @else
                @include('livewire.public.events-calendar._empty-state')
            @endif
        @else
            @if ($this->events->total() > 0)
                @foreach ($this->eventsByMonth as $monthKey => $monthEvents)
                    @php
                        $label = $this->monthLabel($monthKey);
                        $parts = explode(' ', $label);
                        $monthName = $parts[0] ?? $label;
                        $yearLabel = $parts[1] ?? '';
                    @endphp
                    <div class="mb-14 sm:mb-16" wire:key="month-{{ $monthKey }}">
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-4 capitalize">
                            {{ $monthName }}
                            @if ($yearLabel)
                                <span class="text-gray-300 ml-2 font-bold">{{ $yearLabel }}</span>
                            @endif
                        </h2>

                        <div class="space-y-3">
                            @foreach ($monthEvents as $event)
                                @include('livewire.public.events-calendar._event-row', ['event' => $event])
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="mt-8">
                    {{ $this->events->links() }}
                </div>
            @else
                <div class="text-center py-16 bg-white rounded-2xl border border-gray-100 shadow-sm">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">
                        {{ __('public.events.no_results') }}
                    </h3>
                    <p class="text-sm text-gray-500 max-w-md mx-auto mb-4">
                        {{ __('public.events.no_results_hint') }}
                    </p>
                    <button type="button" wire:click="clearFilters"
                        class="inline-flex items-center px-4 py-2 border border-blue-300 text-sm font-medium rounded-lg text-blue-700 bg-white hover:bg-blue-50">
                        {{ __('public.events.filters.clear') }}
                    </button>
                </div>
            @endif
        @endif
    </section>
</div>
