<section>

    <div class="md:flex gap-x-4">

        <div class="w-auto">

            <label class="block text-sm font-medium mb-1" for="country_id">{{ __('Country') }} <span
                    class="text-rose-500">*</span></label>
            <select wire:model.live="country_id" id="country_id" name="country_id" class="form-input w-full">
                <option value="">{{ __('Select Country...') }}</option>
                @if(!empty($countries))
                    @foreach($countries as $key => $item)
                        <option value="{{ $key }}">{{ $item }}</option>
                    @endforeach
                @endif
            </select>

        </div>

    </div>

    @if(!empty($federations))

        <div class="w-auto mt-4">
            <label class="block text-sm font-medium mb-1">{{ __('Underwater Activities') }} <span class="text-rose-500">*</span></label>

            <div class="flex information-box items-center w-full mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="currentColor" class="hidden md:block  w-6 h-6 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                </svg>

                <p class="text-sm">{{ __('Choose the underwater activities you want to be associated with. This choice will send your registration for approval to the respective national organizations of the selected country.') }}</p>
            </div>
            
            <div class="flex flex-col">
                @foreach($federations as $index => $federation)
                    <label class="inline-flex items-center py-2">
                        <input
                            type="checkbox"
                            name="federation_id[]"
                            value="{{ $federation->id }}"
                            wire:model.live="federation_id_array.{{ $index }}"
                            class="form-checkbox h-5 w-5 text-primary-600">
                        <span class="ml-2 text-sm">{{ $federation->membership }}</span>
                    </label>
                @endforeach
            </div>
        </div>


        @if(!empty($local_federations) && $local_federations->count() > 0)
            <div class="w-auto mt-4">
                <label class="block text-sm font-medium mb-1">{{ __('National Organization') }}</label>
                <div class="flex flex-wrap gap-x-4 gap-y-2">
                    @foreach($local_federations as $index => $local_federation)
                        <label class="inline-flex items-center">
                            <input
                                type="checkbox"
                                name="local_federation_id[]"
                                value="{{ $index }}"
                                wire:model.live="local_federation_id_array.{{ $index }}"
                                class="form-checkbox h-5 w-5 text-primary-600">
                            <span class="ml-2 text-sm">{{ $local_federation }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

    @endif

    <div class="w-full">
        @if($error_message)
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4" role="alert">
                <strong class="font-bold">{{ __('No results!') }}</strong>
                <span class="block sm:inline">{{ $error_message }}</span>
            </div>
        @endif
    </div>

</section>
