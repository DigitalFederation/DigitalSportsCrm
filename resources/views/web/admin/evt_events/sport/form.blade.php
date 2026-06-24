<section class="w-full card">
    <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 w-full">
        <div class="sm:w-1/2">
            <label class="block text-sm font-medium mb-1" for="name">{{ __('main.name') }} <span
                    class="text-rose-500">*</span></label>
            <input type="text" name="name" id="name"
                   class="form-input w-full {{ $errors->has('name') ? 'border-rose-300' : '' }}"
                   value="{{ old('name', $sport->name) }}" required>
            @if($errors->has('name'))
                <div class="text-xs mt-1 text-rose-500 h-2">{{ $errors->first('name') }}</div>
            @endif
        </div>

        <div class="sm:w-1/2">
            <label for="sport_type" class="block text-sm font-medium mb-1">{{ __('sports.sport_type') }} <span
                    class="text-rose-500">*</span></label>
            <select name="sport_type" id="sport_type"
                    class="form-select w-full {{ $errors->has('sport_type') ? 'border-rose-300' : '' }}" required>
                <option value="" selected disabled>-- {{ __('sports.sport_type') }} --</option>
                <option value="individual" {{ old('sport_type', $sport->sport_type) === 'individual' ? 'selected' : '' }}>
                    {{ __('sports.individual') }}
                </option>
                <option value="team" {{ old('sport_type', $sport->sport_type) === 'team' ? 'selected' : '' }}>
                    {{ __('sports.team') }}
                </option>
            </select>
            @if($errors->has('sport_type'))
                <div class="text-xs mt-1 text-rose-500">{{ $errors->first('sport_type') }}</div>
            @endif
        </div>
    </div>

    <div class="mt-4">
        <x-forms.card-form-submit :justBack="true" :buttonText="__('Save record')" />
    </div>
</section>
