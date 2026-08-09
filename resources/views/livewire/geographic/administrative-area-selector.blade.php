<div class="contents">
    @if($availableZones->isNotEmpty())
        <div>
            <label class="block text-sm font-medium mb-1" for="administrative_zone_id">
                {{ __('geographic.zone') }}
                @if($required)<span class="text-rose-500">*</span>@endif
            </label>
            <select
                id="administrative_zone_id"
                name="administrative_zone_id"
                wire:model.live="selectedZoneId"
                class="form-select w-full {{ $errors->has('administrative_zone_id') ? 'border-rose-300' : '' }}"
                @if($required && $selectedDistrictId !== $notListedValue) required @endif
            >
                <option value="">{{ __('common.select_option') }}</option>
                @foreach($availableZones as $zone)
                    <option value="{{ $zone->id }}">{{ $zone->name }} ({{ $zone->code }})</option>
                @endforeach
            </select>
            @error('administrative_zone_id')
                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
            @enderror
        </div>
    @endif

    <div>
        <label class="block text-sm font-medium mb-1" for="district_id">
            {{ __('geographic.district') }}
            @if($required)<span class="text-rose-500">*</span>@endif
        </label>
        <select
            id="district_id"
            name="district_id"
            wire:model.live="selectedDistrictId"
            class="form-select w-full {{ $errors->has('district_id') ? 'border-rose-300' : '' }}"
            @if($required) required @endif
        >
            <option value="">{{ __('common.select_option') }}</option>
            @if($allowNotListed)
                <option value="{{ $notListedValue }}">{{ __('main.territory_not_listed') }}</option>
            @endif
            @foreach($availableDistricts as $district)
                <option value="{{ $district->id }}">{{ $district->name }}</option>
            @endforeach
        </select>
        @if($availableZones->isNotEmpty() && !$selectedZoneId && $selectedDistrictId !== $notListedValue)
            <p class="text-xs mt-1 text-slate-500">{{ __('geographic.select_zone_first') }}</p>
        @endif
        @error('district_id')
            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
        @enderror
    </div>
</div>
