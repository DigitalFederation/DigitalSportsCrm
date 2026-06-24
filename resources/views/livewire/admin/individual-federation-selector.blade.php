<div>
    @if(!$isFederationFlow)
        <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
            <div class="w-full">
                <label class="block text-sm font-medium mb-1" for="main_federation_id">
                    {{ __('Main Federation') }}
                    <span class="text-rose-500">*</span>
                </label>
                <select wire:model.live="mainFederationId" class="form-select w-full" required>
                    <option value="">{{ __('-- Select Main Federation --') }}</option>
                    @foreach($availableMainFederations as $federation)
                        <option value="{{ $federation->id }}">
                            {{ $federation->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif

    @if(count($availableLocalFederations) > 0)
        <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
            <div class="w-full">
                <label class="block text-sm font-medium mb-1">
                    {{ __('Local Federations') }}
                    <span class="text-rose-500">*</span>
                </label>
                <div class="space-y-2 mt-2 max-h-60 overflow-y-auto border rounded-md p-3">
                    @foreach($availableLocalFederations as $federation)
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                wire:model.live="localFederationIds"
                                value="{{ $federation->id }}"
                                class="form-checkbox"
                            >
                            <span class="ml-2 text-sm">{{ $federation->name }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-sm text-gray-500 mt-1">{{ __('Select all applicable local federations') }}</p>
            </div>
        </div>
    @endif

    {{-- Hidden inputs for form submission --}}
    <input type="hidden" name="federation_id[]" value="{{ $mainFederationId }}">
    @foreach($localFederationIds as $federationId)
        <input type="hidden" name="federation_id[]" value="{{ $federationId }}">
    @endforeach
</div>