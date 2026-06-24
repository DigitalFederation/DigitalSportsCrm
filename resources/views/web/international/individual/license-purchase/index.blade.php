@section('title', __('admin.purchase_international_license'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('admin.purchase_international_license') }}</h1>
                <p class="text-sm text-slate-500">{{ __('admin.select_purchase_international_license') }}</p>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('international.individual.licenses-attributed.index') }}" class="btn btn-secondary">
                    {{ __('admin.view_my_licenses') }}
                </a>
            </div>

        </div>

        @if($individual->federations()->whereHas('licenses', function($query) { $query->whereHas('committee', fn($q) => $q->where('is_international', true)); })->exists())
            <livewire:individual.international-license-purchase-form
                :individual="$individual"
                :committee="$committee ?? null" />
        @else
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-amber-400 mr-2 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium text-amber-800">{{ __('admin.no_international_access') }}</h3>
                        <p class="text-sm text-amber-700 mt-1">{{ __('admin.no_international_access_description') }}</p>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-layout>
