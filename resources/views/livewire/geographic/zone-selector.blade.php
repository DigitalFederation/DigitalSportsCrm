<div>
    <div class="space-y-4">
        <!-- Zone Selection -->
        <div>
            <label class="block text-sm font-medium mb-2">
                {{ $label }}
                @if($required)
                    <span class="text-rose-500">*</span>
                @endif
            </label>

            <!-- Zone List -->
            @if($availableZones->count() > 0)
                <div class="max-h-60 overflow-y-auto border rounded-md p-3 space-y-2">
                    @foreach($availableZones as $zone)
                        <label class="flex items-start space-x-3 p-2 hover:bg-gray-50 rounded cursor-pointer">
                            <input
                                type="{{ $allowMultiple ? 'checkbox' : 'radio' }}"
                                wire:model.live="{{ $allowMultiple ? 'selectedZoneIds' : 'selectedZoneId' }}"
                                value="{{ $zone->id }}"
                                class="form-{{ $allowMultiple ? 'checkbox' : 'radio' }} mt-1"
                                @if($required && !$allowMultiple) required @endif
                            >
                            <div class="flex-1 min-w-0">
                                <span class="text-sm font-medium text-gray-900">
                                    {{ $zone->name }}
                                </span>
                            </div>
                        </label>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    {{ __('geographic.no_zones_available') }}
                </div>
            @endif

            @if($allowMultiple && $selectedZoneIds)
                <p class="text-sm text-gray-500 mt-2">
                    {{ trans_choice(__('geographic.zone_selected'), count($selectedZoneIds), ['count' => count($selectedZoneIds)]) }}
                </p>
            @endif
        </div>

        <!-- Selected Zones Display (for multiple selection) -->
        @if($allowMultiple && $selectedZoneIds)
            <div class="mt-4">
                <h4 class="text-sm font-medium mb-2">{{ __('geographic.selected_zones') }}:</h4>
                <div class="flex flex-wrap gap-2">
                    @foreach($this->getSelectedZones() as $zone)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                            {{ $zone->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Hidden inputs for form submission -->
    @if($selectedZoneIds)
        @foreach($selectedZoneIds as $zoneId)
            <input type="hidden" name="zone_ids[]" value="{{ $zoneId }}">
        @endforeach
    @endif
</div>