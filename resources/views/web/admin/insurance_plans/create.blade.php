<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title">{{ __('main.create_insurance_plan') }}</h1>
        </div>

        <form action="{{ route('admin.insurance-plans.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grow">
                @include('web.admin.insurance_plans.partials.form', ['edit' => false])
            </div>

        </form>
    </div>
</x-layout>
