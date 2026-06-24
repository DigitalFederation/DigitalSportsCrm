<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title"> {{ __('Edit Federation') }} </h1>
        </div>

        <div class="sm:flex sm:space-x-4">
            <div class="sm:w-2/3">
                <form action="{{ route(Request::segment(1).'.federation.update', $federation->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card mb-8">
                        <x-federation.form_create_information :federation="$federation" :federations="$federations"
                                                              :countries="$countries" :zones="$zones ?? null" />
                    </div>
                </form>
            </div>

            <div class="sm:w-1/3 flex flex-col gap-y-4 mb-8">
                <x-welcome-email-card
                    :user="$federation->users->first()"
                    :sendRoute="route('admin.federation.send-welcome-email', $federation)"
                />
            </div>
        </div>

    </div>

</x-layout>
