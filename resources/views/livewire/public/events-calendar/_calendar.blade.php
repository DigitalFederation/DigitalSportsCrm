@php
    /** @var \Illuminate\Support\Collection $events */
    /** @var string $periodLabel */
    $weekStart = \Carbon\Carbon::now()->startOfWeek();
    $weekdayLabels = collect(range(0, 6))->map(fn ($i) => $weekStart->copy()->addDays($i)->locale(app()->getLocale())->isoFormat('ddd'));
@endphp

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    {{-- Calendar header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 py-4 border-b border-gray-100">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 capitalize">
            {{ $periodLabel }}
        </h2>

        <div class="flex items-center justify-between sm:justify-end gap-3">
            {{-- Mode toggle --}}
            <div class="inline-flex bg-gray-100 rounded-full p-1" role="tablist">
                <button type="button" wire:click="setCalendarMode('month')"
                    class="px-3 py-1 text-xs font-medium rounded-full transition-colors duration-150
                        {{ $calendarMode === 'month' ? 'bg-white text-blue-700 shadow' : 'text-gray-600 hover:text-gray-900' }}">
                    {{ __('public.events.calendar.month_view') }}
                </button>
                <button type="button" wire:click="setCalendarMode('year')"
                    class="px-3 py-1 text-xs font-medium rounded-full transition-colors duration-150
                        {{ $calendarMode === 'year' ? 'bg-white text-blue-700 shadow' : 'text-gray-600 hover:text-gray-900' }}">
                    {{ __('public.events.calendar.year_view') }}
                </button>
            </div>

            {{-- Period nav --}}
            <div class="inline-flex items-center gap-1">
                <button type="button" wire:click="previousPeriod"
                    class="p-2 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors duration-150"
                    aria-label="{{ __('public.events.calendar.previous') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button type="button" wire:click="nextPeriod"
                    class="p-2 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors duration-150"
                    aria-label="{{ __('public.events.calendar.next') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    @if ($calendarMode === 'year')
        {{-- Year view: 12 mini month grids --}}
        <div class="p-4 sm:p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach ($this->yearMonths($events) as $monthData)
                <div wire:key="ym-{{ $monthData['month'] }}"
                    class="border border-gray-100 rounded-xl overflow-hidden bg-gray-50/40">
                    <button type="button" wire:click="focusMonth({{ $monthData['month'] }})"
                        class="w-full px-3 py-2 bg-white border-b border-gray-100 text-left hover:bg-blue-50 transition-colors duration-150">
                        <span class="text-sm font-semibold text-gray-900 capitalize">{{ $monthData['label'] }}</span>
                    </button>
                    <div class="grid bg-gray-50 border-b border-gray-100"
                        style="grid-template-columns: repeat(7, minmax(0, 1fr));">
                        @foreach ($weekdayLabels as $label)
                            <div class="px-1 py-1 text-center text-[10px] font-semibold uppercase text-gray-400">
                                {{ \Illuminate\Support\Str::substr($label, 0, 1) }}
                            </div>
                        @endforeach
                    </div>
                    <div class="grid"
                        style="grid-template-columns: repeat(7, minmax(0, 1fr));">
                        @foreach ($monthData['days'] as $day)
                            @php $hasEvents = $day['events']->count() > 0; @endphp
                            <div class="aspect-square flex items-center justify-center text-[11px]
                                {{ $day['inMonth'] ? 'bg-white' : 'bg-gray-50/50 text-gray-300' }}">
                                <span @class([
                                    'inline-flex items-center justify-center w-6 h-6 rounded-full',
                                    'bg-blue-600 text-white font-semibold' => $day['isToday'],
                                    'bg-blue-100 text-blue-800 font-medium' => ! $day['isToday'] && $hasEvents && $day['inMonth'],
                                    'text-gray-700' => ! $day['isToday'] && ! $hasEvents && $day['inMonth'],
                                    'text-gray-300' => ! $day['inMonth'],
                                ])>
                                    {{ $day['date']->day }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- Month view --}}
        @php $days = $this->calendarDays($events); @endphp

        {{-- Weekday header --}}
        <div class="hidden md:grid bg-gray-50 border-b border-gray-100"
            style="grid-template-columns: repeat(7, minmax(0, 1fr));">
            @foreach ($weekdayLabels as $label)
                <div class="px-2 py-2 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                    {{ $label }}
                </div>
            @endforeach
        </div>

        <div class="grid"
            style="grid-template-columns: 1fr;"
            x-data
            x-init="if (window.innerWidth >= 768) { $el.style.gridTemplateColumns = 'repeat(7, minmax(0, 1fr))'; }
                    window.addEventListener('resize', () => {
                        $el.style.gridTemplateColumns = window.innerWidth >= 768 ? 'repeat(7, minmax(0, 1fr))' : '1fr';
                    });">
            @foreach ($days as $day)
                <div wire:key="day-{{ $day['date']->format('Y-m-d') }}"
                    class="min-h-[6rem] md:min-h-[7rem] p-2 border-b border-r border-gray-100
                        {{ $day['inMonth'] ? 'bg-white' : 'bg-gray-50/60' }}">
                    <div class="flex items-center justify-between mb-1">
                        <span class="md:hidden text-[10px] uppercase tracking-wider text-gray-400">
                            {{ $day['date']->locale(app()->getLocale())->isoFormat('ddd') }}
                        </span>
                        <span @class([
                            'text-xs font-semibold ml-auto inline-flex items-center justify-center w-6 h-6 rounded-full',
                            'text-gray-300' => ! $day['inMonth'],
                            'text-gray-700' => $day['inMonth'] && ! $day['isToday'],
                            'bg-blue-600 text-white' => $day['isToday'],
                        ])>
                            {{ $day['date']->day }}
                        </span>
                    </div>

                    @foreach ($day['events']->take(3) as $event)
                        @php
                            $isCompetition = $event->event_category === 'competition';
                            $tileClasses = $isCompetition
                                ? 'bg-blue-50 text-blue-800 hover:bg-blue-100 border-blue-200'
                                : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border-emerald-200';
                        @endphp
                        <a href="{{ route('public.event.show', $event) }}"
                            class="block mb-1 px-2 py-1 rounded text-[11px] leading-tight font-medium truncate border transition-colors duration-150 {{ $tileClasses }}"
                            title="{{ $event->name }}">
                            {{ $event->name }}
                        </a>
                    @endforeach

                    @if ($day['events']->count() > 3)
                        <span class="block text-[11px] text-gray-500 px-2">
                            +{{ $day['events']->count() - 3 }} {{ __('public.events.calendar.more') }}
                        </span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
