<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title"> {{ __('Edit Individual record') }} </h1>

            <a href="{{ route('admin.individual.show-update-email', $individual) }}"
                class="btn btn-info gap-x-2 flex items-center">
                <x-svg.person-add class="w-5 h-5"></x-svg.person-add>
                <span>{{ __('Update public email') }}</span>
            </a>
        </div>

        <div class="sm:flex sm:space-x-4">
            <div class="sm:w-2/3">
                <form action="{{ route(Request::segment(1) . '.individual.update', $individual->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <x-individual.form :federations="$federations" :entities="null" :countries="$countries" :individual="$individual" :mainFederation="null"
                        :localFederation="null" />
                </form>
            </div>

            <div class="sm:w-1/3 flex flex-col gap-y-4 mb-8">
                <x-welcome-email-card
                    :user="$individual->user"
                    :sendRoute="route('admin.individual.send-welcome-email', $individual)"
                />
            </div>
        </div>

    </div>

</x-layout>
