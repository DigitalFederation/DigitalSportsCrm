@section('title', __('federation.create_individual_affiliation'))
<x-layout>
    <div class="previous-layout-classes">
        
        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('federation.create_individual_affiliation') }}</h1>
                <p class="text-gray-500 text-sm">{{ __('federation.create_individual_affiliation_description') }}</p>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('federation.individual-affiliations.index') }}" class="btn btn-secondary">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M6.6 13.4L5.2 12l4-4-4-4 1.4-1.4L12 8z" />
                    </svg>
                    <span class="hidden xs:block ml-2">{{ __('common.back') }}</span>
                </a>
            </div>
        </div>

        <!-- Information Box -->
        <x-information-box
            :title="__('federation.facilitation_notice')"
            :body="__('federation.entity_pays_notice')" />

        <!-- Livewire Component -->
        <livewire:federation.create-individual-membership />

    </div>
</x-layout>