<section class="w-full card">
    <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4  w-full">
        <div class="w-full">
            <label class="block text-sm font-medium mb-1" for="title">{{ __('Title') }} <span
                    class="text-rose-500">*</span></label>
            <input type="text" name="title" id="title"
                   class="form-input w-full {{ $errors->has('title') ? 'border-rose-300' : '' }}"
                   value="{{ old('title', $ageGroup->title) }}" required>
            @if($errors->has('title'))
                <div class="text-xs mt-1 text-rose-500 h-2">{{ $errors->first('title') }}</div>
            @endif
        </div>
    </div>

    <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5 w-full">
        <div class="sm:w-1/3">
            <label for="sport_id" class="block text-sm font-medium mb-1">{{ __('Sport') }} <span
                    class="text-rose-500">*</span></label>
            <select name="sport_id" id="sport_id"
                    class="form-select w-full {{ $errors->has('sport_id') ? 'border-rose-300' : '' }}" required>
                <option value="" selected disabled>{{ __('-- Select Sport --') }}</option>
                @foreach($sports as $sport)
                    <option
                        value="{{ $sport->id }}" {{ old('sport_id', $ageGroup->sport_id) == $sport->id ? 'selected' : '' }}>{{ $sport->name }}</option>
                @endforeach
            </select>
            @if($errors->has('sport_id'))
                <div class="text-xs mt-1 text-rose-500">{{ $errors->first('sport_id') }}</div>
            @endif
        </div>

        <div class="sm:w-1/3">
            <label for="birthday_start" class="block text-sm font-medium mb-1">{{ __('Birthday Start') }} <span
                    class="text-rose-500">*</span></label>
            <input type="date" name="birthday_start" id="birthday_start"
                   class="form-input w-full {{ $errors->has('birthday_start') ? 'border-rose-300' : '' }}"
                   value="{{ old('birthday_start', $sportAgeGroup->birthday_start ? $sportAgeGroup->birthday_start->format('Y-m-d') : '') }}"
                   required>
            @if($errors->has('birthday_start'))
                <div class="text-xs mt-1 text-rose-500">{{ $errors->first('birthday_start') }}</div>
            @endif
        </div>

        <div class="sm:w-1/3">
            <label for="birthday_end" class="block text-sm font-medium mb-1">{{ __('Birthday End') }} <span
                    class="text-rose-500">*</span></label>
            <input type="date" name="birthday_end" id="birthday_end"
                   class="form-input w-full {{ $errors->has('birthday_end') ? 'border-rose-300' : '' }}"
                   value="{{ old('birthday_end', $sportAgeGroup->birthday_end ? $sportAgeGroup->birthday_end->format('Y-m-d') : '') }}"
                   required>
            @if($errors->has('birthday_end'))
                <div class="text-xs mt-1 text-rose-500">{{ $errors->first('birthday_end') }}</div>
            @endif
        </div>
    </div>

    <div class="mt-4">
        <x-forms.card-form-submit :justBack="true" :buttonText="__('Save record')" />
    </div>
</section>
