<x-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">{{ __('insurances.member_insurance_management') }}</h1>
        </div>


        <div class="max-w-7xl mx-auto">
            <livewire:entity.member-subscription-manager :insurance_filter="true"/>
        </div>

    </div>
</x-layout>