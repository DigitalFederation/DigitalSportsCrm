<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('events.admin_technical_official_assignments_title') }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ __('events.admin_technical_official_assignments_description') }}</p>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2 items-center">
                <form action="{{ route('admin.evt-events.technical-official-assignments.export') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-secondary">
                        <x-heroicon-o-arrow-down-tray class="w-4 h-4 mr-2" />
                        {{ __('events.export_to_excel') }}
                    </button>
                </form>
            </div>

        </div>

        @if(session('error'))
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
                <p class="text-sm text-red-800">{{ session('error') }}</p>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 p-4">
                <p class="text-sm text-emerald-800">{{ session('success') }}</p>
            </div>
        @endif

        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            @livewire('admin.evt-events.technical-official-assignments-table')
        </div>

    </div>
</x-layout>
