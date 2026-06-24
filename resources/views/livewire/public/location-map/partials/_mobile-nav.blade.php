{{-- public/location-map/partials/_mobile-nav.blade.php --}}
<div class="lg:hidden fixed bottom-0 left-0 right-0 z-20 bg-slate-700 border-t border-gray-200">
    <div class="grid grid-cols-2 divide-x divide-gray-200">
        <button
            @click="activeView = 'map'"
            :class="{'text-white bg-slate-700': activeView === 'map'}"
            class="flex items-center justify-center gap-2 py-4 text-sm font-medium"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
            </svg>
            {{ __('location-map.Map') }}
        </button>
        <button
            @click="activeView = 'list'"
            :class="{'text-white bg-slate-700': activeView === 'list'}"
            class="flex items-center justify-center gap-2 py-4 text-sm font-medium"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            {{ __('location-map.List') }}
        </button>
    </div>
</div>
