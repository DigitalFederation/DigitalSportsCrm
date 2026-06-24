@section('title', __('events.competitions'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <x-layout.page-header
            title="{{ __('events.competitions') }}"
        ></x-layout.page-header>

        <!-- Navigation Tabs -->
        <div class="mb-6">
            <div class="flex gap-2">
                <a href="{{ route('entity.evt-events.competitions.index') }}"
                   class="btn btn-primary">
                    {{ __('events.competitions') }}
                </a>
                <a href="{{ route('entity.evt-events.events.index') }}"
                   class="btn btn-secondary">
                    {{ __('events.events') }}
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6 card">
            <form action="{{ route(Request::segment(1).'.evt-events.competitions.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label for="sport_id" class="block text-sm font-medium text-gray-700">{{ __('events.sport') }}</label>
                        <select id="sport_id" name="sport_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">{{ __('events.all_sports') }}</option>
                            @foreach($sports as $sport)
                                <option value="{{ $sport->id }}" {{ request('sport_id') == $sport->id ? 'selected' : '' }}>
                                    {{ $sport->translated_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="date_range" class="block text-sm font-medium text-gray-700">{{ __('events.date_range') }}</label>
                        <select id="date_range" name="date_range"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">{{ __('events.all_dates') }}</option>
                            <option value="upcoming" {{ request('date_range') == 'upcoming' ? 'selected' : '' }}>{{ __('events.upcoming') }}</option>
                            <option value="past" {{ request('date_range') == 'past' ? 'selected' : '' }}>{{ __('events.past') }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">{{ __('events.search') }}</label>
                        <input type="text" id="search" name="search" value="{{ request('search') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                               placeholder="{{ __('events.search_events_placeholder') }}">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full btn-info btn">
                            {{ __('events.apply_filters') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table -->
        @if($competitions->count() > 0)
            <x-dynamic-table
                :headers="[
                    __('events.sport'),
                    __('events.event_name'),
                    __('events.start_date') . ' / ' . __('events.end_date'),
                    __('events.registration_deadline'),
                    __('events.status_label'),
                    ['text' => '', 'alignment' => 'text-right']
                ]">
                @foreach($competitions as $event)
                    <tr class="hover:bg-gray-50" wire:key="competition-{{ $event->id }}">
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                @if($event->competition && $event->competition->sport)
                                    <span class="font-medium text-slate-700">{{ $event->competition->sport->translated_name }}</span>
                                @else
                                    <span class="text-slate-400">---</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3">
                            <a href="{{ route(Request::segment(1).'.evt-events.events.show', $event->id) }}"
                               class="font-medium text-indigo-600 hover:text-indigo-800 hover:underline">
                                {{ $event->name }}
                            </a>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="text-slate-600">
                                @if($event->start_date)
                                    {{ $event->start_date->format('d/m/Y') }}
                                @else
                                    ---
                                @endif
                                <span class="text-slate-400 mx-1">/</span>
                                @if($event->end_date)
                                    {{ $event->end_date->format('d/m/Y') }}
                                @else
                                    ---
                                @endif
                            </div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="text-slate-600">
                                @if($event->end_registration)
                                    {{ $event->end_registration->format('d/m/Y') }}
                                @else
                                    <span class="text-slate-400">---</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <x-tables.badge :status="$event->stateName()" :color="$event->stateColor()" />
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-right">
                            <a href="{{ route(Request::segment(1).'.evt-events.events.show', $event->id) }}" class="btn btn-secondary btn-sm">{{ __('common.enter') }}</a>
                        </td>
                    </tr>
                @endforeach
            </x-dynamic-table>
        @else
            <x-utility.no-data />
        @endif

        <!-- Pagination -->
        <div class="mt-8">
            {{ $competitions->links() }}
        </div>

    </div>
</x-layout>
