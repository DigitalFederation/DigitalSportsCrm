@section('title', __('Event Details'))

@if(isset($isDefaultFederation) && $isDefaultFederation)
{{-- Admin-style layout for main federation --}}
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-end sm:items-center mb-4">

            <!-- Actions -->
            <div class="flex gap-2 items-center">
                @can('manage-events')
                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                        <button @click="open = !open" id="dropdownDefaultButton"
                            class="btn btn-secondary" type="button">
                            <x-svg.person-lines class="w-4 h-4" />
                            <span>{{ __('events.enrollments') }}</span>
                            <svg class="w-2.5 h-2.5 ml-1" aria-hidden="true" fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m1 1 4 4 4-4" />
                            </svg>
                        </button>
                        <!-- Dropdown menu -->
                        <div x-cloak x-show="open" id="dropdown"
                            class="absolute z-50 bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95">
                            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200"
                                aria-labelledby="dropdownDefaultButton">
                                @if ($event->event_category === \App\Enums\EvtEventCategoryTypeEnum::competition->value)
                                    <li>
                                        <a href="{{ route('federation.evt-events.events.athlete-enrollment.index', $event->id) }}"
                                            class="items-center px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white flex gap-x-2">
                                            <span>{{ __('events.athletes_tab') }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('federation.evt-events.events.coach-enrollment.index', $event->id) }}"
                                            class="items-center px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white flex gap-x-2">
                                            <span>{{ __('events.coaches_tab') }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('federation.evt-events.events.officials-enrollment.index', $event->id) }}"
                                            class="items-center px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white flex gap-x-2">
                                            <span>{{ __('events.officials_tab') }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('federation.evt-events.events.referee-enrollment.index', $event->id) }}"
                                            class="items-center px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white flex gap-x-2">
                                            <span>{{ __('events.referees_tab') }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('federation.evt-events.events.staff-enrollment.index', $event->id) }}"
                                            class="items-center px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white flex gap-x-2">
                                            <span>{{ __('events.staff_members') }}</span>
                                        </a>
                                    </li>
                                @endif
                                @if ($event->event_category === \App\Enums\EvtEventCategoryTypeEnum::organization->value)
                                    <li>
                                        <a href="{{ route('federation.evt-events.events.individual-enrollment.index', $event->id) }}"
                                            class="items-center px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white flex gap-x-2">
                                            <span>{{ __('events.members') }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('federation.evt-events.events.staff-enrollment.index', $event->id) }}"
                                            class="items-center px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white flex gap-x-2">
                                            <span>{{ __('events.staff_members') }}</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <a href="{{ route('federation.evt-events.events.edit', $event->id) }}"
                        class="btn btn-primary">{{ __('events.form.update_event') }}
                    </a>

                    <!-- Delete Event -->
                    <form action="{{ route('federation.evt-events.events.destroy', $event->id) }}" method="POST"
                        onsubmit="return confirm('{{ __('common.are_you_sure') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-icon btn-outline-danger" title="{{ __('common.delete') }}">
                            <x-svg.trash class="w-4 h-4" />
                        </button>
                    </form>
                @endcan
            </div>

        </div>

        <!-- Event Details -->
        <div class="flex flex-col md:flex-row gap-x-4 mb-4">
            <x-evt_event.block-event-details :event="$event" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-{{ $event->event_category === \App\Enums\EvtEventCategoryTypeEnum::competition->value ? '3' : '2' }} gap-4 mb-4">
            <!-- Event Location -->
            <x-evt_event.block-event-location :event="$event" />

            @if($event->event_category === \App\Enums\EvtEventCategoryTypeEnum::competition->value)
                <!-- Technical Team -->
                <x-evt_event.block-technical-team :event="$event" />
            @endif

            <!-- Event LOC -->
            <x-evt_event.block-event-loc :event="$event" />
        </div>

        <!-- Event Attachment Management -->
        <livewire:event-attachments-table :event="$event" />

        <x-evt_event.block-event-enrollment-statistics :event="$event" />

        <!-- Event Organization Pricing -->
        <x-evt_event.block-event-organization-pricing :event="$event" />

    </div>
</x-layout>

@else
{{-- Standard federation layout --}}
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

            {{-- Section 1: Event Information (Poster + Details) --}}
            <section class="mb-8">
                <x-evt_event.block-event-details :event="$event" :is-organizer="$isOrganizer" />
            </section>

            {{-- Section 2: Location, Technical Team & Organizing Entity --}}
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-8">
                {{-- Event Location --}}
                <x-evt_event.block-event-location :event="$event" />

                {{-- Technical Team --}}
                <x-evt_event.block-technical-team :event="$event" />

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
                            <span class="font-bold">{{ __('events.registration_prices') }}</span>
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
                                                <span class="text-lg font-bold text-indigo-600">{{ number_format($price->price, 2) }}&euro;</span>
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

            {{-- Event Pricing Details (for organization events) --}}
            @if ($event->event_category === \App\Enums\EvtEventCategoryTypeEnum::organization->value)
                <section class="mb-8">
                    <x-evt_event.block-event-pricing-details :event="$event" />
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
                            :headers="[__('Name'), __('certifications.member_code')]">
                            @foreach ($referees as $referee)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-2 text-sm">{{ $referee->individual->full_name }}</td>
                                    <td class="py-2 text-sm text-right">
                                        <span class="font-medium text-slate-400">{{ __('Code:') }}</span>
                                        <span class="font-mono">{{ $referee->individual->member_code }}</span>
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

            {{-- Section 8: Registration Actions --}}
            <section class="flex flex-col md:flex-row gap-6 mb-8">
                <x-evt_event.block-event-registration :event="$event" :is-entity="$isEntity" :has-own-athlete-enrollments="$hasOwnAthleteEnrollments" />
            </section>

            <section class="flex flex-col md:flex-row gap-6">
                <x-evt_event.block-event-registration-organizer :event="$event" :is-organizer="$isOrganizer" :is-entity="$isEntity" />
            </section>

        </div>

    </div>
</x-layout-full>
@endif
