{{-- public/location-map/_filters.blade.php --}}

{{-- Filters Component --}}
<div>
    {{-- Mobile Filters Panel --}}
    <div
        x-show="filtersOpen"
        x-cloak
        class="lg:hidden fixed inset-0 bg-gray-500 bg-opacity-75 z-[var(--z-modal)]"
        @click="filtersOpen = false"
    ></div>

    <div
        x-show="filtersOpen"
        x-cloak
        class="lg:hidden fixed inset-y-0 right-0 max-w-full w-full bg-white z-[var(--z-modal)] transform transition-transform"
        @click.away="filtersOpen = false"
    >
        <div class="h-auto flex flex-col">
            {{-- Mobile Header --}}
            <div class="px-4 py-6 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('location-map.Filters') }}</h3>
                    <button
                        @click="filtersOpen = false"
                        class="p-2 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Mobile Filter Content --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-6">
                @include('livewire.public.location-map.partials._filter-fields')
            </div>

            {{-- Mobile Footer --}}
            <div class="border-t border-gray-200 p-4">
                <button
                    @click="filtersOpen = false"
                    class="w-full py-3 px-4 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition-colors"
                >
                    {{ __('location-map.Apply Filters') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Desktop Filters Panel --}}
    <div class="hidden lg:block absolute top-3 left-12 z-[var(--z-filters)]">
        <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-xl border border-gray-100 w-80 overflow-hidden">
            {{-- Desktop Header --}}
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                    </svg>
                    {{ __('location-map.Search Filters') }}
                </h2>
            </div>

            {{-- Desktop Filter Content --}}
            <div class="p-6 space-y-6">
                @include('livewire.public.location-map.partials._filter-fields')
            </div>
        </div>
    </div>
</div>
