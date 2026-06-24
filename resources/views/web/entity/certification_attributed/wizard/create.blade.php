<x-layout>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Page header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-700">{{ __('certifications.wizard_entity_title') }}</h1>
            <p class="text-sm text-gray-500">{{ __('certifications.wizard_entity_description') }}</p>
        </div>

        {{-- Livewire Wizard Component --}}
        {{-- Pass committeeCode from the controller. The wizard will use it. --}}
        {{-- Set actorType to 'entity' --}}
        <livewire:certifications.certification-attribution-wizard
            :committee_code="$committeeCode ?? null"
            :actorType="'entity'"
        />

    </div>
</x-layout>
