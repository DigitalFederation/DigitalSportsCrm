@section('title', __('event_applications.titles.edit_application'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-5 mt-5">
            <div class="flex items-center gap-3">
                <h1 class="page-first-title">{{ __('event_applications.titles.edit_application') }}</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      style="background-color: {{ $application->stateColor() }}20; color: {{ $application->stateColor() }};">
                    {{ $application->stateName() }}
                </span>
            </div>
            <p class="text-sm text-slate-600 mt-2">{{ $application->event_name }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Main Content -->
            <div class="lg:col-span-2">
                <livewire:event-applications.entity.application-form-wizard
                    :application="$application"
                    :template="$application->template"
                    :mode="'edit'"
                    :routeNamespace="'federation'" />
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <div id="wizard-step-nav"></div>
            </div>

        </div>

    </div>
</x-layout>
