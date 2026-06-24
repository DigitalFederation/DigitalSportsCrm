{{-- resources/views/components/certification/school-map.blade.php --}}
<div class="h-full relative">
    {{-- Map Container --}}
    <div wire:ignore id="schoolMap" class="min-h-full h-96 w-full absolute inset-0"></div>
    

    {{-- Optional: Map Controls Overlay --}}
    <div class="absolute bottom-4 right-4 flex gap-2 z-[400]">
        <button type="button"
                id="zoomInButton"
                class="p-2 bg-white rounded-lg shadow-lg hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
        </button>
        <button type="button"
                id="zoomOutButton"
                class="p-2 bg-white rounded-lg shadow-lg hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" />
            </svg>
        </button>
        <button type="button"
                id="recenterButton"
                class="p-2 bg-white rounded-lg shadow-lg hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
            </svg>
        </button>
    </div>
</div>

@push('head-css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.css" />
    <style>
        /* Custom Leaflet Controls Styling */
        .leaflet-control-zoom {
            display: none; /* Hide default zoom controls */
        }

        .leaflet-control-attribution {
            background-color: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(4px);
            border-radius: 4px !important;
            margin: 10px !important;
        }
    </style>
@endpush

@push('footer-scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Initialize map with better defaults
            const map = L.map("schoolMap", {
                zoomControl: false,
                scrollWheelZoom: true, // Enable scroll zoom since map is larger now
                dragging: true, // Enable dragging for better interaction
                minZoom: 3,
                maxZoom: 18
            }).setView([{{ $latitude }}, {{ $longitude }}], {{ $zoom }});

            // Add custom tile layer with better styling
            L.tileLayer("https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png", {
                attribution: "&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a>",
                subdomains: "abcd",
                maxZoom: 19
            }).addTo(map);

            // Custom marker icon with better styling
            const schoolIcon = L.divIcon({
                html: `<div class="w-8 h-8 bg-white rounded-full shadow-lg border-2 border-blue-500 flex items-center justify-center transform -translate-y-1/2">
                    <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                    </svg>
                </div>`,
                className: "",
                iconSize: [32, 32],
                iconAnchor: [16, 32]
            });

            // Add marker with enhanced popup
            const marker = L.marker([{{ $latitude }}, {{ $longitude }}], {
                icon: schoolIcon
            }).addTo(map);

            // Custom popup with better styling
            const popupContent = `
                <div class="p-3 min-w-[200px]">
                    <h3 class="font-semibold text-gray-900">{{ $name }}</h3>
                    <p class="text-sm text-gray-600 mt-1">{{ $address }}</p>
                    <div class="mt-2 pt-2 border-t border-gray-200">
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $latitude }},{{ $longitude }}"
                           target="_blank"
                           class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Open in Google Maps
                        </a>
                    </div>
                </div>
            `;

            marker.bindPopup(popupContent, {
                maxWidth: 300,
                className: "custom-popup"
            });

            // Custom controls handlers
            document.getElementById("zoomInButton").addEventListener("click", () => map.zoomIn());
            document.getElementById("zoomOutButton").addEventListener("click", () => map.zoomOut());
            document.getElementById("recenterButton").addEventListener("click", () => {
                map.setView([{{ $latitude }}, {{ $longitude }}], {{ $zoom }});
            });
        });
    </script>
@endpush
