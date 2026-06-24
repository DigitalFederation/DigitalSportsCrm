<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title"> {{ __('Create Individual') }} </h1>
        </div>

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <form action="{{ route(Request::segment(1).'.individual.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <x-individual.form
                :federations="$federations"
                :entities="null"
                :countries="$countries"
                :individual="$individual"
                :mainFederation="null"
                :localFederation="null"
                />
        </form>

    </div>

</x-layout>
