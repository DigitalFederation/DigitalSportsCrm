@section('title', __('events.referee_history'))

<x-layout>
    <div class="previous-layout-classes">
        <div class="mb-8 flex justify-between items-center">
            <h1 class="page-first-title">{{ __('events.referee_history') }}</h1>
        </div>

        <x-information-box
            :title="__('events.referee_history')"
            :body="__('events.referee_history_description')" />

        @if($refereeEnrollments->count() > 0)
            <div class="card">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('events.start_date') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('events.event') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('events.sport') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('events.event_category') }}
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('events.functions_performed') }}
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('events.competition_days') }}
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('events.number_of_games') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($refereeEnrollments as $enrollment)
                                @php
                                    $assignment = $enrollment->refereeFunctionAssignments->first();
                                    $sportId = $enrollment->event->sport_id ?? $enrollment->event->competition?->sport_id;
                                    $isGameSport = in_array($sportId, [4, 5]);
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $enrollment->event->start_date ? $enrollment->event->start_date->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $enrollment->event->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $enrollment->event->location }}</div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $enrollment->event->sport->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($enrollment->event->competition?->cat_competition)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ $enrollment->event->competition->cat_competition }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        @if($enrollment->refereeFunctionAssignments->count() > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($enrollment->refereeFunctionAssignments as $funcAssignment)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ $funcAssignment->refereeFunction->function_name ?? $funcAssignment->function_text }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400">{{ __('events.no_functions_assigned') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                        {{ $assignment?->competition_days ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                        @if($isGameSport)
                                            {{ $assignment?->number_of_games ?? '-' }}
                                        @else
                                            <span class="text-gray-300">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="card text-center py-12">
                <div class="text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('events.no_referee_history') }}</h3>
                    <p class="mt-2 text-sm text-gray-500">{{ __('events.no_referee_history_description') }}</p>
                </div>
            </div>
        @endif
    </div>
</x-layout>
