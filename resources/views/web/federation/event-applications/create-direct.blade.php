@section('title', __('event_applications.titles.create_application'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-5 mt-5">
            <h1 class="page-first-title">{{ __('event_applications.titles.create_application') }}</h1>
            @if(isset($template))
                <p class="text-sm text-slate-600 mt-2">
                    {{ __('event_applications.labels.template') }}: <span class="font-medium">{{ $template->name }}</span>
                </p>
            @else
                <p class="text-sm text-slate-600 mt-2">
                    {{ __('event_applications.types.direct_submission') }}
                </p>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Main Content -->
            <div class="lg:col-span-2">
                <livewire:event-applications.entity.application-form-wizard
                    :template="$template ?? null"
                    :mode="'create'"
                    :routeNamespace="'federation'" />
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <div id="wizard-step-nav"></div>
            </div>

        </div>

    </div>
</x-layout>
