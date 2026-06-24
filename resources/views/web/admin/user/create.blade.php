<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title"> {{ __('Create User') }} </h1>
        </div>

        <div class="flex information-box items-center w-full mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-4" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="#9e9e9e" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <circle cx="12" cy="12" r="9"/>
                <line x1="12" y1="8" x2="12.01" y2="8"/>
                <polyline points="11 12 12 12 12 16 13 16"/>
            </svg>
            <p class="text-sm">
                {{ __('Use the following form to create a new user.') }} <br>
                {{ __('After the record is created a email will be sent to the User in order to setup their credentials.') }} <br>
            </p>
        </div>


        <form action="{{ route('admin.user.store') }}" method="POST">
            @csrf
            @include('web.admin.user.form')
        </form>


    </div>
</x-layout>
