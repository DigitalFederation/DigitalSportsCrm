<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('Create Manual Order') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn-info btn-sm" href="{{ route('admin.document.index') }}">
                    @include('components.svg.chevron-left', ['class' => 'h-4 w-4'])
                    <span>{{ __('Back') }}</span>
                </a>
            </div>

        </div>


        <div class="w-full">
            <livewire:document-manual-create-component />
        </div>


    </div>
</x-layout>
