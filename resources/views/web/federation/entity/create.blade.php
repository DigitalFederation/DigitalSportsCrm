<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <h1 class="page-first-title"> {{ __('entity.create_entity') }} </h1>
        </div>


        <form action="{{ route(Request::segment(1).'.entity.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <x-entity.form_create_edit :entity="$entity" :countries="$countries" />
        </form>

    </div>

</x-layout>
