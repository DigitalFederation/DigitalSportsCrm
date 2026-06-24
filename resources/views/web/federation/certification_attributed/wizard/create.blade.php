<x-layout>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-700">{{ __('Assign Certification Wizard') }}</h1>
            <p class="text-sm text-gray-500">{{ __('Follow the steps to attribute a new certification.') }}</p>
        </div>

        {{-- Livewire Wizard Component --}}

        <livewire:certifications.certification-attribution-wizard
            :committee_code="!empty(Request::query()['filter']) && !empty(Request::query()['filter']['committee'])
            ? Request::query()['filter']['committee']
            : null" />


    </div>
</x-layout>
