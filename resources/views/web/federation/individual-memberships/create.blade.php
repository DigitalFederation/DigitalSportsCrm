@section('title', __('federation.individual_memberships.create_title'))
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="page-first-title">{{ __('federation.individual_memberships.create_title') }}</h1>
                <p class="text-slate-600 mt-2">{{ __('federation.individual_memberships.create_subtitle') }}</p>
            </div>
            <div>
                <a href="{{ route('federation.individual-memberships.index') }}" 
                   class="btn border-slate-200 hover:border-slate-300 text-slate-600">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M6.6 13.4L5.2 12l4-4-4-4 1.4-1.4L12 8z"/>
                    </svg>
                    <span class="ml-2">{{ __('federation.common.back_to_list') }}</span>
                </a>
            </div>
        </div>

        <!-- Information Boxes -->
        <x-information-box
            title="{{ __('federation.individual_memberships.payment_responsibility_title') }}"
            body="{{ __('federation.individual_memberships.payment_responsibility_body') }}">
        </x-information-box>

        <x-information-box
            title="{{ __('federation.individual_memberships.membership_packages_title') }}"
            body="{{ __('federation.individual_memberships.membership_packages_body') }}">
        </x-information-box>

        <!-- Creation Form -->
        <div class="card">
            <livewire:federation.create-individual-membership />
        </div>
    </div>
</x-layout>