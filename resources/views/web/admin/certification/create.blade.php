<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title"> {{ $title }} </h1>
        </div>

        <form action="{{ route('admin.certification.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <x-certification.form_create :committees="$committees" :professionalRoles="$professional_roles" :parents="$parents" :licenses="$licenses"
                :certification="$certification" :categories="$certification_categories" :roles="$roles" />

        </form>

    </div>

</x-layout>
