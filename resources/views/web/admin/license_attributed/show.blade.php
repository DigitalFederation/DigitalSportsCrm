<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <div class="sm:mb-0">
                <h1 class="page-first-title">{{ __('License Detail') }} :: {{ $license->license_name }}</h1>
            </div>

        </div>

        <div class="mb-5 mt-4">

            <div class="sm:flex gap-x-4 sm:justify-start sm:items-start">
                <div class="card md:w-2/3 overflow-hidden">
                    <x-license_attributed.show_panel :license="$license"></x-license_attributed.show_panel>
                </div>

                <div class="w-full md:w-1/3 mt-4 md:mt-0">


                    @if($license->model_type === 'individual')
                        <x-individual.profile_panel :individual="$license->owner"
                                                    individualType="individual"></x-individual.profile_panel>
                    @endif

                    @livewire('widget-activity-log', ['subject' => $license, 'loadType' => 'poll'])
                </div>
            </div>

        </div>
    </div>
</x-layout>
