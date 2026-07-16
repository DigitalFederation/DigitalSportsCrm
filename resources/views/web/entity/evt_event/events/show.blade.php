@section('title', __('events.event_details'))
<x-layout-full>

    <div class="relative">

        {{-- Page Hero Header --}}
        <div
            class="h-64 md:h-72 flex items-end bg-cover bg-center relative overflow-hidden"
            style="background-image: url('{{ $event->heroImage }}');">
            {{-- Gradient Overlay --}}
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/50 to-transparent"></div>

            {{-- Header Content --}}
            <div class="relative w-full page-wrapper pb-8">
                <nav class="mb-4">
                    <a href="{{ route(Request::segment(1).'.evt-events.events.index') }}"
                       class="inline-flex items-center gap-2 text-white/80 hover:text-white text-sm transition-colors">
                        <x-heroicon-o-arrow-left class="w-4 h-4" />
                        {{ __('events.back_to_events') }}
                    </a>
                </nav>
                <h1 class="text-3xl md:text-4xl text-white font-bold tracking-tight drop-shadow-lg">
                    {{ $event->name ?? __('events.event_details') }}
                </h1>
            </div>
        </div>

        <div class="page-wrapper py-8">

            {{-- Section 1: Event Information --}}
            <section class="mb-8">
                <x-evt_event.block-event-details :event="$event" />
            </section>

            {{-- Section 2: Location, Technical Team & Organizing Entity --}}
            <section class="grid grid-cols-1 lg:grid-cols-{{ $event->event_category === \App\Enums\EvtEventCategoryTypeEnum::competition->value ? '3' : '2' }} gap-4 mb-8">
                {{-- Event Location --}}
                <x-evt_event.block-event-location :event="$event" />

                @if($event->event_category === \App\Enums\EvtEventCategoryTypeEnum::competition->value)
                    {{-- Technical Team --}}
                    <x-evt_event.block-technical-team :event="$event" />
                @endif

                {{-- Organizing Entity --}}
                <x-evt_event.block-event-loc :event="$event" />
            </section>

            {{-- Section 4: Event Documents --}}
            @if(!empty($attachments) && $attachments->count() > 0)
                <section class="mb-8">
                    <x-evt_event.block-event-attachments :event="$event" :attachments="$attachments" />
                </section>
            @endif

            {{-- Section 5: Registration Prices --}}
            @if($event->pricing && $event->pricing->count() > 0)
                <section class="mb-8">
                    <div class="card">
                        <div class="flex gap-x-2 items-center border-b border-gray-300 pb-2 mb-4">
                            <x-svg.currency-euro class="w-6 h-6 text-slate-600" />
                            <span class="font-bold">{{ __('events.registration_fees') }}</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="border-b border-slate-200">
                                        <th class="py-3 px-4 text-left text-sm font-semibold text-slate-700">{{ __('events.price') }}</th>
                                        <th class="py-3 px-4 text-left text-sm font-semibold text-slate-700">{{ __('events.price_type') }}</th>
                                        <th class="py-3 px-4 text-left text-sm font-semibold text-slate-700">{{ __('events.valid_period') }}</th>
                                        <th class="py-3 px-4 text-left text-sm font-semibold text-slate-700">{{ __('common.description') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($event->pricing as $price)
                                        <tr class="hover:bg-slate-50">
                                            <td class="py-3 px-4">
                                                <span class="text-lg font-bold text-indigo-600">{{ money($price->price) }}</span>
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full">
                                                    {{ \App\Enums\EvtEventFeeTypeEnum::toString($price->price_type) }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4">
                                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                                    <x-heroicon-o-calendar class="w-4 h-4 text-slate-400" />
                                                    <span>{{ date('d/m/Y', strtotime($price->start_date)) }} - {{ date('d/m/Y', strtotime($price->end_date)) }}</span>
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 text-sm text-slate-500">
                                                {{ $price->description ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            @endif


            {{-- Section 6: Referees List --}}
            @if(!empty($referees) && $referees->count() > 0)
                <section class="mb-8">
                    <div class="card">
                        <div class="flex justify-between items-center border-b border-gray-300 pb-2 mb-4">
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-user-group class="w-6 h-6 text-slate-600" />
                                <span class="font-bold">{{ __('events.referees') }}</span>
                            </div>
                            <span class="text-sm text-slate-500">{{ $referees->count() }} {{ __('events.total_referees') }}</span>
                        </div>

                        <x-dynamic-table
                            :displayable-headers="false"
                            :headers="['Name', __('certifications.member_code')]">
                            @foreach($referees as $referee)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-2 text-sm">{{ $referee->individual?->full_name }}</td>
                                    <td class="py-2 text-sm text-right">
                                        <span class="font-medium text-slate-400">{{ __('events.code') }}:</span>
                                        <span class="font-mono">{{ $referee->individual?->member_code }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </x-dynamic-table>
                    </div>
                </section>
            @endif

            {{-- Section 7: Individual Enrollments --}}
            <x-evt_event.block-event-individual-enrollments :event="$event"
                                                            :federationIndividualEnrollments="$federationIndividualEnrollments" />

            {{-- Section 8: Event Registration (for entities) --}}
            <section class="mb-8">
                <x-evt_event.block-event-registration :event="$event" :is-entity="$isEntity" :has-own-athlete-enrollments="$hasOwnAthleteEnrollments" />
            </section>

            {{-- Section 9: Organizer Registration --}}
            <section class="mb-8">
                <x-evt_event.block-event-registration-organizer :event="$event" :is-organizer="$isOrganizer" :is-entity="$isEntity" />
            </section>

        </div>

    </div>
</x-layout-full>
