{{-- public/location-map/_list.blade.php --}}
<div class="flex flex-col h-full">
    {{-- List Header --}}
    <div class="flex-none p-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ __('location-map.Results') }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ $this->federationsCount }} {{ __('location-map.Federations') }},
                    {{ $this->entitiesCount }} {{ __('location-map.Entities') }}
                </p>
            </div>
            <button
                @click="isDrawerOpen = false"
                class="hidden p-2 text-gray-500 hover:text-gray-700"
            >
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Quick Filter --}}
    <div class="flex-none p-4 border-b border-gray-200 bg-gray-50">
        <div class="relative">
            <input
                type="text"
                wire:model.live="searchTerm"
                placeholder="{{ __('location-map.Search ...') }}"
                class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-200 focus:border-blue-500 focus:ring-blue-500"
            >
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>
    </div>

    {{-- List Content --}}
    <div class="flex-1 overflow-y-auto">
        @if($this->federationsCount !== 0 && $this->entitiesCount !== 0)
            {{-- Federations Section --}}
            @if($this->federationsCount > 0)
                <div class="px-4 py-2 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-sm font-medium text-gray-500">{{ __('location-map.Federations') }}</h3>
                </div>
                <div class="divide-y divide-gray-200">
                    @foreach($this->mapLocations as $location)
                        @if($location['type'] === 'federation')
                            @include('livewire.public.location-map.partials._list-item', ['location' => $location])
                        @endif
                    @endforeach
                </div>
            @endif

            {{-- Entities Section --}}
            @if($this->entitiesCount > 0)
                <div class="px-4 py-2 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-sm font-medium text-gray-500">{{ __('location-map.Entities') }}</h3>
                </div>
                <div class="divide-y divide-gray-200">
                    @foreach($this->mapLocations as $location)
                        @if($location['type'] === 'entity')
                            @include('livewire.public.location-map.partials._list-item', ['location' => $location])
                        @endif
                    @endforeach
                </div>
            @endif
        @else
            {{-- Empty State --}}
            <div class="h-full flex items-start justify-center">
                <div class="p-8 text-center max-w-sm">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-4">
                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 mb-1">{{ __('location-map.No locations found') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('location-map.Try adjusting your filters to find more locations.') }}</p>
                </div>
            </div>
        @endif
    </div>
</div>
