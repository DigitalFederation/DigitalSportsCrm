@pushOnce('footer-scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.min.js"></script>
@endPushOnce
@pushOnce('head-css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.css" />
    <style>
        .leaflet-container {
            width: 100%;
            height: 100%;
        }

        .location-search-wrapper {
            position: absolute;
            top: 10px;
            left: 50px;
            right: 10px;
            z-index: 1000;
        }

        .leaflet-control-geocoder {
            top: 60px !important; /* Adjust based on your design */
            left: 10px !important;
        }
    </style>
@endPushOnce
<div
    x-cloak
    x-data="{
        map: null,
        marker: null,
        lat: @js($latitude),
        lng: @js($longitude),
        mapId: 'map-' + Date.now(),

        init() {
            this.$watch('$wire.isModalOpen', (value) => {
                if (value) {
                    // Wait for modal transition to complete
                    setTimeout(() => {
                        this.initializeMap();
                    }, 100);
                } else if (this.map) {
                    // Cleanup when modal closes
                    this.map.remove();
                    this.map = null;
                    this.marker = null;
                }
            });
        },

        initializeMap() {
            // Make sure the map container exists
            const container = document.getElementById(this.mapId);
            if (!container) return;

            // Clean up existing map if any
            if (this.map) {
                this.map.remove();
                this.map = null;
            }

            // Set explicit dimensions before initialization
            container.style.width = '100%';
            container.style.height = '400px';


            // Initialize map with specific size
            this.map = L.map(this.mapId, {
                center: [this.lat || 0, this.lng || 0],
                zoom: this.lat && this.lng ? 13 : 2,
                zoomControl: true,
                scrollWheelZoom: true
            });


            // Initialize geocoder control for searching locations
            const geocoder = L.Control.geocoder({
                defaultMarkGeocode: false
            }).addTo(this.map);

            geocoder.on('markgeocode', (e) => {
                const latLng = e.geocode.center;
                this.updateMarkerPosition(latLng.lat, latLng.lng);
                this.map.setView(latLng, 13);
            });

            // Add additional code to set focus on search input when map is initialized
            setTimeout(() => {
                const geocoderInput = document.querySelector('.leaflet-control-geocoder-form input');
                if (geocoderInput) {
                    geocoderInput.focus();
                }
            }, 500);


            // Add tile layer with retry mechanism
            const addTileLayer = (retries = 3) => {
                try {
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors',
                        maxZoom: 19
                    }).addTo(this.map);
                } catch (e) {
                    if (retries > 0) {
                        setTimeout(() => addTileLayer(retries - 1), 1000);
                    }
                }
            };
            addTileLayer();

            // Initialize marker
            this.marker = L.marker([this.lat || 0, this.lng || 0], {
                draggable: true
            });

            if (this.lat && this.lng) {
                this.marker.addTo(this.map);
            }

            // Handle map clicks
            this.map.on('click', (e) => {
                this.updateMarkerPosition(e.latlng.lat, e.latlng.lng);
            });

            // Handle marker drags
            this.marker.on('dragend', () => {
                const pos = this.marker.getLatLng();
                this.updateMarkerPosition(pos.lat, pos.lng);
            });

            // Get user location if needed
            if (!this.lat && !this.lng && navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    this.map.setView([position.coords.latitude, position.coords.longitude], 13);
                });
            }

            // Force multiple resize checks
            const ensureMapSize = () => {
                this.map.invalidateSize();
            };

            // Check size multiple times to ensure proper rendering
            setTimeout(ensureMapSize, 100);
            setTimeout(ensureMapSize, 250);
            setTimeout(ensureMapSize, 500);

            // Add resize observer
            const resizeObserver = new ResizeObserver(() => {
                this.map.invalidateSize();
            });
            resizeObserver.observe(container);



        },

        updateMarkerPosition(lat, lng) {
            const roundedLat = parseFloat(lat.toFixed(6));
            const roundedLng = parseFloat(lng.toFixed(6));

            this.marker.setLatLng([roundedLat, roundedLng]);
            if (!this.map.hasLayer(this.marker)) {
                this.marker.addTo(this.map);
            }
            this.$wire.handleLocationSelected(roundedLat, roundedLng);
        }
    }"
>
    {{-- Location Input Fields --}}
    {{-- Enhanced Location Input Fields --}}
    <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="relative">
                <label for="{{ $latFieldName }}" class="block text-sm font-medium text-gray-700">
                    Latitude
                    @if($latitude)
                        <span class="text-xs text-gray-500 ml-1">(Selected)</span>
                    @endif
                </label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <input
                        type="text"
                        id="{{ $latFieldName }}"
                        name="{{ $latFieldName }}"
                        wire:model.live="latitude"
                        readonly
                        class="block w-full pr-10 rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm disabled:bg-gray-50 disabled:text-gray-500"
                        placeholder="Select on map">
                    @if($latitude)
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    @endif
                </div>
            </div>

            <div class="relative">
                <label for="{{ $lngFieldName }}" class="block text-sm font-medium text-gray-700">
                    Longitude
                    @if($longitude)
                        <span class="text-xs text-gray-500 ml-1">(Selected)</span>
                    @endif
                </label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <input
                        type="text"
                        id="{{ $lngFieldName }}"
                        name="{{ $lngFieldName }}"
                        wire:model.live="longitude"
                        readonly
                        class="block w-full pr-10 rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500 sm:text-sm disabled:bg-gray-50 disabled:text-gray-500"
                        placeholder="Select on map">
                    @if($longitude)
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex space-x-2">
            <button
                type="button"
                wire:click="openModal"
                class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Set on Map
            </button>
            @if($latitude && $longitude)
                <button
                    type="button"
                    wire:click="clearLocation"
                    class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Clear Location
                </button>
            @endif
        </div>
    </div>

    {{-- Enhanced Map Modal --}}
    @if($isModalOpen)
        <div
            class="fixed z-50 inset-0 overflow-y-auto"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-title">
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
                <div
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    aria-hidden="true"
                    wire:click="closeModal">
                </div>

                <div
                    class="relative bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-4xl sm:w-full">
                    <div class="absolute right-0 top-0 pr-4 pt-4 z-10">
                        <button
                            type="button"
                            class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                            wire:click="closeModal">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Select Location
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Click on the map to select a location or use the search box to find a specific
                                        place.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 relative" style="height: 400px">
                            <div wire:ignore>
                                <div x-bind:id="mapId" class="w-full h-full rounded-lg"></div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm"
                            wire:click="closeModal">
                            Confirm Location
                        </button>
                        <button
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            wire:click="closeModal">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
