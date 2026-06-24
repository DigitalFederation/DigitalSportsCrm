<x-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <div class="mb-8 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('membership.entity_member_memberships_title') }}</h1>
        </div>

        <div class="max-w-7xl mx-auto">
            {{-- Mount the Livewire component --}}
            <livewire:entity.member-subscription-manager :insurance_filter="false" />
        </div>

    </div>
</x-layout>