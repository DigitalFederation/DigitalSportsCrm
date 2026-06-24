<x-layout>
    <div class="previous-layout-classes">

        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('event_applications.titles.templates') }}</h1>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('federation.application-templates.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="hidden xs:block ml-2">{{ __('event_applications.actions.create_template') }}</span>
                </a>
            </div>
        </div>

        <x-filter-form :post="route('federation.application-templates.index')">
            <x-forms.filter-input-select
                :label="__('event_applications.labels.event_type')"
                name="event_type"
                :value="request('event_type')">
                <option value="">{{ __('event_applications.filters.all_types') }}</option>
                <option value="organization" {{ request('event_type') === 'organization' ? 'selected' : '' }}>
                    {{ __('event_applications.event_types.organization') }}
                </option>
                <option value="competition" {{ request('event_type') === 'competition' ? 'selected' : '' }}>
                    {{ __('event_applications.event_types.competition') }}
                </option>
            </x-forms.filter-input-select>

            <x-forms.filter-input-select
                :label="__('common.status')"
                name="state"
                :value="request('state')">
                <option value="">{{ __('common.all') }}</option>
                @foreach(['draft', 'open', 'closed', 'archived'] as $state)
                    <option value="{{ $state }}" {{ request('state') === $state ? 'selected' : '' }}>
                        {{ __('event_applications.template_states.' . $state) }}
                    </option>
                @endforeach
            </x-forms.filter-input-select>

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
                                <div class="font-semibold text-left">{{ __('event_applications.labels.template_name') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('event_applications.labels.event_type') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">{{ __('event_applications.labels.submission_deadline') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-center">{{ __('common.status') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-center">{{ __('event_applications.table.applications') }}</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-right">{{ __('common.actions') }}</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200">
                        @forelse($templates as $application_template)
                            <tr class="table-row">
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium text-slate-800">{{ $application_template->name }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    {{ __('event_applications.event_types.' . $application_template->event_type) }}
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    {{ $application_template->submission_end_date?->format('d/m/Y') ?? '-' }}
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="flex justify-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $application_template->state_color }}-100 text-{{ $application_template->state_color }}-800">
                                            {{ __('event_applications.template_states.' . $application_template->state) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                            {{ $application_template->applications_count }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="space-x-1 flex justify-end items-end">
                                        <x-dynamic-table-buttons type="show" :route="route('federation.application-templates.show', $application_template)" />
                                        <x-dynamic-table-buttons type="edit" :route="route('federation.application-templates.edit', $application_template)" />

                                        @if($application_template->applications_count === 0)
                                            <x-dynamic-table-buttons type="delete" :route="route('federation.application-templates.destroy', $application_template)" method="DELETE" />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-2 first:pl-5 last:pr-5 py-12 text-center">
                                    <div class="text-slate-500">{{ __('event_applications.messages.no_templates') }}</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8">
            {{ $templates->links() }}
        </div>

    </div>
</x-layout>
