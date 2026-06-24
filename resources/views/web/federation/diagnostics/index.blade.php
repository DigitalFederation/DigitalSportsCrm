@section('title', __('diagnostics.title'))
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('diagnostics.title') }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('diagnostics.subtitle') }}</p>
            </div>
        </div>

        <livewire:admin.diagnostics.eligibility-diagnostic-center />
    </div>
</x-layout>
