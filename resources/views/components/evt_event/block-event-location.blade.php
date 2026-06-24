@php
    // Build address for map search if no specific location_url provided
    $mapAddress = implode(', ', array_filter([
        $event->venue,
        $event->venue_address,
        $event->venue_city,
        optional($event->venueCountry)->name
    ]));
    $encodedAddress = urlencode($mapAddress);

    // Extract Google Maps place ID or use address for embed
    $locationUrl = $event->location_url;
    $embedUrl = null;

    if ($locationUrl && str_contains($locationUrl, 'google.com/maps')) {
        // Try to extract place ID or coordinates from the URL
        if (preg_match('/place\/([^\/]+)/', $locationUrl, $matches)) {
            $embedUrl = "https://www.google.com/maps/embed/v1/place?key=" . config('services.google.maps_api_key', '') . "&q=" . urlencode($matches[1]);
        } elseif (preg_match('/@(-?\d+\.?\d*),(-?\d+\.?\d*)/', $locationUrl, $matches)) {
            $embedUrl = "https://www.google.com/maps/embed/v1/view?key=" . config('services.google.maps_api_key', '') . "&center={$matches[1]},{$matches[2]}&zoom=15";
        }
    }

    // Fallback: use search by address if we have address info
    if (!$embedUrl && $mapAddress && config('services.google.maps_api_key')) {
        $embedUrl = "https://www.google.com/maps/embed/v1/place?key=" . config('services.google.maps_api_key') . "&q=" . $encodedAddress;
    }

    $hasLocationInfo = $event->venue || $event->venue_address || $event->venue_city || optional($event->venueCountry)->name;
@endphp

<div class="card h-full">
    <div class="flex gap-x-2 items-center border-b border-gray-300 pb-2 mb-4">
        <x-svg.geo-alt class="w-6 h-6 text-slate-600" />
        <span class="font-bold">{{ __('events.event_location') }}</span>
    </div>

    @if($hasLocationInfo)
        <div class="flex flex-col gap-4">
            {{-- Location Details Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                @if($event->venue)
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wide">{{ __('events.venue_name') }}</p>
                        <p class="text-base text-slate-700 font-medium">{{ $event->venue }}</p>
                    </div>
                @endif

                @if($event->venue_city)
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wide">{{ __('events.venue_city') }}</p>
                        <p class="text-base text-slate-600">{{ $event->venue_city }}</p>
                    </div>
                @endif

                @if($event->venue_address)
                    <div class="sm:col-span-2">
                        <p class="text-xs text-slate-400 uppercase tracking-wide">{{ __('events.venue_address') }}</p>
                        <p class="text-base text-slate-600">{{ $event->venue_address }}</p>
                    </div>
                @endif

                @if(optional($event->venueCountry)->name)
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wide">{{ __('events.venue_country') }}</p>
                        <p class="text-base text-slate-600">{{ $event->venueCountry->name }}</p>
                    </div>
                @endif
            </div>

            {{-- Map Preview --}}
            @if($embedUrl)
                <div class="w-full">
                    <div class="aspect-video rounded-lg overflow-hidden bg-slate-100 shadow-inner">
                        <iframe
                            src="{{ $embedUrl }}"
                            width="100%"
                            height="100%"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            class="w-full h-full"
                        ></iframe>
                    </div>
                </div>
            @elseif($mapAddress)
                {{-- Static map fallback without API key --}}
                <div class="w-full">
                    <a href="https://www.google.com/maps/search/{{ $encodedAddress }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="block aspect-video rounded-lg overflow-hidden bg-gradient-to-br from-slate-100 to-slate-200 relative group">
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-500 group-hover:text-indigo-600 transition-colors">
                            <x-heroicon-o-map class="w-12 h-12 mb-2" />
                            <span class="text-sm font-medium">{{ __('events.click_to_view_map') }}</span>
                        </div>
                    </a>
                </div>
            @endif

            {{-- View on Google Maps Link --}}
            @if($locationUrl || $mapAddress)
                <div>
                    <a href="{{ $locationUrl ?: 'https://www.google.com/maps/search/' . $encodedAddress }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-800 transition-colors">
                        <x-heroicon-o-map-pin class="w-4 h-4" />
                        <span>{{ __('events.view_on_map') }}</span>
                        <x-heroicon-o-arrow-top-right-on-square class="w-3 h-3" />
                    </a>
                </div>
            @endif
        </div>
    @else
        <x-utility.no-data :inCard="true" />
    @endif
</div>
