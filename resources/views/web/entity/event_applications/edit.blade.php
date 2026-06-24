@section('title', __('event_applications.titles.edit_application'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-5 mt-5">
            <div class="sm:flex sm:justify-between sm:items-center">
                <div>
                    <h1 class="page-first-title">{{ __('event_applications.titles.edit_application') }}</h1>
                    <p class="text-sm text-slate-600 mt-2">
                        {{ $application->event_name }}
                    </p>
                </div>

                <!-- Current Status -->
                <div>
                    @include('web.entity.event_applications.components.status-badge', ['application' => $application])
                </div>
            </div>
        </div>

        <!-- Application Form using Livewire -->
        <livewire:event-applications.entity.application-form
            :application="$application"
            :template="$application->template"
            :mode="'edit'" />

    </div>
</x-layout>
