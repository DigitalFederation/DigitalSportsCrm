<div class="flex flex-col md:flex-row gap-4">
    <div class="w-full">
        <label class="block text-sm font-medium mb-1" for="mainFederation_id">
            {{ __('National Federation') }}
            <span
                class="text-rose-500">*</span></label>
        <select id="mainFederation_id"
                class="form-select w-full {{ $errors->has('mainFederation_id') ? 'border-rose-300' : '' }}"
                wire:model.live="selectedMainFederation" required>
            <option value="0" selected>{{ __('-- Select option -- ') }}</option>
            @foreach($federations as $federation)
                <option value="{{ $federation->id }}"
                        @if($federation->id == old('main_federation_id')) selected @endif>{{ $federation->name }}</option>
            @endforeach
        </select>
        @if($errors->has('federation_id'))
            <div class="text-xs mt-1 text-rose-500 h-2">
                {{ $errors->first('federation_id') }}
            </div>
        @endif

        <div wire:loading>Loading...</div>
    </div>


    <div class="w-full">
        <label class="block text-sm font-medium mb-1" for="local_id">{{ __('National Organization') }}</label>
        <select id="local_id" class="form-select w-full {{ $errors->has('federation_id') ? 'border-rose-300' : '' }}"
                wire:model.live="selectedLocalFederation">
            <option value="{{ $selectedMainFederation }}" selected>{{ __('-- Select option -- ') }}</option>
            @foreach($localFederations as $localFederation)
                <option value="{{ $localFederation->id }}"
                        @if($localFederation->id == old('federation_id')) selected @endif>{{ $localFederation->name }}</option>
            @endforeach
        </select>
        @if($errors->has('federation_id'))
            <div class="text-xs mt-1 text-rose-500 h-2">
                {{ $errors->first('federation_id') }}
            </div>
        @endif
    </div>


    <input type="hidden" name="federation_id" value="{{ $selectedLocalFederation ?? $selectedMainFederation }}">
</div>
