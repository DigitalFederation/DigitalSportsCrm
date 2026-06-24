<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title"> {{ __('Edit Entity') }} </h1>
        </div>

        <div class="sm:flex sm:space-x-4">
            <div class="sm:w-2/3">
                <form action="{{ route(Request::segment(1).'.entity.update', $entity->id) }}" method="POST"
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <x-entity.form_create_edit
                        :entity="$entity"
                        :federations="$federations"
                        :countries="$countries"
                        :committees="$committees"
                        :edit="true" />
                </form>
            </div>

            <div class="sm:w-1/3 flex flex-col gap-y-4 mb-8">
                <x-welcome-email-card
                    :user="$entity->users->first()"
                    :sendRoute="route('admin.entity.send-welcome-email', $entity)"
                />
            </div>
        </div>

    </div>

</x-layout>
