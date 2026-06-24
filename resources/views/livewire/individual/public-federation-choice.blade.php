<div class="space-y-4">
    {{-- Main Information Box --}}
    <div class="flex p-4 bg-white rounded-lg border-2 border-blue-300 information-box items-start w-full mb-4">
        <svg xmlns="http://www.w3.org/2000/svg"
             class="h-6 w-6 mr-4 text-blue-600 flex-shrink-0 mt-0.5"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor"
             stroke-width="1.5">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="space-y-2">
            <p class="text-sm leading-relaxed text-slate-600">
                {{ __('Choose the national federation you want to join as a member. We will send a request for affiliation to the national federation and organisation you have selected.') }}
            </p>
        </div>
    </div>

    {{-- Steps Indicator --}}
    <div class="flex items-center justify-between mb-6 px-2">
        <div class="flex items-center">
            <span class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-medium">1</span>
            <span class="ml-2 text-sm font-medium @if(!$country_id) text-blue-600 @else text-gray-500 @endif">{{ __('Country') }}</span>
        </div>
        <div class="h-0.5 w-10 bg-gray-200"></div>
        <div class="flex items-center">
            <span class="w-8 h-8 rounded-full @if($country_id) bg-blue-600 text-white @else bg-gray-200 text-gray-400 @endif flex items-center justify-center text-sm font-medium">2</span>
            <span class="ml-2 text-sm font-medium @if($country_id && !$committee_id) text-blue-600 @else text-gray-500 @endif">{{ __('Committee') }}</span>
        </div>
        <div class="h-0.5 w-10 bg-gray-200"></div>
        <div class="flex items-center">
            <span class="w-8 h-8 rounded-full @if($committee_id) bg-blue-600 text-white @else bg-gray-200 text-gray-400 @endif flex items-center justify-center text-sm font-medium">3</span>
            <span class="ml-2 text-sm font-medium @if($committee_id && !$main_federation_id) text-blue-600 @else text-gray-500 @endif">{{ __('Federation') }}</span>
        </div>
    </div>

    {{-- First Row --}}
    <div class="grid grid-cols-1 gap-4">
        {{-- Country Selection --}}
        <div>
            <label class="block text-sm font-medium mb-1" for="country_id">
                {{ __('Select Country') }} <span class="text-red-500">*</span>
            </label>
            <select
                wire:model.live="country_id"
                id="country_id"
                class="form-select w-full"
                required
            >
                <option value="">{{ __('Choose a country to begin') }}</option>
                @foreach($countries as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Committee Selection with Helper Text --}}
        <div>
            <label class="block text-sm font-medium mb-1" for="committee_id">
                {{ __('Select Committee') }} <span class="text-red-500">*</span>
            </label>
            <select
                wire:model.live="committee_id"
                id="committee_id"
                class="form-select w-full"
                @if(empty($country_id)) disabled @endif
                required
            >
                <option value="">{{ empty($country_id) ? __('First select a country above') : __('Select your committee') }}</option>
                @foreach($committees as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
            @if(empty($country_id))
                <p class="mt-1 text-sm text-gray-500">{{ __('Please select a country first to view available committees') }}</p>
            @endif
        </div>
    </div>

    {{-- Second Row with Helper Text --}}
    <div class="grid grid-cols-1 gap-4">
        @if($has_federations)
            {{-- Main Federation Selection --}}
            <div>
                <label class="block text-sm font-medium mb-1" for="main_federation_id">
                    {{ __('National Federation') }} <span class="text-red-500">*</span>
                </label>
                <select
                    wire:model.live="main_federation_id"
                    id="main_federation_id"
                    class="form-select w-full"
                    @if(empty($committee_id)) disabled @endif
                    required
                >
                    <option value="">{{ empty($committee_id) ? __('First select a committee above') : __('Choose your national federation') }}</option>
                    @foreach($main_federations as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
                @if(empty($committee_id))
                    <p class="mt-1 text-sm text-gray-500">{{ __('Please select a committee first to view available federations') }}</p>
                @endif
            </div>

            {{-- Local Federation Selection with Context --}}
            @if(!empty($local_federations))
                <div>
                    <label class="block text-sm font-medium mb-1" for="local_federation_id">
                        {{ __('National Organization') }} <span class="text-sm text-gray-500">({{ __('Optional') }})</span>
                    </label>
                    <select
                        wire:model.live="local_federation_id"
                        id="local_federation_id"
                        class="form-select w-full"
                        @if(empty($main_federation_id)) disabled @endif
                    >
                        <option value="">{{ __('Select a national organization if applicable') }}</option>
                        @foreach($local_federations as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">{{ __('National organizations are optional sub-organizations within your national federation') }}</p>
                </div>
            @endif
        @else
            {{-- Hidden input to maintain the default federation ID --}}
            <input type="hidden" wire:model="main_federation_id">
        @endif
    </div>

    {{-- Hidden fields to pass values to the form --}}
    <input type="hidden" name="country_id" value="{{ $country_id }}">
    <input type="hidden" name="committee_id" value="{{ $committee_id }}">
    <input type="hidden" name="federation_id[]" value="{{ $main_federation_id }}">
    @if($local_federation_id)
        <input type="hidden" name="federation_id[]" value="{{ $local_federation_id }}">
    @endif
</div>
