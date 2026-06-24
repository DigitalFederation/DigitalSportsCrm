<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title"> {{ __('entity.edit_entity_record') }} </h1>
        </div>


        <form action="{{ route(Request::segment(1).'.entity.update', $entity->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="sm:flex sm:space-x-4">
                <x-entity.form_create_edit
                    :entity="$entity"
                    :federations="null"
                    :countries="$countries"
                    :committees="$committees"
                    :edit="true" />
            </div>
        </form>

    </div>

</x-layout>
