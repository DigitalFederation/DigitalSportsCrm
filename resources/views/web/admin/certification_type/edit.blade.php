<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title"> {{ __('Edit Certification Type') }} </h1>
        </div>

        <div class="sm:flex sm:space-x-4">
            <div class="mb-8 sm:w-full">
                <form action="{{ route('admin.certification-type.update', $type->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <x-international.certification-type.create :type="$type"/>

                </form>
            </div>
        </div>


    </div>

</x-layout>
