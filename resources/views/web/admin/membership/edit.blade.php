<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title">{{ __('Edit Membership') }}</h1>
        </div>

        <form action="{{ route(Request::segment(1).'.membership.update', $membership->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grow">
                @include('web.admin.membership.partials.form', ['edit' => true])
            </div>

        </form>
    </div>
</x-layout>
