@php
    /** @var \Domain\EvtEvents\Models\Event $event */
    $isCompetition = $event->event_category === 'competition';
    $startDate = $event->start_date;
    $endDate = $event->end_date;
    $sameDay = $startDate && $endDate && $startDate->isSameDay($endDate);
    $sameMonth = $startDate && $endDate && $startDate->month === $endDate->month && $startDate->year === $endDate->year;
    $locale = app()->getLocale();
    $location = collect([
        $event->venue_city,
        $event->venueCountry?->name,
    ])->filter()->join(', ');
    $categoryLabel = $isCompetition
        ? ($event->competition?->sport?->translated_name ?? __('events.competition'))
        : ($event->organization_type
            ? \App\Enums\EvtEventOrganizationCategoryEnum::toString($event->organization_type)
            : __('public.events.types.organization'));

    $posterRaw = $event->getFirstMediaUrl('poster')
        ?: ($event->featured_image ? asset('storage/' . $event->featured_image) : null)
        ?: asset($isCompetition ? 'img/placeholder_event_competition.png' : 'img/placeholder_event_organization.png');
    $posterUrl = preg_replace('#^https?:#', '', $posterRaw);
@endphp

<a href="{{ route('public.event.show', $event) }}"
    wire:key="event-row-{{ $event->id }}"
    class="group flex items-center gap-4 sm:gap-6 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-blue-200 transition-all duration-150 p-4 sm:p-5">

    {{-- Date column --}}
    <div class="hidden sm:flex flex-col items-center justify-center min-w-[8rem] sm:min-w-[9rem] text-center px-3 border-r border-gray-100">
        @if ($startDate)
            @if ($sameDay)
                <span class="text-xs uppercase tracking-wider text-gray-500">
                    {{ $startDate->locale($locale)->isoFormat('MMM') }}
                </span>
                <span class="text-3xl font-extrabold text-gray-900 leading-none mt-1">
                    {{ $startDate->format('d') }}
                </span>
            @else
                <span class="text-xs uppercase tracking-wider text-gray-500">
                    {{ $startDate->locale($locale)->isoFormat('MMM') }}
                    @if (! $sameMonth && $endDate)
                        &ndash; {{ $endDate->locale($locale)->isoFormat('MMM') }}
                    @endif
                </span>
                <span class="text-2xl font-extrabold text-gray-900 leading-none mt-1">
                    {{ $startDate->format('d') }}@if ($endDate) <span class="text-gray-400">&ndash;</span>{{ $endDate->format('d') }}@endif
                </span>
            @endif
        @else
            <span class="text-xs uppercase tracking-wider text-gray-400">{{ __('public.events.list.tba') }}</span>
        @endif
    </div>

    {{-- Poster --}}
    <div class="flex-shrink-0">
        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-lg bg-gray-100 border border-gray-200 overflow-hidden">
            <img src="{{ $posterUrl }}" alt="{{ $event->name }}"
                class="w-full h-full object-cover" loading="lazy">
        </div>
    </div>

    {{-- Title + meta --}}
    <div class="flex-1 min-w-0">
        {{-- Mobile date --}}
        <div class="sm:hidden flex items-center gap-2 text-xs uppercase tracking-wider text-gray-500 mb-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            @if ($startDate)
                {{ $startDate->locale($locale)->isoFormat('DD MMM') }}
                @if ($endDate && ! $sameDay)
                    &ndash; {{ $endDate->locale($locale)->isoFormat('DD MMM') }}
                @endif
            @endif
        </div>

        <p class="text-xs font-semibold uppercase tracking-wider {{ $isCompetition ? 'text-blue-600' : 'text-emerald-600' }} mb-1">
            {{ $categoryLabel }}
        </p>
        <h3 class="text-lg sm:text-xl font-bold text-gray-900 group-hover:text-blue-700 transition-colors duration-150 leading-tight">
            {{ $event->name }}
        </h3>
        @if ($location)
            <p class="mt-1 text-sm text-gray-500 inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ $location }}
            </p>
        @endif
    </div>

    {{-- View action --}}
    <div class="hidden md:flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-blue-700 group-hover:text-blue-900 flex-shrink-0">
        {{ __('public.events.list.view_event') }}
        <svg class="w-4 h-4 transition-transform duration-150 group-hover:translate-x-0.5" fill="none"
            stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
        </svg>
    </div>
</a>
