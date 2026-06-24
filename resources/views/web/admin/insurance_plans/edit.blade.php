<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title">{{ __('main.edit_insurance_plan') }}</h1>
        </div>

        <form action="{{ route('admin.insurance-plans.update', $insurance_plan->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grow">
                @include('web.admin.insurance_plans.partials.form', ['edit' => true])
            </div>

        </form>
    </div>
</x-layout>
