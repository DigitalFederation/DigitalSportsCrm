<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <h1 class="page-first-title"> {{ ucwords($license_type_name) }} {{ __('license request') }} </h1>
        </div>

        <x-common.license_attributed.create
            :committee="$committee"
            :licenseTypeName="$license_type_name"
            :federations="$federations"
            :federation="null"
            :entities="$entities"
            :entity="null"
            :professionalRole="$professional_role"
            :requester_model_type="$requester_model_type" />

    </div>

</x-layout>
