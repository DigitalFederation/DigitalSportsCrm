@section('title', __('Edit Individual'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title"> {{ __('Edit Individual') }} </h1>


            <a href="{{ route('federation.individual.show-update-email', $individual) }}"
                class="btn btn-info gap-x-2 flex items-center">
                <x-svg.person-add class="w-5 h-5"></x-svg.person-add>
                <span>{{ __('Update public email') }}</span>
            </a>

        </div>

        <form action="{{ route('federation.individual.update', $individual->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Entities removed. Before: entities="$federation[0]->entities" -->
            <x-individual.form :federations="$federation" :entities="null" :countries="$countries" :individual="$individual" :mainFederation="$federation[0]->id"
                :localFederation="null" />

        </form>

    </div>

</x-layout>
