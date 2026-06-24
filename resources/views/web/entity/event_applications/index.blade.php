@section('title', __('event_applications.titles.my_applications'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('event_applications.titles.my_applications') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-secondary" href="{{ route('entity.event-applications.available-templates') }}">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
                    </svg>
                    <span class="hidden xs:block ml-2">{{ __('event_applications.titles.available_templates') }}</span>
                </a>

                <a class="btn btn-primary" href="{{ route('entity.event-applications.create-direct') }}">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="hidden xs:block ml-2">{{ __('event_applications.actions.create_application') }}</span>
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <x-filter-form :post="route('entity.event-applications.index')">
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

        <!-- Applications Table -->
        <div class="card-no-padding">
            <div class="overflow-x-auto">
                <table class="table-auto w-full divide-y divide-slate-200">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-slate-200">
                        <tr>
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
                                    @include('web.entity.event_applications.components.status-badge', ['application' => $application])
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    {{ $application->submitted_at ? $application->submitted_at->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="space-x-1 flex justify-end items-end">
                                        <x-dynamic-table-buttons
                                            type="show"
                                            :route="route('entity.event-applications.show', $application)" />

                                        @if($application->state->canEdit())
                                            <x-dynamic-table-buttons
                                                type="edit"
                                                :route="route('entity.event-applications.edit', $application)" />
                                        @endif

                                        @if($application->state->canDelete())
                                            <x-dynamic-table-buttons
                                                type="delete"
                                                :route="route('entity.event-applications.destroy', $application)"
                                                method="DELETE" />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-2 first:pl-5 last:pr-5 py-12 text-center">
                                    <div class="text-slate-500">{{ __('event_applications.messages.no_applications') }}</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($applications->hasPages())
            <div class="mt-8">
                {{ $applications->links() }}
            </div>
        @endif

    </div>
</x-layout>
