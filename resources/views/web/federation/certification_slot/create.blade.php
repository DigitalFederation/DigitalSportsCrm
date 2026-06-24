@section('title', __('Create Certification Slot'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title"> {{ __('Create Certification Slot') }} </h1>
        </div>


        <livewire:order-certification-slot-form />

    </div>

</x-layout>
