@php
    $competition = $event->competition;
    $posterUrl = $event->getFirstMediaUrl('poster');

    $now = now();
    $registrationOpen = $event->isRegistrationOpen();
    $registrationClosed = $event->isRegistrationClosed();
    $registrationNotStarted = $event->isRegistrationNotStarted();
@endphp

{{-- Event Information Card --}}
<div class="card w-full">
    {{-- Header --}}
    <div class="flex gap-x-2 items-center border-b border-gray-200 pb-3 mb-4">
        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="font-semibold text-slate-700">{{ __('events.event_information') }}</span>
    </div>

    {{-- Main Content: Poster + Fields --}}
    <div class="flex flex-col lg:flex-row gap-6 mb-6">

        {{-- Poster Image (25% on desktop) --}}
        @if($posterUrl)
            <div class="lg:w-1/4 w-full flex-shrink-0">
                <div class="aspect-[3/4] w-full rounded-xl overflow-hidden shadow-lg bg-slate-900 relative group">
                    <img
                        src="{{ $posterUrl }}"
                        alt="{{ $event->name }}"
                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                        onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'flex items-center justify-center w-full h-full bg-slate-800 text-slate-400\'><svg class=\'w-16 h-16\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\'></path></svg></div>';"
                    >
                </div>
            </div>
        @endif

        {{-- Fields (75% on desktop, or 100% if no poster) --}}
        <div class="flex-1">
            {{-- Nome do Evento e Badges de Estado --}}
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-6">
                <div>
                    {{-- 1. Nome do Evento --}}
                    <h2 class="text-2xl font-bold text-slate-800 leading-tight mb-3">
                        {{ $event->name ?? __('events.event') }}
                    </h2>

                    {{-- Badges de Estado --}}
                    <div class="flex flex-wrap gap-2">
                        @if($registrationClosed)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-100 text-red-700 text-sm font-medium rounded-full">
                                <x-heroicon-s-lock-closed class="w-4 h-4" />
                                {{ __('events.registration_closed_badge') }}
                            </span>
                        @elseif($registrationNotStarted)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 text-amber-700 text-sm font-medium rounded-full">
                                <x-heroicon-s-clock class="w-4 h-4" />
                                {{ __('events.registration_not_started') }}
                            </span>
                        @else
                            <x-tables.badge
                                :status="ucfirst($event->stateName())"
                                :color="$event->stateColor()"
                            />
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex flex-wrap gap-2">
                    @if(isset($isOrganizer) && $isOrganizer && Request::segment(1) === 'federation')
                        <a href="{{ route('federation.evt-events.events.edit', ['event'=> $event->id]) }}"
                           class="btn btn-secondary px-4 py-2 flex gap-x-2 items-center text-sm">
                            <x-heroicon-s-user-group class="w-4 h-4" />
                            <span>{{ __('events.manage_event_officials') }}</span>
                        </a>
                    @endif
                    @if($event->public_athlete_list && Auth::check() && optional(Auth::user()->group()->first())->code != 'ADMIN' && in_array(Request::segment(1), ['admin','federation','entity','individual','cmas'], true))
                        <a href="{{ route(Request::segment(1).'.evt-events.events.athlete-enrollment.public', ['event'=> $event->id]) }}"
                           target="_blank"
                           class="btn btn-primary px-4 py-2 flex gap-x-2 items-center text-sm">
                            <x-heroicon-s-users class="w-4 h-4" />
                            <span>{{ __('Athlete Public List') }}</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Event Details Grid - Campos Principais --}}
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">

                {{-- 2. Modalidade --}}
                @if(!empty($competition) && $competition->sport)
                    <div class="bg-indigo-50 rounded-lg p-3 border border-indigo-100">
                        <p class="text-xs text-indigo-600 uppercase tracking-wide mb-1">{{ __('events.form.sport') }}</p>
                        <p class="text-base font-semibold text-indigo-700 flex items-center gap-2">
                            <x-heroicon-s-trophy class="w-4 h-4" />
                            {{ $competition->sport->translated_name }}
                        </p>
                    </div>
                @endif

                {{-- 3. Data de Inicio --}}
                <div class="bg-slate-50 rounded-lg p-3">
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('events.start_date') }}</p>
                    <p class="text-base font-semibold text-slate-700 flex items-center gap-2">
                        <x-heroicon-o-calendar class="w-4 h-4 text-slate-400" />
                        {{ $event->start_date ? $event->start_date->format('d/m/Y') : '--' }}
                    </p>
                </div>

                {{-- 4. Data de Fim --}}
                <div class="bg-slate-50 rounded-lg p-3">
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('events.end_date') }}</p>
                    <p class="text-base font-semibold text-slate-700 flex items-center gap-2">
                        <x-heroicon-o-calendar class="w-4 h-4 text-slate-400" />
                        {{ $event->end_date ? $event->end_date->format('d/m/Y') : '--' }}
                    </p>
                </div>

                {{-- Organization Type (for organization events) --}}
                @if(!empty($event->organization_type))
                    <div class="bg-slate-50 rounded-lg p-3">
                        <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('events.form.organization_type') }}</p>
                        <p class="text-base font-semibold text-slate-700">
                            {{ \App\Enums\EvtEventOrganizationCategoryEnum::toString($event->organization_type) }}
                        </p>
                    </div>
                @endif

                {{-- 5. Escalao Etario --}}
                @if(!empty($competition) && $competition->cat_age)
                    <div class="bg-slate-50 rounded-lg p-3">
                        <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('events.form.age_group') }}</p>
                        <p class="text-base font-semibold text-slate-700">{{ $competition->cat_age }}</p>
                    </div>
                @endif

                {{-- 6. Data de Abertura de Inscricao --}}
                <div class="bg-slate-50 rounded-lg p-3">
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('events.form.registration_start') }}</p>
                    <p class="text-base font-semibold text-slate-700 flex items-center gap-2">
                        <x-heroicon-o-calendar class="w-4 h-4 text-emerald-500" />
                        {{ $event->start_registration ? $event->start_registration->format('d/m/Y') : '--' }}
                    </p>
                </div>

                {{-- 7. Estado do Evento - ja mostrado nos badges acima --}}

                {{-- 8. Data Limite de Inscricao - DESTAQUE --}}
                @php
                    $endRegistration = $event->end_registration_end_of_day;
                    $isDeadlineClose = $endRegistration && (int) $now->diffInDays($endRegistration) <= 7 && !$registrationClosed;
                @endphp
                <div class="{{ $isDeadlineClose ? 'bg-rose-50 border-2 border-rose-300' : ($registrationClosed ? 'bg-slate-100' : 'bg-amber-50 border border-amber-200') }} rounded-lg p-3">
                    <p class="text-xs {{ $isDeadlineClose ? 'text-rose-600' : ($registrationClosed ? 'text-slate-500' : 'text-amber-600') }} uppercase tracking-wide mb-1 font-semibold">
                        {{ __('events.form.registration_end') }}
                    </p>
                    <p class="text-base font-bold {{ $isDeadlineClose ? 'text-rose-700' : ($registrationClosed ? 'text-slate-500' : 'text-amber-700') }} flex items-center gap-2">
                        @if($isDeadlineClose)
                            <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-rose-500" />
                        @else
                            <x-heroicon-o-calendar class="w-4 h-4" />
                        @endif
                        {{ $event->end_registration ? $event->end_registration->format('d/m/Y') : '--' }}
                    </p>
                    @if($isDeadlineClose && $event->end_registration)
                        <p class="text-xs text-rose-600 mt-1 font-medium">
                            @php $daysLeft = (int) $now->diffInDays($endRegistration); @endphp
                            @if($daysLeft === 0)
                                {{ __('events.last_day_registration') }}
                            @else
                                {{ $daysLeft }} {{ __('events.days_remaining') }}
                            @endif
                        </p>
                    @endif
                </div>

                {{-- 9. Numero da Competicao --}}
                @if(!empty($competition) && $competition->number)
                    <div class="bg-slate-50 rounded-lg p-3">
                        <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('events.form.competition_number') }}</p>
                        <p class="text-base font-semibold text-slate-700 font-mono">{{ $competition->number }}</p>
                    </div>
                @endif

                {{-- Event Category --}}
                @if(!empty($competition) && $competition->cat_competition)
                    <div class="bg-slate-50 rounded-lg p-3">
                        <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('events.form.event_category') }}</p>
                        <p class="text-base font-semibold text-slate-700">{{ $competition->cat_competition }}</p>
                    </div>
                @endif

                {{-- Visibility --}}
                <div class="bg-slate-50 rounded-lg p-3">
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('events.form.visibility') }}</p>
                    @if($event->is_visible)
                        <span class="inline-flex items-center gap-1 text-sm font-semibold text-emerald-600">
                            <x-heroicon-s-eye class="w-4 h-4" />
                            {{ __('common.visible') }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-sm font-semibold text-red-500">
                            <x-heroicon-s-eye-slash class="w-4 h-4" />
                            {{ __('common.hidden') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    {{-- End of Main Content (Poster + Fields) --}}
    </div>

    {{-- 10. Notas (full width) --}}
    @if($event->notes)
        <div class="border-t border-slate-100 pt-4 mb-4">
            <p class="text-xs text-slate-500 uppercase tracking-wide mb-2">{{ __('events.form.notes') }}</p>
            <div class="prose prose-sm prose-slate max-w-none bg-slate-50 rounded-lg p-4">
                {!! $event->notes !!}
            </div>
        </div>
    @endif

    {{-- External Link Button (full width) --}}
    @if($event->external_url)
        <div class="border-t border-slate-100 pt-4">
            <a href="{{ $event->external_url }}"
               target="_blank"
               rel="noopener noreferrer"
               class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition-colors duration-200">
                <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                <span class="font-medium">{{ __('events.form.external_url') }}</span>
            </a>
        </div>
    @endif
</div>
