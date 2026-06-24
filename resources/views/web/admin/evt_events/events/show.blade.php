@section('title', __('Event Details'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-end sm:items-center mb-4">

            <!-- Actions -->

            <div class="flex gap-2 items-center">
                @if ($event->event_category === \App\Enums\EvtEventCategoryTypeEnum::competition->value)
                    <a href="{{ route('admin.evt-events.events.reports', $event->id) }}" class="btn btn-secondary">
                        <x-heroicon-o-document-text class="w-4 h-4" />
                        <span>{{ __('events.reports_and_referees') }}</span>
                    </a>
                @endif

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
                                    <a href="{{ route('admin.evt-events.events.enrollments.athlete.index', $event->id) }}"
                                        target="_blank"
                                        class="items-center px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white flex gap-x-2">
                                        <span>{{ __('events.athletes_tab') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.evt-events.events.enrollments.coach.index', $event->id) }}"
                                        target="_blank"
                                        class="items-center px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white flex gap-x-2">
                                        <span>{{ __('events.coaches_tab') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.evt-events.events.officials-enrollment.index', $event->id) }}"
                                        target="_blank"
                                        class="items-center px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white flex gap-x-2">
                                        <span>{{ __('events.officials_tab') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.evt-events.events.referee-enrollment.index', $event->id) }}"
                                        target="_blank"
                                        class="items-center px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white flex gap-x-2">
                                        <span>{{ __('events.referees_tab') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.evt-events.events.staff-enrollment.index', $event->id) }}"
                                        target="_blank"
                                        class="items-center px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white flex gap-x-2">
                                        <span>{{ __('events.staff_members') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if ($event->event_category === \App\Enums\EvtEventCategoryTypeEnum::organization->value)
                                <li>
                                    <a href="{{ route('admin.evt-events.events.enrollments.individual.index', $event->id) }}"
                                        target="_blank"
                                        class="items-center px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white flex gap-x-2">
                                        <span>{{ __('events.members') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.evt-events.events.staff-enrollment.index', $event->id) }}"
                                        target="_blank"
                                        class="items-center px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white flex gap-x-2">
                                        <span>{{ __('events.staff_members') }}</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>


                <a href="{{ route('admin.evt-events.events.edit', $event->id) }}"
                    class="btn btn-primary">{{ __('events.form.update_event') }}
                </a>

                <!-- Delete Event -->
                <form action="{{ route('admin.evt-events.events.destroy', $event->id) }}" method="POST"
                    onsubmit="return confirm('{{ __('common.are_you_sure') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-icon btn-outline-danger" title="{{ __('common.delete') }}">
                        <x-svg.trash class="w-4 h-4" />
                    </button>
                </form>
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
