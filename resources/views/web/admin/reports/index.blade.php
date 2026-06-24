@section('title', __('reports.title'))
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('reports.title') }}</h1>
            </div>
        </div>

        <!-- Livewire Component -->
        <livewire:reports.report-generator />
    </div>
</x-layout>
