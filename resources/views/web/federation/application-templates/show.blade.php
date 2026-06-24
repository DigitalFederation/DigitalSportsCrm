<x-layout>
    <div class="previous-layout-classes">

        <div class="space-y-6 mt-5">

            {{-- Slim Header --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-6 sm:px-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        {{-- Left: Template Info --}}
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-100">
                                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $application_template->state_color }}-100 text-{{ $application_template->state_color }}-800">
                                    {{ __('event_applications.template_states.' . $application_template->state) }}
                                </span>
                            </div>
                            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">{{ $application_template->name }}</h1>
                        </div>

                        {{-- Right: Edit CTA --}}
                        <div class="flex-shrink-0">
                            <a href="{{ route($routeNamespace . '.application-templates.edit', $application_template) }}"
                               class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white rounded-xl font-medium text-sm shadow-sm hover:bg-primary-700 focus:outline-none focus:ring focus:ring-primary-light/30 transition-colors duration-150">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span class="text-white">{{ __('common.edit') }}</span>
                            </a>
                        </div>
                    </div>

                    {{-- Event Information --}}
                    <div class="border-t border-gray-100 mt-5 pt-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-5">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">
                                    {{ __('event_applications.labels.event_type') }}
                                </label>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ __('event_applications.event_types.' . $application_template->event_type) }}
                                </p>
                            </div>

                            @if($application_template->sport)
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">
                                        {{ __('event_applications.labels.sport') }}
                                    </label>
                                    <p class="text-sm font-medium text-gray-900">{{ $application_template->sport->translated_name }}</p>
                                </div>
                            @endif

                            @if($application_template->event_category)
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">
                                        {{ __('event_applications.labels.event_category') }}
                                    </label>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ \App\Enums\EvtEventOrganizationCategoryEnum::toString($application_template->event_category) }}
                                    </p>
                                </div>
                            @endif

                            @if($application_template->registration_type)
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">
                                        {{ __('event_applications.labels.registration_type') }}
                                    </label>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ __('event_applications.registration_types.' . $application_template->registration_type) }}
                                    </p>
                                </div>
                            @endif

                            @if($application_template->category)
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">
                                        {{ __('event_applications.labels.category') }}
                                    </label>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ __('event_applications.categories.' . $application_template->category) }}
                                    </p>
                                </div>
                            @endif

                            @if($application_template->age_group)
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">
                                        {{ __('event_applications.labels.age_group') }}
                                    </label>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $application_template->age_group }}
                                    </p>
                                </div>
                            @endif

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">
                                    {{ __('event_applications.sections.submission_period') }}
                                </label>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $application_template->submission_start_date?->format('d/m/Y') ?? '-' }}
                                    &ndash;
                                    {{ $application_template->submission_end_date?->format('d/m/Y') ?? '-' }}
                                </p>
                                @if($application_template->submission_end_date?->isFuture())
                                    <p class="text-xs text-blue-600 font-medium">
                                        {{ $application_template->submission_end_date->diffForHumans() }}
                                    </p>
                                @endif
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">
                                    {{ __('event_applications.sections.event_period') }}
                                </label>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $application_template->event_start_date?->format('d/m/Y') ?? '-' }}
                                    &ndash;
                                    {{ $application_template->event_end_date?->format('d/m/Y') ?? '-' }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">
                                    {{ __('event_applications.labels.max_applications') }}
                                </label>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $application_template->max_applications ?? __('event_applications.labels.unlimited') }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">
                                    {{ __('event_applications.labels.number_of_applications') }}
                                </label>
                                <p class="text-sm font-medium text-gray-900">{{ $application_template->applications_count }}</p>
                            </div>

                            @if($application_template->target_audience)
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">
                                        {{ __('event_applications.labels.target_audience') }}
                                    </label>
                                    <p class="text-sm font-medium text-gray-900">{{ __('event_applications.template_target_audience.' . $application_template->target_audience) }}</p>
                                </div>
                            @endif

                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">
                                    {{ __('common.created_at') }}
                                </label>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $application_template->created_at?->format('d/m/Y H:i') ?? '-' }}
                                </p>
                            </div>

                            @if($application_template->createdBy)
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">
                                        {{ __('common.created_by') }}
                                    </label>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $application_template->createdBy->name }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        @if($application_template->description)
                            <div class="mt-5 pt-5 border-t border-gray-100">
                                <label class="block text-xs font-medium text-gray-500 mb-2">
                                    {{ __('event_applications.labels.description') }}
                                </label>
                                <div class="text-sm text-gray-700 prose prose-sm max-w-none">
                                    {{ $application_template->description }}
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Action Bar --}}
                    <div class="mt-5 pt-4 border-t border-gray-100 flex flex-wrap items-center gap-3">
                        <a href="{{ route($backRoute) }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg font-medium text-sm text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring focus:ring-gray-200/50 transition-colors duration-150">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            {{ __('event_applications.actions.back_to_templates') }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- State Management Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200 bg-gray-50/50">
                    <div class="px-6 py-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="font-semibold text-gray-700">{{ __('event_applications.labels.state_management') }}</span>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-500 mb-4">{{ __('event_applications.help.state_management') }}</p>

                    <form action="{{ route($routeNamespace . '.application-templates.update-state', $application_template) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="flex flex-wrap gap-2">
                            <button type="submit" name="state" value="draft"
                                    class="btn btn-sm {{ $application_template->state === 'draft' ? 'btn-primary' : 'btn-secondary' }}">
                                {{ __('event_applications.template_states.draft') }}
                            </button>
                            <button type="submit" name="state" value="open"
                                    class="btn btn-sm {{ $application_template->state === 'open' ? 'btn-primary' : 'btn-secondary' }}">
                                {{ __('event_applications.template_states.open') }}
                            </button>
                            <button type="submit" name="state" value="closed"
                                    class="btn btn-sm {{ $application_template->state === 'closed' ? 'btn-primary' : 'btn-secondary' }}">
                                {{ __('event_applications.template_states.closed') }}
                            </button>
                            <button type="submit" name="state" value="archived"
                                    class="btn btn-sm {{ $application_template->state === 'archived' ? 'btn-primary' : 'btn-secondary' }}">
                                {{ __('event_applications.template_states.archived') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Document Manager --}}
            <div>
                @livewire('federation.template-document-manager', ['template' => $application_template])
            </div>

            {{-- Applications List --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200 bg-gray-50/50">
                    <div class="px-6 py-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span class="font-semibold text-gray-700">{{ __('event_applications.messages.applications_count', ['count' => $applications->total()]) }}</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="table-auto w-full divide-y divide-slate-200">
                        <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50">
                            <tr>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('event_applications.labels.entity') }}</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">{{ __('event_applications.labels.event_name') }}</div>
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
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        {{ $application->event_name }}
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
                                            <x-dynamic-table-buttons type="show" :route="route($routeNamespace . '.event-applications.show', $application)" />
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-2 first:pl-5 last:pr-5 py-12 text-center">
                                        <div class="text-slate-500">{{ __('event_applications.messages.no_applications') }}</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($applications->hasPages())
                <div>
                    {{ $applications->links() }}
                </div>
            @endif

        </div>

    </div>
</x-layout>
