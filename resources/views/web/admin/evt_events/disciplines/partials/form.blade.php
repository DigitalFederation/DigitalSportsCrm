<section class="w-full">
    <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5 w-full">

        <div class="w-full">
            <label class="block text-sm font-medium mb-1" for="name"> {{ __('Discipline Name') }} <span
                    class="text-rose-500">*</span></label>
            <input type="text" name="name" id="name"
                   class="form-input w-full {{ $errors->has('name') ? 'border-rose-300' : '' }}"
                   value="{{old('name',$discipline->name)}}" required>

            @if($errors->has('name'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('name') }}
                </div>
            @endif
        </div>

    </div>

    <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5 w-full">

        <div class="sm:w-1/3">
            <label class="block text-sm font-medium mb-1" for="gender">{{ __('Gender Rules') }} <span
                    class="text-rose-500">*</span></label>
            <select name="gender" id="gender"
                    class="form-input w-full {{ $errors->has('gender') ? 'border-rose-300' : '' }}" required>
                <option value="" selected disabled> {{ __('-- Select an option --') }} </option>
                @foreach($genders as $gender)
                    <option
                        value="{{ $gender->name }}"
                        @if(old('gender', $discipline->gender) == strtolower($gender->name)) selected @endif>
                        {{ $gender->value }}</option>
                @endforeach
            </select>

            @if($errors->has('event_type'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('event_type') }}
                </div>
            @endif
        </div>

        <div class="sm:w-2/3 flex flex-col md:flex-row gap-4 items-start">

            <div class="md:w-1/3">
                <label class="block text-sm font-medium mb-1"
                       for="athlete_limit">{{ __('Athlete Limit') }} <span
                        class="text-rose-500">*</span></label>
                <input type="text" placeholder="{{ __('Enter a number') }}" name="athlete_limit" id="athlete_limit"
                       class="form-input w-full {{ $errors->has('name') ? 'border-rose-300' : '' }}"
                       value="{{ old('athlete_limit', $discipline->athlete_limit) }}">
                <p class="cursor-help text-xs italic text-gray-400 my-1">
                    {{ __('Athlete limit per enrollment for this Discipline') }}
                </p>
            </div>

        </div>

    </div>

    <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5 w-full">

        <div class="md:w-1/3">
            <label class="block text-sm font-medium mb-1"
                   for="enrollment_type">{{ __('Enrollment Type') }} <span class="text-rose-500">*</span></label>
            <select name="enrollment_type"
                    id="enrollment_type"
                    class="form-input w-full {{ $errors->has('enrollment_type') ? 'border-rose-300' : '' }}"
                    required>
                <option value="" selected disabled> {{ __('-- Select an option --') }} </option>

                @foreach($enrollment_types as $value => $label)
                    <option
                        value="{{ $value }}"
                        @selected(old('enrollment_type', $discipline->enrollment_type) === $value)
                    >{{ $label }}</option>
                @endforeach
            </select>

            @if($errors->has('enrollment_type'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('enrollment_type') }}
                </div>
            @endif
        </div>

        <div class="md:w-2/3">
            <x-evt-event.team-composition-input
                :value="old('team_composition_requirements',
                    is_string($discipline->team_composition_requirements)
                        ? $discipline->team_composition_requirements
                        : json_encode($discipline->team_composition_requirements)
                )"
            />
            @if($errors->has('team_composition_requirements'))
                <div class="text-xs mt-1 text-rose-500">
                    {{ $errors->first('team_composition_requirements') }}
                </div>
            @endif
        </div>
    </div>


    <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5 w-full">

        <div class="sm:w-1/3">
            <label for="sports"
                   class="block text-sm font-medium mb-1">{{ __('Sport') }}</label>
            <select name="sport_id"
                    id="sport_id"
                    class="form-select w-full {{ $errors->has('licenses') ? 'border-rose-300' : '' }}">
                @foreach($sports as $sport)
                    <option
                        value="{{ $sport->id }}" {{ old('sport_id', $discipline->sport_id) == $sport->id ? 'selected' : '' }}>
                        {{ $sport->name }}
                    </option>
                @endforeach
            </select>
            @if($errors->has('licenses'))
                <div class="text-xs mt-1 text-rose-500">
                    {{ $errors->first('licenses') }}
                </div>
            @endif
            <p class="text-xs mt-1 text-gray-500">{{ __('Associated Sport') }}</p>
        </div>

        <div class="sm:w-1/3">
            <label for="sport_age_groups" class="block text-sm font-medium mb-1">{{ __('Age Groups') }}</label>

            <livewire:input.select-multiple
                :key="'age-groups-select'"
                :items="$ageGroups->pluck('title', 'id')->toArray()"
                inputId="sport_age_groups"
                inputName="sport_age_groups[]"
                :inputSelected="old('sport_age_groups', $discipline->sportAgeGroups->pluck('id')->toArray())"
                :multiple="true" />

            @if($errors->has('sport_age_groups'))
                <div class="text-xs mt-1 text-rose-500">
                    {{ $errors->first('sport_age_groups') }}
                </div>
            @endif

            <p class="text-xs mt-1 text-gray-500">{{ __('Hold down "Ctrl" or "Command" to select multiple options.') }}</p>
        </div>

    </div>

    <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5 w-full">
        <div class="sm:w-1/2">
            <label class="block text-sm font-medium mb-1" for="distance">{{ __('Distance') }}</label>
            <input type="text"
                   name="distance"
                   id="distance"
                   placeholder="{{ __('e.g., 100m, 200m, etc.') }}"
                   class="form-input w-full {{ $errors->has('distance') ? 'border-rose-300' : '' }}"
                   value="{{ old('distance', $discipline->distance) }}">

            @if($errors->has('distance'))
                <div class="text-xs mt-1 text-rose-500">
                    {{ $errors->first('distance') }}
                </div>
            @endif
            <p class="text-xs mt-1 text-gray-500">{{ __('Specify the distance for this discipline (if applicable)') }}</p>
        </div>

        <div class="sm:w-1/2">
            <label class="block text-sm font-medium mb-1" for="style">{{ __('Style') }}</label>
            <input type="text"
                   name="style"
                   id="style"
                   placeholder="{{ __('e.g., Freestyle, etc.') }}"
                   class="form-input w-full {{ $errors->has('style') ? 'border-rose-300' : '' }}"
                   value="{{ old('style', $discipline->style) }}">

            @if($errors->has('style'))
                <div class="text-xs mt-1 text-rose-500">
                    {{ $errors->first('style') }}
                </div>
            @endif
            <p class="text-xs mt-1 text-gray-500">{{ __('Specify the style for this discipline (if applicable)') }}</p>
        </div>
    </div>

    <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5 w-full">
        <!-- Licenses Selection -->
        <div class="sm:w-1/2">
            <label for="licenses"
                   class="block text-sm font-medium mb-1">{{ __('Required Licenses') }}</label>

            <livewire:input.select-multiple
                :key="'licenses-select'"
                :items="$licenses->pluck('name', 'id')->toArray()"
                inputId="licenses"
                inputName="licenses[]"
                :inputSelected="old('licenses', $discipline->licenses->pluck('id')->toArray())"
                :multiple="true" />

            @if($errors->has('licenses'))
                <div class="text-xs mt-1 text-rose-500">
                    {{ $errors->first('licenses') }}
                </div>
            @endif
            <p class="text-xs mt-1 text-gray-500">{{ __('Hold down "Ctrl" or "Command" to select multiple options.') }}</p>
        </div>

        <div class="sm:w-1/2">
            <label for="attributes"
                   class="block text-sm font-medium mb-1">{{ __('Available Attributes') }}</label>

            <livewire:input.select-multiple
                :key="'discipline-attrs-select'"
                :items="$attributes->pluck('name', 'id')->toArray()"
                inputId="discipline_attrs"
                inputName="discipline_attrs[]"
                :inputSelected="old('discipline_attrs', $discipline->attributes->pluck('id')->toArray())"
                :multiple="true" />

            @if($errors->has('attributes'))
                <div class="text-xs mt-1 text-rose-500">
                    {{ $errors->first('attributes') }}
                </div>
            @endif
            <p class="text-xs mt-1 text-gray-500">{{ __('Hold down "Ctrl" or "Command" to select multiple options.') }}</p>
        </div>
    </div>

    <div class="mt-4">
        <x-forms.card-form-submit :justBack="true" :buttonText="__('Save record')" />
    </div>
</section>
