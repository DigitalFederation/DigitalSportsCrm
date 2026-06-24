@section('title', __('event_applications.titles.available_templates'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('event_applications.titles.available_templates') }}</h1>
                <p class="text-sm text-slate-600 mt-2">
                    {{ __('event_applications.instructions.browse_available_events') }}
                </p>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-secondary" href="{{ route('entity.event-applications.index') }}">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/>
                        <path d="M3.293 14.707a1 1 0 010-1.414L6.586 10 3.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/>
                    </svg>
                    <span class="ml-2">{{ __('event_applications.actions.my_applications') }}</span>
                </a>

                <a class="btn btn-primary" href="{{ route('entity.event-applications.create-direct') }}">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="ml-2">{{ __('event_applications.actions.direct_submission') }}</span>
                </a>
            </div>
        </div>

        <!-- Filters -->
        <x-filter-form :post="route('entity.event-applications.available-templates')">
            <x-forms.filter-input-select
                :label="__('event_applications.labels.event_type')"
                name="filter[event_type]"
                :value="request('filter.event_type')">
                <option value="">{{ __('event_applications.filters.all_types') }}</option>
                <option value="organization" {{ request('filter.event_type') === 'organization' ? 'selected' : '' }}>
                    {{ __('event_applications.event_types.organization') }}
                </option>
                <option value="competition" {{ request('filter.event_type') === 'competition' ? 'selected' : '' }}>
                    {{ __('event_applications.event_types.competition') }}
                </option>
            </x-forms.filter-input-select>

            <x-forms.filter-input-select
                :label="__('event_applications.labels.sport')"
                name="filter[sport_id]"
                :value="request('filter.sport_id')">
                <option value="">{{ __('common.all') }}</option>
                @foreach($sports as $sport)
                    <option value="{{ $sport->id }}" {{ request('filter.sport_id') == $sport->id ? 'selected' : '' }}>
                        {{ $sport->translated_name }}
                    </option>
                @endforeach
            </x-forms.filter-input-select>
        </x-filter-form>

        @if($templates->isEmpty())
            <!-- Empty State -->
            <div class="card">
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">
                        {{ __('event_applications.messages.no_available_templates') }}
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ __('event_applications.help.direct_submission') }}
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('entity.event-applications.create-direct') }}" class="btn btn-primary">
                            {{ __('event_applications.actions.create_application') }}
                        </a>
                    </div>
                </div>
            </div>
        @else
            <!-- Templates Table -->
            <div class="card-no-padding">
                <div class="overflow-x-auto">
                    <table class="table-auto w-full divide-y divide-slate-200">
                        <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-slate-200">
                            <tr>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('event_applications.labels.event_name') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('event_applications.labels.event_type') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('event_applications.labels.period') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-center">{{ __('event_applications.labels.application_status') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-right">{{ __('common.actions') }}</div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-200">
                            @foreach($templates as $template)
                                <tr class="table-row">
                                    <td class="px-2 first:pl-5 last:pr-5 py-3">
                                        <div class="font-medium text-slate-800">{{ $template->name }}</div>
                                        @if($template->sport)
                                            <div class="text-xs text-slate-600">{{ $template->sport->translated_name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        {{ __('event_applications.event_types.' . $template->event_type) }}
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        @if($template->event_start_date && $template->event_end_date)
                                            {{ $template->event_start_date->format('d/m/Y') }} - {{ $template->event_end_date->format('d/m/Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="flex justify-center">
                                            @if($template->hasEntityApplied && isset($template->existingApplication))
                                                @include('web.entity.event-applications.components.status-badge', ['application' => $template->existingApplication])
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                                    {{ __('event_applications.statuses.not_applied') }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="flex justify-end gap-2">
                                            @if($template->hasEntityApplied && isset($template->existingApplication))
                                                <a href="{{ route('entity.event-applications.show', $template->existingApplication) }}"
                                                   class="btn btn-sm btn-secondary">
                                                    {{ __('event_applications.actions.view_application') }}
                                                </a>
                                            @elseif($template->isOpen())
                                                <a href="{{ route('entity.event-applications.create-from-template', $template) }}"
                                                   class="btn btn-sm btn-primary">
                                                    {{ __('event_applications.actions.view_details') }}
                                                </a>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                                    {{ __('event_applications.template_states.closed') }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</x-layout>
