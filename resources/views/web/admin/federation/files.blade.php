<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title"> {{ __('Files for ') }} {{ $federation->legal_name}} </h1>
        </div>


        <x-information-box
            title="{{ __('Information') }}"
            body="{{ __('Use the above box to upload files associated to this Federation. Attentionto the file size. Dont upload files larger than 50MB.')}}" >
        </x-information-box>


        <h2 class="font-bold">File Selection</h2>
        <div class="sm:space-x-4">
            <livewire:file-upload :model="$federation" :files="$federation->getMedia('media')" />
        </div>


    </div>

</x-layout>
