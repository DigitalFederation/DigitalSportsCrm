<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('License Detail') }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            </div>
        </div>


        <div class="sm:flex sm:justify-center sm:items-start mb-5 space-x-4">

            <div class="bg-white shadow-lg rounded-sm border border-slate-200 md:w-2/3">
                <x-license_attributed.show_panel :license="$license"></x-license_attributed.show_panel>
            </div>

            @if($license->owner instanceof \Domain\Individuals\Models\Individual)
                    <x-individual.profile_panel :individual="$license->owner" individualType="Individual"></x-individual.profile_panel>
            @endif

            @if($license->owner instanceof \Domain\Entities\Models\Entity)
                    <x-individual.profile_panel :individual="$license->owner" individualType="Individual"></x-individual.profile_panel>
            @endif
        </div>
    </div>
</x-layout>
