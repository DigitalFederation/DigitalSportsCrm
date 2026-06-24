<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title"> {{ __('Edit License') }} </h1>
        </div>

        <form action="{{ route('admin.license.update', $license->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <x-license.form_create
                :license="$license"
                :committees="$committees"
                :licenseTypes="$licenseTypes"
                :professionalRoles="$professionalRoles"
                :sports="$sports"
                :intervalUnit="$intervalUnit"
                :requesterModels="$requesterModels"
                :certifications="$certifications"
                :federations="$federations"
                :roles="$roles"
            />
        </form>

    </div>

</x-layout>
