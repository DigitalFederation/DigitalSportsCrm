@section('title', __('event_applications.titles.my_applications'))
<x-layout>
    <div class="space-y-6">

        {{-- Header Card --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-6 sm:px-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-100">
                                <x-heroicon-s-document-text class="w-6 h-6 text-primary-600" />
                            </div>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">{{ __('event_applications.titles.my_applications') }}</h1>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('federation.event-applications.available-templates') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg font-medium text-sm text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring focus:ring-gray-200/50 transition-colors duration-150">
                            <x-heroicon-m-arrow-right-circle class="w-4 h-4" />
                            {{ __('event_applications.titles.available_templates') }}
                        </a>

                        <a href="{{ route('federation.event-applications.create-direct') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-lg shadow-sm hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                            <x-heroicon-m-plus class="w-4 h-4" />
                            {{ __('event_applications.actions.create_application') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Form --}}
        <x-filter-form :post="route('federation.event-applications.index')">
            <x-forms.filter-input-select
                :label="__('event_applications.labels.current_state')"
                name="filter_state"
                :options="$states" />
            <x-forms.filter-input-select
                :label="__('event_applications.labels.event_type')"
                name="filter_type"
                :options="$eventTypes" />
            <x-forms.filter-input-text
                :label="__('event_applications.labels.event_name')"
                name="filter_name" />
        </x-filter-form>

        {{-- Applications Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-auto w-full divide-y divide-slate-200">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-slate-200">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('event_applications.labels.applicant') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('event_applications.table.event_name') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('event_applications.table.type') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('event_applications.labels.start_date') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('event_applications.table.state') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('event_applications.table.submitted_at') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-right">{{ __('common.actions') }}</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200">
                        @forelse($applications as $application)
                            <tr class="table-row">
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium text-slate-800">
                                        {{ $application->entity?->name ?? '-' }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        @if($application->entity_type === 'entity')
                                            {{ __('event_applications.labels.entity_type_entity') }}
                                        @else
                                            {{ __('event_applications.labels.entity_type_federation') }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium text-slate-800">{{ $application->event_name }}</div>
                                    @if($application->template)
                                        <div class="text-xs text-slate-500">{{ $application->template->name }}</div>
                                    @endif
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <span class="text-slate-600">
                                        {{ __('event_applications.event_types.' . $application->event_type) }}
                                    </span>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    {{ $application->start_date->format('d/m/Y') }}
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          style="background-color: {{ $application->stateColor() }}20; color: {{ $application->stateColor() }};">
                                        {{ $application->stateName() }}
                                    </span>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    {{ $application->submitted_at ? $application->submitted_at->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="space-x-1 flex justify-end items-end">
                                        <x-dynamic-table-buttons
                                            type="show"
                                            :route="route('federation.event-applications.show', $application)" />

                                        @if($application->state->canEdit())
                                            <x-dynamic-table-buttons
                                                type="edit"
                                                :route="route('federation.event-applications.edit', $application)" />
                                        @endif

                                        @if($application->state->canDelete())
                                            <x-dynamic-table-buttons
                                                type="delete"
                                                :route="route('federation.event-applications.destroy', $application)"
                                                method="DELETE" />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-2 first:pl-5 last:pr-5 py-12 text-center">
                                    <div class="text-slate-500">{{ __('event_applications.messages.no_applications') }}</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($applications->hasPages())
            <div class="mt-2">
                {{ $applications->links() }}
            </div>
        @endif

    </div>
</x-layout>
