<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title"> {{ __('Edit Certification') }} </h1>
        </div>

        <form action="{{ route('admin.certification.update', $certification->id) }}" method="POST"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <x-certification.form_create
                :parents="$parents"
                :committees="$committees"
                :professionalRoles="$professional_roles"
                :licenses="$licenses"
                :certification="$certification"
                :categories="$certification_categories"
                :roles="$roles"
            />

        </form>

    </div>

</x-layout>
