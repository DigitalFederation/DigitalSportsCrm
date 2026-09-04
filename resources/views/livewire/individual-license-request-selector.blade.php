<div class="flex flex-col md:flex-row gap-x-2">

    <div class="sm:w-1/3">
        <label for="federation_id" class="block text-sm font-medium mb-1">{{ __('Federation') }}</label>
        <select wire:model.live="selectedFederationId" name="federation_id" id="federation_id"
                class="form-select w-full" required>
            <option hidden selected>Select Federation...</option>
            @foreach($federations as $federation)
                <option value="{{ $federation->id }}"
                        @if(old('federation_id') == $federation->id) selected @endif>{{ $federation->name }}</option>
            @endforeach
        </select>

        @if($errors->has('federation_id'))
            <div class="text-xs mt-1 text-rose-500 h-2">
                {{ $errors->first('federation_id') }}
            </div>
        @endif
    </div>


    @if(!empty($licenses))
        <div class="sm:w-1/3">
            <label for="license_id" class="block text-sm font-medium mb-1">{{ __('License') }}</label>
            <select wire:model.live="selectedLicenseId" name="license_id" id="license_id" class="form-select w-full"
                    required>
                <option hidden selected>Select License...</option>
                @foreach($licenses as $license)
                    <option value="{{ $license->id }}">{{ $license->name }}</option>
                @endforeach
            </select>

            @if($errors->has('license_id'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('license_id') }}
                </div>
            @endif
        </div>
        @if($selectedLicenseCost !== null)
            <div class="sm:w-1/3">
                <label class="block text-sm font-medium mb-1">{{ __('Total Cost') }}</label>
                <div class="bg-gray-100 p-2 rounded">
                    {{ money($selectedLicenseCost) }}
                    <p class="text-xs mt-2">
                        {{ __('Please proceed to payment after submitting the request to activate your license.') }}
                    </p>
                </div>
            </div>
        @endif
    @endif

</div>

