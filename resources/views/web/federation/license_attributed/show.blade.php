@section('title', __('License Detail'))
<x-layout>
    <div class="previous-layout-classes">

        <div class="sm:flex gap-x-4 sm:justify-start sm:items-start mb-4 mt-4">

            <div class="card w-full md:w-2/3 overflow-hidden">
                <x-license_attributed.show_panel :license="$license"></x-license_attributed.show_panel>
            </div>

            <div class="w-full md:w-1/3 mt-4 md:mt-0">

                @if($isDefaultFederation && $license->model_type === 'individual')
                    <x-individual.profile_panel :individual="$license->owner"
                                                individualType="individual"></x-individual.profile_panel>
                @endif

                @livewire('widget-activity-log', ['subject' => $license, 'loadType' => $isDefaultFederation ? 'poll' : 'lazy'])
            </div>

        </div>


    </div>
</x-layout>
