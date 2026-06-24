<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title"> {{ __('Create Entity') }} </h1>
        </div>


        <form action="{{ route(Request::segment(1).'.entity.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <x-entity.form_create_edit :entity="$entity" :federations="$federations" :countries="$countries" :committees="$committees"/>

        </form>

    </div>

</x-layout>
