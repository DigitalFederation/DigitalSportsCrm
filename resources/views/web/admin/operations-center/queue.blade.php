@section('title', __('operations.queue_management'))
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <nav class="text-sm text-slate-500 mb-2">
                    <a href="{{ route('admin.operations.index') }}" class="hover:text-slate-700">{{ __('operations.title') }}</a>
                    <span class="mx-2">/</span>
                    <span>{{ __('operations.queue_management') }}</span>
                </nav>
                <h1 class="page-first-title">{{ __('operations.queue_management') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-secondary" href="{{ route('admin.operations.index') }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('operations.back_to_dashboard') }}
                </a>
            </div>
        </div>

        <!-- Queue Monitor Component -->
        <livewire:admin.operations-center.queue-monitor />
    </div>
</x-layout>
