<div class="space-y-4" x-data="{ sportOpen: false, divingOpen: false, districtOpen: true }">
    {{-- District Filter --}}
    <div class="border-b border-gray-100 dark:border-gray-700">
        <button type="button" @click="districtOpen = !districtOpen"
            class="flex items-center justify-between w-full py-2 text-left">
            <span class="text-sm font-medium text-gray-900 dark:text-white">
                {{ __('location-map.district') }}
            </span>
            <svg class="w-4 h-4 text-gray-500 transition-transform duration-200"
                :class="{ 'rotate-180': districtOpen }" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
        <div x-show="districtOpen" x-collapse class="pb-3">
            <select wire:model.live="selectedDistrict"
                class="form-select w-full text-sm border-gray-200 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">{{ __('location-map.all_districts') }}</option>
                @foreach($this->districts as $district)
                    <option value="{{ $district->id }}">{{ $district->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Sport Clubs Filter (Clubes Desportivos) --}}
    <div class="border-b border-gray-100 dark:border-gray-700">
        <button type="button" @click="sportOpen = !sportOpen"
            class="flex items-center justify-between w-full py-2 text-left">
            <span class="text-sm font-medium text-gray-900 dark:text-white">
                {{ __('location-map.sport_clubs') }}
            </span>
            <svg class="w-4 h-4 text-gray-500 transition-transform duration-200"
                :class="{ 'rotate-180': sportOpen }" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
        <div x-show="sportOpen" x-collapse class="pb-3">
            <select wire:model.live="selectedSportLicense"
                class="form-select w-full text-sm border-gray-200 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">{{ __('location-map.all_sport_licenses') }}</option>
                @foreach($this->sportLicenses as $license)
                    <option value="{{ $license->id }}">{{ $license->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Diving Entities Filter (Entidades de Mergulho) --}}
    <div class="border-b border-gray-100 dark:border-gray-700">
        <button type="button" @click="divingOpen = !divingOpen"
            class="flex items-center justify-between w-full py-2 text-left">
            <span class="text-sm font-medium text-gray-900 dark:text-white">
                {{ __('location-map.diving_entities') }}
            </span>
            <svg class="w-4 h-4 text-gray-500 transition-transform duration-200"
                :class="{ 'rotate-180': divingOpen }" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
        <div x-show="divingOpen" x-collapse class="pb-3">
            <select wire:model.live="selectedDivingLicense"
                class="form-select w-full text-sm border-gray-200 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">{{ __('location-map.all_diving_licenses') }}</option>
                @foreach($this->divingLicenses as $license)
                    <option value="{{ $license->id }}">{{ $license->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Clear Filters Button --}}
    @if($this->selectedSportLicense || $this->selectedDivingLicense || $this->selectedDistrict || $this->searchTerm)
        <div class="pt-2">
            <button type="button" wire:click="clearFilters"
                class="w-full py-2 px-4 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                {{ __('location-map.clear_filters') }}
            </button>
        </div>
    @endif
</div>
