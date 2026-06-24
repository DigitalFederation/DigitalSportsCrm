<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title"> {{ __('Edit Individual record') }} </h1>
        </div>

        <form action="{{ route(Request::segment(1).'.individual.update', $individual->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="entity_id" value="{{auth()->user()->entities()->value('entity.id')}}">
            <x-individual.form :federations="$federations" :entities="null" :countries="$countries" :individual="$individual" :mainFederation="null" :localFederation="null" />
        </form>

    </div>

</x-layout>
