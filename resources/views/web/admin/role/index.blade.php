<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('Roles & Permissions') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

            </div>

        </div>


        <div class="flex information-box mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-4" width="24" height="24" viewBox="0 0 24 24"
                 stroke-width="1.5" stroke="#9e9e9e" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <circle cx="12" cy="12" r="9" />
                <line x1="12" y1="8" x2="12.01" y2="8" />
                <polyline points="11 12 12 12 12 16 13 16" />
            </svg>
            <p class="text-sm"> Permissions can be updated for each defined Role. <br> <strong>Attention</strong>
                Defining new permissions for existing roles can have adverse effects, since it can lift restrictions to
                actions and show private information. <br> Use with caution.</p>

        </div>

        <div class="flex flex-col sm:justify-center sm:items-center mb-5">

            <livewire:role-permission-manager />

        </div>


    </div>
</x-layout>
