@php
    /** @var \Domain\EvtEvents\Models\Event $event */
    $isCompetition = $event->event_category === 'competition';
    $startDate = $event->start_date;
    $endDate = $event->end_date;
    $sameDay = $startDate && $endDate && $startDate->isSameDay($endDate);
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
    wire:key="event-grid-{{ $event->id }}"
    class="group flex flex-col bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-blue-200 transition-all duration-200 p-4 sm:p-5 h-full">

    {{-- Top row: poster icon + category tag --}}
    <div class="flex items-center gap-3 mb-3">
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-gray-100 border border-gray-200 overflow-hidden flex-shrink-0">
            <img src="{{ $posterUrl }}" alt="{{ $event->name }}"
                class="w-full h-full object-cover" loading="lazy">
        </div>
        <span @class([
            'inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wider truncate',
            'bg-blue-100 text-blue-800' => $isCompetition,
            'bg-emerald-100 text-emerald-800' => ! $isCompetition,
        ])>
            {{ $categoryLabel }}
        </span>
    </div>

    {{-- Date --}}
    @if ($startDate)
        <p class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-1">
            {{ $startDate->locale($locale)->isoFormat('DD MMM') }}
            @if ($endDate && ! $sameDay)
                <span class="text-gray-300">&ndash;</span>
                {{ $endDate->locale($locale)->isoFormat('DD MMM YYYY') }}
            @else
                {{ $startDate->format('Y') }}
            @endif
        </p>
    @endif

    {{-- Title --}}
    <h3 class="text-base sm:text-lg font-bold text-gray-900 group-hover:text-blue-700 transition-colors duration-150 leading-tight line-clamp-2 mb-2">
        {{ $event->name }}
    </h3>

    {{-- Location --}}
    @if ($location)
        <p class="text-sm text-gray-500 inline-flex items-center gap-1.5 mb-3">
            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="truncate">{{ $location }}</span>
        </p>
    @endif

    {{-- CTA --}}
    <div class="mt-auto pt-3 border-t border-gray-100 flex items-center justify-end text-xs font-semibold uppercase tracking-wider text-blue-700 group-hover:text-blue-900">
        {{ __('public.events.list.view_event') }}
        <svg class="w-3.5 h-3.5 ml-1 transition-transform duration-150 group-hover:translate-x-0.5" fill="none"
            stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
        </svg>
    </div>
</a>
