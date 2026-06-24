<x-layout>
    <div class="previous-layout-classes">

        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('event_applications.titles.applications') }}</h1>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <button type="button" class="btn btn-info" onclick="window.print()">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M14 10V6a2 2 0 00-2-2H4a2 2 0 00-2 2v4a2 2 0 002 2h8a2 2 0 002-2zm-2-4v4H4V6h8z"/>
                    </svg>
                    <span class="ml-2">{{ __('common.export') }}</span>
                </button>
            </div>
        </div>

        <x-filter-form :post="route('admin.event-applications.index')">
            <x-forms.filter-input-select
                :label="__('event_applications.filters.filter_by_state')"
                name="status"
                :options="[
                    'draft' => __('event_applications.states.draft'),
                    'submitted' => __('event_applications.states.submitted'),
                    'in_validation' => __('event_applications.states.in_validation'),
                    'returned_for_correction' => __('event_applications.states.returned_for_correction'),
                    'approved' => __('event_applications.states.approved'),
                    'rejected' => __('event_applications.states.rejected'),
                    'published' => __('event_applications.states.published'),
                ]" />

            <x-forms.filter-input-select
                :label="__('event_applications.filters.filter_by_type')"
                name="application_type"
                :options="[
                    \App\Enums\EventApplicationTypeEnum::FederationInitiated->value => __('event_applications.types.federation_initiated'),
                    \App\Enums\EventApplicationTypeEnum::DirectSubmission->value => __('event_applications.types.direct_submission'),
                ]" />

            <x-forms.filter-input-select
                :label="__('event_applications.labels.event_type')"
                name="event_type"
                :options="[
                    'organization' => __('event_applications.event_types.organization'),
                    'competition' => __('event_applications.event_types.competition'),
                ]" />

            <x-forms.filter-input-select
                :label="__('event_applications.filters.filter_by_entity')"
                name="template_id"
                :options="$templates" />

            <x-forms.filter-input-date-range
                :label="__('event_applications.filters.date_range')"
                nameStart="date_from"
                nameEnd="date_to"
            />

            <x-forms.filter-input-text
                :label="__('common.search')"
                name="search"
                :value="request('search')"
                :placeholder="__('event_applications.filters.search_placeholder')"
            />
        </x-filter-form>

        <div class="card-no-padding">
            <div class="overflow-x-auto">
                <table class="table-auto w-full divide-y divide-slate-200">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-slate-200">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('event_applications.labels.entity') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('event_applications.labels.event_name') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('event_applications.table.type') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-center">{{ __('event_applications.table.state') }}</div>
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
                                    @if($application->template)
                                        <div class="text-xs text-slate-500">{{ $application->template->name }}</div>
                                    @endif
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium text-slate-800">{{ $application->event_name }}</div>
                                    <div class="text-xs text-slate-500">
                                        {{ __('event_applications.event_types.' . $application->event_type) }}
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    {{ __('event_applications.types.' . $application->application_type) }}
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="flex justify-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                              style="background-color: {{ $application->stateColor() }}20; color: {{ $application->stateColor() }};">
                                            {{ $application->stateName() }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    {{ $application->submitted_at?->format('d/m/Y H:i') ?? '-' }}
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="space-x-1 flex justify-end items-end">
                                        <x-dynamic-table-buttons type="show" :route="route('admin.event-applications.show', $application)" />
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

        <div class="mt-8">
            {{ $applications->links() }}
        </div>

    </div>
</x-layout>
