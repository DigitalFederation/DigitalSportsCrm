{{-- public/location-map/partials/_assets.blade.php --}}
@push('head-css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.css" />
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.css" />
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.Default.css" />
<style>

    /* Updated z-index hierarchy */
    :root {
        --z-base: 0;
        --z-map: 10;
        --z-map-controls: 15;
        --z-filters: 20;
        --z-controls: 30;
        --z-drawer: 40;
        --z-modal: 60;
        --z-loading: 60;
    }

    /* Force map to stay at base level and ensure proper height */
    #map {
        z-index: var(--z-base) !important;
        min-height: 400px;
        height: 100%;
    }

    /* Override Leaflet control z-indices */
    .leaflet-pane {
        z-index: var(--z-map) !important;
    }

    .leaflet-top,
    .leaflet-bottom {
        z-index: var(--z-map-controls) !important;
    }


    /* Custom styles for the map components */
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

    .marker-icon::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 50%;
        transform: translateX(-50%);
        border-left: 8px solid transparent;
        border-right: 8px solid transparent;
        border-top: 8px solid currentColor;
        filter: drop-shadow(0 2px 2px rgb(0 0 0 / 0.1));
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
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .custom-tooltip::before {
        border-top-color: white;
    }
        /* Safe area insets for modern mobile browsers */
    @supports(padding: max(0px)) {
        .pb-safe-area-inset-bottom {
            padding-bottom: max(env(safe-area-inset-bottom), 1rem);
        }
    }

    /* Mobile optimization for markers */
    @media (max-width: 768px) {
        .marker-icon {
            width: 44px; /* Larger touch target */
            height: 44px;
        }

        .custom-tooltip {
            font-size: 16px; /* Larger text for readability */
            max-width: 250px;
        }
    }

    /* Smooth view transitions */
    .view-transition {
        transition: opacity 0.3s ease-in-out;
    }

</style>
@endpush

@push('footer-scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.js"></script>
<script
    src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/leaflet.markercluster.js"></script>
<script>
    document.addEventListener("livewire:init", () => {
        let map, markers, activeMarker;

        function initializeMap() {
            map = L.map("map", {
                minZoom: 3 ,
                maxZoom: 16,
                maxBounds: [
                    [-90, -180],
                    [90, 180]
                ],
                maxBoundsViscosity: 1.0,
                worldCopyJump: true
            }).setView([38.7223, -9.1393], 6);
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: "&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors"
            }).addTo(map);

            markers = L.markerClusterGroup({
                maxClusterRadius: 50,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                zoomToBoundsOnClick: true,
                disableClusteringAtZoom: 18
            });
            map.addLayer(markers);
        }

        function createCustomMarker(location) {
            const type = location.type;
            let markerContent = type === "federation" ? "F" : "E";

            // Add license indicators for entities
            if (type === "entity" && location.licenses && location.licenses.length > 0) {
                markerContent += `<span class="marker-license-count">${location.licenses.length}</span>`;
            }

            const icon = L.divIcon({
                className: `custom-marker ${type}`,
                html: `<div class="marker-icon ${type}">${markerContent}</div>`,
                iconSize: [36, 36],
                iconAnchor: [18, 36]
            });

            const marker = L.marker([location.lat, location.lng], { icon });

            // Enhanced tooltip content
            const name = location.name.length > 25 ? location.name.slice(0, 25) + "…" : location.name;

            let tooltipContent = `<strong>${name}</strong>`;
            // if (location.licenses && location.licenses.length > 0) { // Remove license info
            // tooltipContent += "<br><small>" + location.licenses.join(", ") + "</small>";
            // }

            marker.bindTooltip(tooltipContent, {
                direction: "top",
                offset: [0, -36],
                className: "custom-tooltip"
            });

            return marker;
        }

        function updateMarkers(locations) {
            markers.clearLayers();

            locations.forEach(location => {
                const marker = createCustomMarker(location);

                marker.on("click", () => {
                    @this.
                    showDetails(location.id, location.type);
                });

                markers.addLayer(marker);
            });
        }

        // Initialize the map
        initializeMap();

        // Listen for location updates - Livewire 3 passes args as array
        Livewire.on("updateLocations", (data) => {
            const locations = data.locations || data[0]?.locations || [];
            console.log("Received locations:", locations.length, locations[0]?.type, locations[0]?.name, locations[0]?.lat, locations[0]?.lng);
            updateMarkers(locations);
        });

        // Listen for map center updates - Livewire 3 passes args as array
        Livewire.on("centerMap", (data) => {
            const params = Array.isArray(data) ? data[0] : data;
            if (params && params.lat !== undefined && params.lng !== undefined) {
                map.setView([params.lat, params.lng], params.zoom || 10, {
                    animate: true,
                    duration: 1
                });
            }
        });

        // Add marker highlight handling - Livewire 3 passes args as array
        Livewire.on("highlightMarker", (data) => {
            const params = Array.isArray(data) ? data[0] : data;
            const markerId = `${params.type}-${params.id}`;

            // Reset previous highlight
            if (activeMarker) {
                const element = activeMarker.getElement();
                if (element) {
                    element.classList.remove('scale-125', 'z-50');
                }
            }

            // Find and highlight new marker
            markers.getLayers().forEach(marker => {
                if (marker.options.id === markerId) {
                    activeMarker = marker;
                    const element = marker.getElement();
                    if (element) {
                        element.classList.add('scale-125', 'z-50');
                    }

                    // Ensure marker is visible
                    if (!map.getBounds().contains(marker.getLatLng())) {
                        map.panTo(marker.getLatLng());
                    }
                }
            });
        });


    });
</script>
@endpush
