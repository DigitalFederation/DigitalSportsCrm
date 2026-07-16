@php
    $isCompetition = $event->event_category === \App\Enums\EvtEventCategoryTypeEnum::competition->value;
    $sport = $event->competition?->sport;
    $sportHeroRaw = $sport?->getFirstMediaUrl('hero-image');
    $posterRaw = $event->getFirstMediaUrl('poster');
    $heroBackground = $sportHeroRaw ?: $posterRaw ?: $event->hero_image;
    $heroBackground = preg_replace('#^https?:#', '', $heroBackground);
    $posterUrl = preg_replace('#^https?:#', '', $posterRaw);

    $location = collect([
        $event->venue_city,
        $event->venueCountry?->name,
    ])->filter()->join(', ');

    $startDate = $event->start_date;
    $endDate = $event->end_date;
    $sameDay = $startDate && $endDate && $startDate->isSameDay($endDate);

@endphp

<div class="min-h-screen bg-gray-50">
    {{-- Hero --}}
    <section class="relative bg-blue-900 text-white overflow-hidden">
        @if ($heroBackground)
            <div class="absolute inset-0"
                style="background-image: url('{{ $heroBackground }}'); background-size: cover; background-position: center; opacity: 0.45;">
            </div>
        @endif
        <div class="absolute inset-0"
            style="background: linear-gradient(180deg, rgba(15, 39, 89, 0.55) 0%, rgba(15, 39, 89, 0.85) 60%, rgba(15, 39, 89, 0.95) 100%);">
        </div>

        <div class="relative w-full px-4 sm:px-6 lg:px-8 pt-8 pb-20 sm:pt-10 sm:pb-28">
            <a href="{{ route('public.events') }}"
                class="inline-flex items-center text-sm text-blue-100 hover:text-white mb-10 sm:mb-12">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('public.events.detail.back') }}
            </a>

            <div class="max-w-4xl mx-auto text-center">
                {{-- Sport / Category --}}
                <p class="text-xs sm:text-sm font-bold tracking-[0.25em] uppercase text-blue-200 mb-3">
                    @if ($isCompetition)
                        {{ $sport?->translated_name ?? __('events.competition') }}
                    @elseif ($event->organization_type)
                        {{ \App\Enums\EvtEventOrganizationCategoryEnum::toString($event->organization_type) }}
                    @else
                        {{ __('public.events.types.organization') }}
                    @endif
                </p>

                {{-- Title --}}
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white mb-8 sm:mb-10">
                    {{ $event->name }}
                </h1>

                {{-- Meta row --}}
                <div class="flex flex-wrap items-center justify-center gap-x-4 sm:gap-x-6 gap-y-3 text-sm sm:text-base text-blue-50 font-medium">
                    @if ($location)
                        <span class="inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ $location }}
                        </span>
                    @endif

                    @if ($startDate)
                        <span class="text-blue-300/70 hidden sm:inline">·</span>
                        <span class="inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ $startDate->locale(app()->getLocale())->isoFormat('DD MMM YYYY') }}
                            @if ($endDate && ! $sameDay)
                                &ndash; {{ $endDate->locale(app()->getLocale())->isoFormat('DD MMM YYYY') }}
                            @endif
                        </span>
                    @endif

                    @if ($event->isRegistrationOpen())
                        <span class="text-blue-300/70 hidden sm:inline">·</span>
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-300/40 text-emerald-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('public.events.detail.registration_open') }}
                        </span>
                    @elseif ($event->isRegistrationClosed())
                        <span class="text-blue-300/70 hidden sm:inline">·</span>
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/20 border border-rose-300/40 text-rose-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            {{ __('public.events.detail.registration_closed') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Content --}}
    <article class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 space-y-12">

        {{-- Description (when present) --}}
        @if ($event->description)
            <section id="section-description" class="scroll-mt-20">
                <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                    {!! \Support\RichTextSanitizer::cleanWithLineBreaks($event->description) !!}
                </div>
            </section>
        @endif

        {{-- Details --}}
        <section id="section-details" class="scroll-mt-20">
            <div class="flex flex-col lg:flex-row gap-6 mb-6">
                {{-- Poster --}}
                @if ($posterUrl)
                    <div class="lg:w-1/4 w-full flex-shrink-0">
                        <div class="aspect-[3/4] w-full rounded-xl overflow-hidden shadow-lg bg-slate-900">
                            <img src="{{ $posterUrl }}" alt="{{ $event->name }}"
                                class="w-full h-full object-cover">
                        </div>
                    </div>
                @endif

                {{-- Fields --}}
                <div class="flex-1">
            <dl class="grid grid-cols-1 sm:grid-cols-2 {{ $posterUrl ? 'lg:grid-cols-2 xl:grid-cols-3' : 'lg:grid-cols-3' }} gap-4">
                @if ($isCompetition && $sport)
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-blue-700">
                            {{ __('events.form.sport') }}
                        </dt>
                        <dd class="mt-1.5 text-base font-bold text-blue-900">
                            {{ $sport->translated_name }}
                        </dd>
                    </div>
                @endif

                @if (! $isCompetition && $event->organization_type)
                    <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-emerald-700">
                            {{ __('events.form.organization_type') }}
                        </dt>
                        <dd class="mt-1.5 text-base font-bold text-emerald-900">
                            {{ \App\Enums\EvtEventOrganizationCategoryEnum::toString($event->organization_type) }}
                        </dd>
                    </div>
                @endif

                @if ($startDate)
                    <div class="bg-white border border-gray-200 rounded-xl p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                            {{ __('events.start_date') }}
                        </dt>
                        <dd class="mt-1.5 text-base font-bold text-gray-900">
                            {{ $startDate->format('d/m/Y') }}
                        </dd>
                    </div>
                @endif

                @if ($endDate)
                    <div class="bg-white border border-gray-200 rounded-xl p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                            {{ __('events.end_date') }}
                        </dt>
                        <dd class="mt-1.5 text-base font-bold text-gray-900">
                            {{ $endDate->format('d/m/Y') }}
                        </dd>
                    </div>
                @endif

                @if ($event->start_registration)
                    <div class="bg-white border border-gray-200 rounded-xl p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                            {{ __('events.form.registration_start') }}
                        </dt>
                        <dd class="mt-1.5 text-base font-bold text-gray-900">
                            {{ $event->start_registration->format('d/m/Y') }}
                        </dd>
                    </div>
                @endif

                @if ($event->end_registration)
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-amber-700">
                            {{ __('events.form.registration_end') }}
                        </dt>
                        <dd class="mt-1.5 text-base font-bold text-amber-900">
                            {{ $event->end_registration->format('d/m/Y') }}
                        </dd>
                    </div>
                @endif

                @if ($isCompetition && $event->competition?->cat_age)
                    <div class="bg-white border border-gray-200 rounded-xl p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                            {{ __('events.form.age_group') }}
                        </dt>
                        <dd class="mt-1.5 text-base font-bold text-gray-900">
                            {{ $event->competition->cat_age }}
                        </dd>
                    </div>
                @endif

                @if ($isCompetition && $event->competition?->number)
                    <div class="bg-white border border-gray-200 rounded-xl p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                            {{ __('events.form.competition_number') }}
                        </dt>
                        <dd class="mt-1.5 text-base font-bold text-gray-900 font-mono">
                            {{ $event->competition->number }}
                        </dd>
                    </div>
                @endif

                @if ($isCompetition && $event->competition?->cat_competition)
                    <div class="bg-white border border-gray-200 rounded-xl p-4">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                            {{ __('events.form.event_category') }}
                        </dt>
                        <dd class="mt-1.5 text-base font-bold text-gray-900">
                            {{ $event->competition->cat_competition }}
                        </dd>
                    </div>
                @endif
            </dl>
                </div>
            </div>

            @if ($event->notes)
                <div class="mt-6 bg-slate-50 border border-slate-200 rounded-xl p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">
                        {{ __('events.form.notes') }}
                    </p>
                    <div class="prose prose-sm max-w-none text-slate-700">
                        {!! \Support\RichTextSanitizer::cleanWithLineBreaks($event->notes) !!}
                    </div>
                </div>
            @endif

            @if ($event->external_url || $event->regulations_url)
                <div class="mt-6 flex flex-wrap gap-3">
                    @if ($event->external_url)
                        <a href="{{ $event->external_url }}" target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 !text-white font-semibold text-sm shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            {{ __('public.events.detail.more_info') }}
                        </a>
                    @endif

                    @if ($event->regulations_url)
                        <a href="{{ $event->regulations_url }}" target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-slate-700 hover:bg-slate-800 !text-white font-semibold text-sm shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            {{ __('events.form.regulations_url') }}
                        </a>
                    @endif
                </div>
            @endif
        </section>

        {{-- Location + Officials + Organizer in one row --}}
        <section>
            <div class="grid grid-cols-1 lg:grid-cols-{{ $isCompetition ? '3' : '2' }} gap-4 sm:gap-6">
                <x-evt_event.block-event-location :event="$event" />

                @if ($isCompetition)
                    <x-evt_event.block-technical-team :event="$event" />
                @endif

                <x-evt_event.block-event-loc :event="$event" />
            </div>
        </section>

        {{-- Fees --}}
        @if ($event->pricing->count() > 0)
            <section>
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
                    <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-semibold text-gray-900">{{ __('events.registration_fees') }}</span>
                        </div>
                        @if ($event->pricing->first()?->price_type)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-blue-50 border border-blue-200 text-xs font-semibold text-blue-700">
                                {{ \App\Enums\EvtEventFeeTypeEnum::toString(strtoupper($event->pricing->first()->price_type)) }}
                            </span>
                        @endif
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        {{ __('events.price') }}
                                    </th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        {{ __('common.description') }}
                                    </th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        {{ __('events.from_date') }}
                                    </th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        {{ __('events.closing_date') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach ($event->pricing as $pricing)
                                    <tr wire:key="pricing-{{ $pricing->id }}">
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <span class="inline-flex items-center px-3 py-1 rounded-md bg-blue-50 border border-blue-200 font-bold text-blue-700">
                                                {{ money($pricing->price) }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-sm text-gray-700">
                                            {{ $pricing->description ?? '--' }}
                                        </td>
                                        <td class="px-5 py-3 whitespace-nowrap text-sm text-gray-700">
                                            {{ $pricing->start_date?->format('d/m/Y') ?? '--' }}
                                        </td>
                                        <td class="px-5 py-3 whitespace-nowrap text-sm text-gray-700">
                                            {{ $pricing->end_date?->format('d/m/Y') ?? '--' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif

        @guest
            <section class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl border border-blue-200 p-8 text-center">
                <h3 class="text-xl font-bold text-gray-900 mb-2">
                    {{ __('public.events.detail.login_prompt') }}
                </h3>
                <a href="{{ route('login') }}"
                    class="inline-flex items-center justify-center px-6 py-3 mt-2 rounded-lg bg-blue-600 hover:bg-blue-700 !text-white text-sm font-semibold shadow-sm">
                    {{ __('public.events.detail.login_to_register') }}
                </a>
            </section>
        @endguest
    </article>
</div>
