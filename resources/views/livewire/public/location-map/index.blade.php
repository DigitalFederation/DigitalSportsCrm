{{-- public/location-map/index.blade.php --}}
<div wire:key="location-map-{{ $this->getId() }}" class="relative flex flex-col md:flex-row"
    x-data="{
        isModalOpen: false,
        isSidebarOpen: window.innerWidth >= 768,
        closeSidebar() { this.isSidebarOpen = false; }
    }" x-init="window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
            isSidebarOpen = true;
        }
    })"
    @keydown.escape.window="isModalOpen = false; $wire.call('closeModal')" x-cloak>

    {{-- Sidebar / Filters --}}
    <div x-show="isSidebarOpen" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 transform shadow-xl z-30 md:z-auto md:static md:transform-none w-full md:w-96 lg:w-96 flex-shrink-0 bg-gray-50 dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col pt-16 md:pt-0">

        {{-- Sidebar Header with Search --}}
        <div class="px-6 py-4 flex-shrink-0 border-b border-gray-200 dark:border-gray-700 bg-white">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                    {{ __('location-map.title') }}
                </h2>
                {{-- Mobile Close Button --}}
                <button @click="isSidebarOpen = false"
                    class="md:hidden p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            {{-- Search Box --}}
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-blue-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <input type="text" id="search" wire:model.live.debounce.500ms="searchTerm"
                    placeholder="{{ __('location-map.search_placeholder') }}"
                    class="pl-10 block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500 shadow-sm py-2.5 text-sm">
            </div>
        </div>

        {{-- Sidebar Content Area --}}
        <div class="px-6 py-4 bg-white dark:bg-gray-900 flex-1 overflow-y-auto">
            {{-- Stats Panel --}}
            <div class="flex space-x-3 mb-6">
                <div class="flex-1 bg-white dark:bg-gray-700 rounded-xl overflow-hidden shadow-sm">
                    <div
                        class="px-4 py-3 bg-gradient-to-r from-blue-50 to-blue-100 dark:from-gray-700 dark:to-gray-800 border-b border-blue-100 dark:border-gray-600">
                        <p class="text-xs font-medium text-blue-800 dark:text-blue-300 uppercase tracking-wider">
                            {{ __('location-map.entities') }}</p>
                    </div>
                    <div class="px-4 py-3 flex justify-between items-center">
                        <p class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $this->totalLocations }}</p>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        {{ __('location-map.filters') }}</h3>
                    <button type="button" wire:click="clearFilters"
                        class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                        {{ __('location-map.clear_filters') }}
                    </button>
                </div>

                <div class="space-y-4" x-data="{ districtOpen: false, sportOpen: false, divingOpen: false }">
                    {{-- District Filter --}}
                    <div class="bg-white dark:bg-gray-700 rounded-xl overflow-hidden shadow-sm">
                        <button type="button" @click="districtOpen = !districtOpen"
                            class="w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-blue-100 dark:from-gray-700 dark:to-gray-800 border-b border-blue-100 dark:border-gray-600 flex justify-between items-center text-left">
                            <p class="text-xs font-medium text-blue-800 dark:text-blue-300 uppercase tracking-wider">
                                {{ __('location-map.district') }}
                            </p>
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 transform transition-transform duration-200"
                                :class="{ 'rotate-180': districtOpen }" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="districtOpen" x-collapse class="p-3">
                            <select id="district" wire:model.live="selectedDistrict"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-gray-200 text-sm">
                                <option value="">{{ __('location-map.all_districts') }}</option>
                                @foreach ($this->districts as $district)
                                    <option value="{{ $district->id }}">{{ $district->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Sport Clubs Filter --}}
                    <div class="bg-white dark:bg-gray-700 rounded-xl overflow-hidden shadow-sm">
                        <button type="button" @click="sportOpen = !sportOpen"
                            class="w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-blue-100 dark:from-gray-700 dark:to-gray-800 border-b border-blue-100 dark:border-gray-600 flex justify-between items-center text-left">
                            <p class="text-xs font-medium text-blue-800 dark:text-blue-300 uppercase tracking-wider">
                                {{ __('location-map.sport_clubs') }}
                            </p>
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 transform transition-transform duration-200"
                                :class="{ 'rotate-180': sportOpen }" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="sportOpen" x-collapse class="p-3">
                            <select wire:model.live="selectedSportLicense"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-gray-200 text-sm">
                                <option value="">{{ __('location-map.all_sport_licenses') }}</option>
                                @foreach ($this->sportLicenses as $license)
                                    <option value="{{ $license->id }}">{{ $license->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Diving Entities Filter --}}
                    <div class="bg-white dark:bg-gray-700 rounded-xl overflow-hidden shadow-sm">
                        <button type="button" @click="divingOpen = !divingOpen"
                            class="w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-blue-100 dark:from-gray-700 dark:to-gray-800 border-b border-blue-100 dark:border-gray-600 flex justify-between items-center text-left">
                            <p class="text-xs font-medium text-blue-800 dark:text-blue-300 uppercase tracking-wider">
                                {{ __('location-map.diving_entities') }}
                            </p>
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 transform transition-transform duration-200"
                                :class="{ 'rotate-180': divingOpen }" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="divingOpen" x-collapse class="p-3">
                            <select wire:model.live="selectedDivingLicense"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-gray-200 text-sm">
                                <option value="">{{ __('location-map.all_diving_licenses') }}</option>
                                @foreach ($this->divingLicenses as $license)
                                    <option value="{{ $license->id }}">{{ $license->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Location List --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                    {{ __('location-map.results') }}</h3>

                @if(count($this->mapLocations) > 0)
                    <div class="space-y-3">
                        @foreach($this->mapLocations as $location)
                            <div wire:key="loc-{{ $location['id'] }}-{{ $location['type'] }}"
                                wire:click="showDetails({{ $location['id'] }}, '{{ $location['type'] }}')"
                                class="bg-white dark:bg-gray-700 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-600 cursor-pointer hover:shadow-md hover:border-blue-200 dark:hover:border-blue-700 transition-all">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center
                                        {{ $location['type'] === 'federation' ? 'bg-purple-100 dark:bg-purple-900/50' : 'bg-blue-100 dark:bg-blue-900/50' }}">
                                        @if($location['type'] === 'federation')
                                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                            {{ $location['name'] }}
                                        </h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            {{ $location['location'] ?? $location['address'] ?? '' }}
                                        </p>
                                        <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded text-xs font-medium
                                            {{ $location['type'] === 'federation' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' }}">
                                            {{ $location['type'] === 'federation' ? __('location-map.federation') : __('location-map.entity') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('location-map.no_locations_found') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('location-map.try_adjusting_filters') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Map Area --}}
    <div class="flex-1 relative">
        {{-- Map container with unique ID and wire:ignore --}}
        <div wire:ignore id="map-{{ $this->getId() }}" class="w-full h-full z-0"></div>

        {{-- Loading Indicator --}}
        <div wire:loading wire:target="searchTerm, selectedDistrict, selectedSportLicense, selectedDivingLicense"
            class="absolute inset-0 bg-white bg-opacity-80 dark:bg-gray-800 dark:bg-opacity-80 backdrop-filter backdrop-blur-sm flex items-center justify-center z-10">
            <div class="flex flex-col items-center bg-white dark:bg-gray-700 rounded-lg p-6 shadow-xl">
                <svg class="animate-spin h-10 w-10 text-blue-600 dark:text-blue-400"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-200">{{ __('location-map.loading') }}</span>
            </div>
        </div>

        {{-- Mobile Fab Buttons --}}
        <div class="md:hidden fixed right-4 bottom-8 z-30 flex flex-col space-y-3">
            {{-- Toggle Sidebar Button --}}
            <button @click="isSidebarOpen = !isSidebarOpen"
                class="bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 p-3 rounded-full shadow-lg hover:shadow-xl transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Location Details Modal --}}
    @include('livewire.public.location-map._modal')
</div>

@push('head-css')
    {{-- Leaflet & MarkerCluster CSS --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.Default.css" />
    <style>
        :root {
            --z-base: 0;
            --z-map: 10;
            --z-map-controls: 15;
            --z-sidebar: 20;
            --z-controls: 30;
            --z-modal: 60;
        }

        /* Map Container */
        #map-{{ $this->getId() }} {
            height: 100%;
            width: 100%;
            min-height: 100vh;
            z-index: var(--z-base);
            background-color: #f0f0f0;
        }

        /* Make sure map is always placed at a suitable layer */
        .leaflet-container {
            background-color: #f8fafc !important;
        }

        /* Custom markers */
        .custom-marker {
            transition: transform 0.2s ease;
        }

        .custom-marker:hover {
            transform: scale(1.1);
            z-index: 1000;
        }

        .marker-icon {
            position: relative;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            border: 2px solid white;
            background-color: #1b6cb3;
            color: white;
        }

        .marker-icon.federation {
            background-color: #7c3aed;
        }

        .marker-icon::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
            border-top: 8px solid #1b6cb3;
        }

        .marker-icon.federation::after {
            border-top-color: #7c3aed;
        }

        .marker-license-count {
            position: absolute;
            top: -6px;
            right: -6px;
            width: 20px;
            height: 20px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgb(0 0 0 / 0.1);
        }

        .custom-tooltip {
            background: white;
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            font-size: 14px;
            max-width: 200px;
        }

        .custom-tooltip::before {
            border-top-color: white;
        }

        @media (max-width: 768px) {
            .marker-icon {
                width: 44px;
                height: 44px;
            }

            .custom-tooltip {
                font-size: 16px;
                max-width: 250px;
            }
        }
    </style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/leaflet.markercluster.js"></script>
<script>
    document.addEventListener("livewire:init", () => {
        const componentId = '{{ $this->getId() }}';
        const mapElementId = 'map-' + componentId;
        const initialCenter = @js($mapCenter);
        let map, markers, activeMarker;

        // Wait for DOM to be ready
        setTimeout(() => {
            const mapElement = document.getElementById(mapElementId);
            if (!mapElement) {
                console.error('Map element not found:', mapElementId);
                return;
            }

            // Initialize map from deployment configuration
            map = L.map(mapElementId, {
                minZoom: 3,
                maxZoom: 18,
                maxBounds: [[-90, -180], [90, 180]],
                maxBoundsViscosity: 1.0,
                worldCopyJump: true
            }).setView([initialCenter.lat, initialCenter.lng], initialCenter.zoom);

            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            markers = L.markerClusterGroup({
                maxClusterRadius: 50,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                zoomToBoundsOnClick: true,
                disableClusteringAtZoom: 18,
                iconCreateFunction: function(cluster) {
                    const count = cluster.getChildCount();
                    return L.divIcon({
                        html: `<div class="marker-icon" style="width:40px;height:40px;font-size:14px;">${count}</div>`,
                        className: 'custom-cluster',
                        iconSize: [40, 40]
                    });
                }
            });
            map.addLayer(markers);

            function createCustomMarker(location) {
                const type = location.type;
                let markerContent = type === "federation" ? "F" : "E";

                if (type === "entity" && location.licenses && location.licenses.length > 0) {
                    markerContent += `<span class="marker-license-count">${location.licenses.length}</span>`;
                }

                const icon = L.divIcon({
                    className: `custom-marker ${type}`,
                    html: `<div class="marker-icon ${type}">${markerContent}</div>`,
                    iconSize: [36, 36],
                    iconAnchor: [18, 36]
                });

                const marker = L.marker([location.lat, location.lng], { icon, id: `${type}-${location.id}` });

                const name = location.name.length > 25 ? location.name.slice(0, 25) + "..." : location.name;
                marker.bindTooltip(`<strong>${name}</strong>`, {
                    direction: "top",
                    offset: [0, -36],
                    className: "custom-tooltip"
                });

                return marker;
            }

            function updateMarkers(locations) {
                markers.clearLayers();

                locations.forEach(location => {
                    if (location.lat && location.lng) {
                        const marker = createCustomMarker(location);
                        marker.on("click", () => {
                            Livewire.find(componentId).call('showDetails', location.id, location.type);
                        });
                        markers.addLayer(marker);
                    }
                });

                // Fit bounds if we have markers
                if (markers.getLayers().length > 0) {
                    try {
                        map.fitBounds(markers.getBounds(), { padding: [50, 50], maxZoom: 12 });
                    } catch (e) {
                        console.log('Could not fit bounds:', e);
                    }
                }
            }

            // Listen for location updates
            Livewire.on("updateLocations", (data) => {
                const locations = data.locations || (Array.isArray(data) ? data[0]?.locations : []) || [];
                console.log("Received locations:", locations.length);
                updateMarkers(locations);
            });

            // Listen for map center updates
            Livewire.on("centerMap", (data) => {
                const params = Array.isArray(data) ? data[0] : data;
                if (params && params.lat !== undefined && params.lng !== undefined) {
                    map.setView([params.lat, params.lng], params.zoom || 10, {
                        animate: true,
                        duration: 1
                    });
                }
            });

            // Listen for highlight marker
            Livewire.on("highlightMarker", (data) => {
                const params = Array.isArray(data) ? data[0] : data;
                const markerId = `${params.type}-${params.id}`;

                markers.getLayers().forEach(marker => {
                    if (marker.options.id === markerId) {
                        map.panTo(marker.getLatLng());
                        marker.openTooltip();
                    }
                });
            });

            // Invalidate map size after a short delay to ensure proper rendering
            setTimeout(() => {
                map.invalidateSize();
            }, 250);

            // Call initializeMap on Livewire component to load initial markers
            if (componentId) {
                const component = Livewire.find(componentId);
                if (component) {
                    component.call('initializeMap');
                }
            }

        }, 100);
    });
</script>
@endpush
