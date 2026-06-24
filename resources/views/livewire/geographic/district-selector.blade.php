<div>
    <div class="space-y-4">
        <!-- Country Selection (if enabled) -->
        @if($showCountrySelector && $availableCountries->count() > 0)
            <div>
                <label class="block text-sm font-medium mb-2">
                    {{ __('geographic.country') }}
                    @if($required)
                        <span class="text-rose-500">*</span>
                    @endif
                </label>
                <select wire:model.live="selectedCountryId" class="form-select w-full">
                    <option value="">{{ __('geographic.select_country') }}</option>
                    @foreach($availableCountries as $country)
                        <option value="{{ $country->id }}">
                            {{ $country->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <!-- District Selection -->
        @if($selectedCountryId || !$showCountrySelector)
            <div>
                <label class="block text-sm font-medium mb-2">
                    {{ $label }}
                    @if($required)
                        <span class="text-rose-500">*</span>
                    @endif
                </label>

                <!-- District List -->
                @if($availableDistricts->count() > 0)
                    <div class="space-y-2">
                        @if($availableDistricts->count() <= 10)
                            <!-- Radio buttons for small lists -->
                            @foreach($availableDistricts as $district)
                                <label class="flex items-start space-x-3 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input
                                        type="radio"
                                        wire:model.live="selectedDistrictId"
                                        value="{{ $district->id }}"
                                        class="form-radio mt-1"
                                        @if($required) required @endif
                                    >
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ $district->name }}
                                            </span>
                                            @if($district->code)
                                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                                                    {{ $district->code }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($district->description)
                                            <p class="text-xs text-gray-500 mt-1">{{ $district->description }}</p>
                                        @endif
                                        <div class="flex items-center space-x-4 mt-1 text-xs text-gray-500">
                                            <span>{{ $district->entities_count }} {{ __('geographic.entities') }}</span>
                                            <span>{{ $district->federations_count }} {{ __('geographic.federations') }}</span>
                                            <span>{{ $district->individuals_count }} {{ __('geographic.individuals') }}</span>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        @else
                            <!-- Dropdown for large lists -->
                            <select wire:model.live="selectedDistrictId" class="form-select w-full" @if($required) required @endif>
                                <option value="">{{ __('geographic.select_district') }}</option>
                                @foreach($availableDistricts as $district)
                                    <option value="{{ $district->id }}">
                                        {{ $district->name }}
                                        @if($district->code) ({{ $district->code }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        @if($selectedCountryId)
                            {{ __('geographic.no_districts_for_country') }}
                        @else
                            {{ __('geographic.no_districts_available') }}
                        @endif
                    </div>
                @endif
            </div>
        @endif

        <!-- Selected District Display -->
        @if($selectedDistrictId)
            @php $selectedDistrict = $this->getSelectedDistrict(); @endphp
            @if($selectedDistrict)
                <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                    <h4 class="text-sm font-medium text-blue-900 mb-1">{{ __('geographic.selected_district') }}:</h4>
                    <div class="text-sm text-blue-800">
                        <span class="font-medium">{{ $selectedDistrict->name }}</span>
                        @if($selectedDistrict->code)
                            <span class="text-blue-600">({{ $selectedDistrict->code }})</span>
                        @endif
                        <div class="text-xs text-blue-600 mt-1">
                            {{ __('geographic.country') }}: {{ $selectedDistrict->country->name }}
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>

    <!-- Hidden input for form submission -->
    @if($selectedDistrictId)
        <input type="hidden" name="district_id" value="{{ $selectedDistrictId }}">
    @endif
</div>