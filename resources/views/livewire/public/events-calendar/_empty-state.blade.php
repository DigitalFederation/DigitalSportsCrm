<div class="text-center py-16 bg-white rounded-2xl border border-gray-100 shadow-sm">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4">
        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
    </div>
    <h3 class="text-lg font-medium text-gray-900 mb-1">
        {{ __('public.events.no_results') }}
    </h3>
    <p class="text-sm text-gray-500 max-w-md mx-auto mb-4">
        {{ __('public.events.no_results_hint') }}
    </p>
    <button type="button" wire:click="clearFilters"
        class="inline-flex items-center px-4 py-2 border border-blue-300 text-sm font-medium rounded-lg text-blue-700 bg-white hover:bg-blue-50">
        {{ __('public.events.filters.clear') }}
    </button>
</div>
