@section('title', __('License Management'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('License Management') }}</h1>
                <p class="text-slate-600">{{ __('Comprehensive license administration and analytics') }}</p>
            </div>
            
            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route(Request::segment(1).'.license.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    {{ __('Create License Type') }}
                </a>
                
                <button wire:click="openAnalyticsModal" class="btn btn-secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    {{ __('Analytics') }}
                </a>
            </div>
        </div>

        <x-information-box
            title="{{ __('License Administration') }}"
            :body="__('Manage all licenses in the system. You can view, suspend, activate, and track the status of licenses across all federations. Use bulk actions for efficient management of multiple licenses.')"/>

        <!-- Dashboard Component -->
        {{-- TODO: Implement license-management-dashboard component --}}
        {{-- <livewire:admin.license-management-dashboard /> --}}

    </div>
</x-layout>