<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title"> {{ __('Create License') }} </h1>
        </div>

        <form action="{{ route('admin.license.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
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
