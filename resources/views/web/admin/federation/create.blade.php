<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title"> {{ __('Create Federation') }} </h1>
        </div>

        <form action="{{ route('admin.federation.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('web.admin.federation.partials.form')
        </form>

    </div>

</x-layout>
