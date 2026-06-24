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

        <!-- Application Form using Livewire -->
        <livewire:event-applications.federation.application-form
            :template="$template ?? null"
            :mode="'create'" />

    </div>
</x-layout>
