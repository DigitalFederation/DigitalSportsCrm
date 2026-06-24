{{-- public/location-map/partials/_mobile-header.blade.php --}}
{{-- Mobile Header --}}
<div class="lg:hidden fixed top-0 left-0 right-0 z-20 bg-slate-700 border-b border-white">
    <div class="flex items-center justify-between px-4 py-2">

        <a href="{{ route('login') }}">
            <div class="h-12">
                <x-authentication-card-logo class="h-full" />
            </div>
        </a>

        <div class=" text-white text-center">
            <span class="block text-lg font-bold">{{ __('location-map.Federation Community') }}</span>
            <span class="block text-sm text-gray-300">{{ __('location-map.Find your local Member') }}</span>
        </div>

        <button
            @click="filtersOpen = !filtersOpen"
            class="p-2 text-white hover:text-gray-700"
        >
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
        </button>
    </div>
</div>
