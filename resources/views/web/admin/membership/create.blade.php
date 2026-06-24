<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title">{{ __('Add Membership') }}</h1>
        </div>

        <form action="{{ route(Request::segment(1).'.membership.store') }}" method="POST">
            @csrf

                    <div class="grow">
                        <!-- Panel body -->

                        @include('web.admin.membership.partials.form', ['edit' => false])


                    </div>

        </form>
    </div>
</x-layout>
