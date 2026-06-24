<div class="card">
    <div class="flex gap-x-2 items-center border-b border-gray-300 pb-2 mb-4">
        <x-svg.calendar-event class="w-8 h-8 text-slate-600" />
        <span class="font-bold">{{ __('Event Information') }}</span>
    </div>
    <div class="flex flex-col md:flex-row w-full gap-4">
        <div class="w-full">
            <label class="block text-sm font-medium mb-1" for="name">
                {{ __('Event Name') }} <span class="text-rose-500">*</span>
            </label>
            <input
                type="text"
                name="name"
                id="name"
                class="form-input w-full {{ $errors->has('name') ? 'border-rose-300' : '' }}"
                value="{{old('name',$event->name)}}"
                required>

            @if($errors->has('name'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('name') }}
                </div>
            @endif
        </div>

        <!-- Enrollment Type -->
        <div class="sm:w-1/2">

            <label class="flex text-sm font-medium mb-1" for="enrollment_type">
                {{ __('Enrollment Type') }}
                <sl-tooltip
                    content="Use this option to select to whom the enrollment process will be open and allowed.">
                    <sl-button>
                        <x-svg.info class="h-5 w-5 text-gray-400" />
                    </sl-button>
                </sl-tooltip>
                <span class="text-rose-500">*</span>
            </label>
            <select name="enrollment_type"
                    id="enrollment_type"
                    class="form-input w-full {{ $errors->has('enrollment_type') ? 'border-rose-300' : '' }}"
                    required>
                <option value="" selected> {{ __('-- Select an option --') }} </option>
                @foreach(\App\Enums\EvtEventEnrollmentTypeEnum::cases() as  $enrollment_type)
                    <option value="{{ $enrollment_type->name }}"
                            @if(old('enrollment_type', $event->enrollment_type) == $enrollment_type->name) selected @endif
                    >{{ $enrollment_type->value }}</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400">{{ __('How enrollments work in this event.') }} </p>
            @if($errors->has('enrollment_type'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('enrollment_type') }}
                </div>
            @endif
        </div>

    </div>

    <!-- Competition-specific fields -->
    @if($category === 'competition')
        <div class="flex flex-col md:flex-row w-full gap-4 mt-4">
            <div class="w-full md:w-1/3">
                <label class="block text-sm font-medium mb-1" for="competition_number">{{ __('Competition Number') }} <span
                        class="text-rose-500">*</span></label>
                <input type="text" name="competition[number]"
                    value="{{ old('competition[number]', optional($event->competition)->number) }}"
                    id="competition_number" min="0" class="form-input w-full" required>
                @error('competition[number]')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="w-full md:w-1/3">
                <label class="block text-sm font-medium mb-1" for="sport_id">{{ __('Sport') }} <span
                        class="text-rose-500">*</span></label>
                <select name="competition[sport_id]" id="sport_id"
                    class="form-input w-full {{ $errors->has('sport_id') ? 'border-rose-300' : '' }}" required>
                    <option value="" selected> {{ __('-- Select an option --') }} </option>
                    @foreach ($sports as $key => $sport)
                        <option value="{{ $key }}" @if (old('competition[sport_id]', optional($event->competition)->sport_id) == $key) selected @endif>
                            {{ $sport }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400">{{ __('The type of sport for the competition') }}</p>
                @if ($errors->has('competition[sport_id]'))
                    <div class="text-xs mt-1 text-rose-500 h-2">
                        {{ $errors->first('competition[sport_id]') }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Competition Dates -->
        <h2 class="font-bold mb-2 text-slate-400 mt-4">{{ __('Competition Dates') }}</h2>
        <div class="flex flex-col mb-4 md:flex-row w-full gap-4">
            <div class="w-full md:w-1/2">
                <label class="block text-sm font-medium mb-1" for="competition_start_date">
                    {{ __('Competition Start Date') }}
                    <span class="text-rose-500">*</span></label>
                <input type="date" name="start_date" id="competition_start_date"
                    value="{{ old('start_date', $event->start_date?->format('Y-m-d')) }}"
                    class="form-input w-full">
                @error('start_date')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="w-full md:w-1/2">
                <label class="block text-sm font-medium mb-1" for="competition_end_date">
                    {{ __('Competition End Date') }} <span class="text-rose-500">*</span>
                </label>
                <input type="date" name="end_date" id="competition_end_date"
                    value="{{ old('end_date', $event->end_date?->format('Y-m-d')) }}" class="form-input w-full">
                @error('end_date')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>
        
        <div class="flex flex-col md:flex-row w-full gap-4">
            <div class="md:w-1/2">
                <label class="block text-sm font-medium mb-1" for="registration_start_date">{{ __('Registration Start Date') }}</label>
                <input type="date" name="start_registration" id="registration_start_date"
                    value="{{ old('start_registration', $event->start_registration?->format('Y-m-d')) }}"
                    class="form-input w-full">
                @error('start_registration')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="md:w-1/2">
                <label class="block text-sm font-medium mb-1" for="registration_end_date">{{ __('Registration End Date') }}</label>
                <input type="date" name="end_registration" id="registration_end_date"
                    value="{{ old('end_registration', $event->end_registration?->format('Y-m-d')) }}"
                    class="form-input w-full">
                @error('end_registration')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>
        
        <!-- Enrollment Settings -->
        <h2 class="font-bold mb-2 text-slate-400 mt-4">{{ __('Enrollment Settings') }}</h2>
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex flex-col gap-y-2 md:w-1/2">
                <p class="text-slate-500 text-xs">
                    {{ __('This will make the coach and referee enrollment available.') }}
                </p>
                <div class="form-check">
                    <input type="checkbox" name="allow_coach_enrollment" id="allow_coach_enrollment" value="1"
                        {{ old('allow_coach_enrollment', $event->allow_coach_enrollment ?? false) ? 'checked' : '' }}>
                    <label for="allow_coach_enrollment"> {{ __('Allow Coach Enrollment') }}</label>
                </div>

                <div class="form-check">
                    <input type="checkbox" name="allow_referee_enrollment" id="allow_referee_enrollment" value="1"
                        {{ old('allow_referee_enrollment', $event->allow_referee_enrollment ?? false) ? 'checked' : '' }}>
                    <label for="allow_referee_enrollment"> {{ __('Allow Referee Enrollment') }}</label>
                </div>

                <div class="form-check">
                    <input type="checkbox" name="allow_individual_enrollment" id="allow_individual_enrollment"
                        value="1"
                        {{ old('allow_individual_enrollment', $event->allow_individual_enrollment ?? false) ? 'checked' : '' }}>
                    <label for="allow_individual_enrollment"> {{ __('Allow Individual Enrollment') }}</label>
                </div>
            </div>
            
            <div class="flex flex-col gap-y-2 md:w-1/2">
                <p class="text-slate-500 text-xs">
                    {{ __('This will make the athlete list, coach list and referee list public.') }}
                </p>
                <div class="form-check">
                    <input type="checkbox" name="public_athlete_list" id="public_athlete_list" value="1"
                        {{ old('public_athlete_list', $event->public_athlete_list ?? false) ? 'checked' : '' }}>
                    <label for="public_athlete_list"> {{ __('Public Athlete List') }}</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="public_coach_list" id="public_coach_list" value="1"
                        {{ old('public_coach_list', $event->public_coach_list ?? false) ? 'checked' : '' }}>
                    <label for="public_coach_list"> {{ __('Public Coach List') }}</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="public_referee_list" id="public_referee_list" value="1"
                        {{ old('public_referee_list', $event->public_referee_list ?? false) ? 'checked' : '' }}>
                    <label for="public_referee_list"> {{ __('Public Referee List') }}</label>
                </div>
            </div>
        </div>
        
        <!-- Document Requirements (formerly ADEL Requirements) -->
        <h2 class="font-bold mb-2 text-slate-400 mt-4">{{ __('Documentos Obrigatórios') }}</h2>
        <div class="space-y-2">
            <div class="form-check">
                <input type="checkbox" name="competition[requires_athlete_adel]" id="requires_athlete_adel"
                    value="1" @checked(old('competition.requires_athlete_adel', $event->competition?->requires_athlete_adel ?? false))>
                <label for="requires_athlete_adel">{{ __('Require Documents for Athletes') }}</label>
            </div>

            <div class="form-check">
                <input type="checkbox" name="competition[requires_coach_adel]" id="requires_coach_adel"
                    value="1" @checked(old('competition.requires_coach_adel', $event->competition?->requires_coach_adel ?? false))>
                <label for="requires_coach_adel">{{ __('Require Documents for Coaches') }}</label>
            </div>

            <div class="form-check">
                <input type="checkbox" name="competition[requires_referee_adel]" id="requires_referee_adel"
                    value="1" @checked(old('competition.requires_referee_adel', $event->competition?->requires_referee_adel ?? false))>
                <label for="requires_referee_adel">{{ __('Require Documents for Referees') }}</label>
            </div>
        </div>
    @endif

    <!-- Event Start and End Date -->
    @if($category === 'organization')
        <div class="flex flex-col md:flex-row w-full gap-4">

            <div class="md:w-1/2">
                <label class="block text-sm font-medium mb-1"
                       for="start_date"> {{ __('Event Start Date') }}</label>
                <input
                    type="date"
                    name="start_date"
                    id="start_date"
                    value="{{old('start_date',$event->start_date?->format('Y-m-d'))}}"
                    class="form-input w-full {{ $errors->has('start_date') ? 'border-rose-300' : '' }}">
                <p class="text-xs text-gray-400">{{ __('The suggested date when the event should officially start.') }}</p>
                @if($errors->has('start_date'))
                    <div class="text-xs mt-1 text-rose-500 h-2">
                        {{ $errors->first('start_date') }}
                    </div>
                @endif
            </div>

            <div class="md:w-1/2">
                <label class="block text-sm font-medium mb-1"
                       for="end_date"> {{ __('Event End Date') }}</label>
                <input
                    type="date"
                    name="end_date"
                    id="end_date"
                    value="{{old('end_date',$event->end_date?->format('Y-m-d'))}}"
                    class="form-input w-full {{ $errors->has('end_date') ? 'border-rose-300' : '' }}">
                <p class="text-xs text-gray-400">{{ __('The suggested date when the event should officially end.') }}</p>

                @if($errors->has('end_date'))
                    <div class="text-xs mt-1 text-rose-500 h-2">
                        {{ $errors->first('end_date') }}
                    </div>
                @endif

            </div>

        </div>
    @endif

    <div class="flex flex-col w-full gap-4">

        @if($category === 'organization')
            <div class="flex gap-4 my-2">

                <div class="w-full md:w-1/2">

                    <label class="block text-sm font-medium mb-1"
                           for="organization_type">{{ __('Organizational Type') }} <span
                            class="text-rose-500">*</span></label>
                    <select name="organization_type"
                            id="organization_type"
                            class="form-input w-full {{ $errors->has('organization_type') ? 'border-rose-300' : '' }}"
                            required>
                        <option value="" selected> {{ __('-- Select an option --') }} </option>
                        @foreach(App\Enums\EvtEventOrganizationCategoryEnum::getGroupedOptions() as $group => $types)
                            <optgroup label="{{ $group }}">
                                @foreach($types as $type)
                                    <option value="{{ $type }}"
                                            @if($event->organization_type == $type) selected @endif
                                    >
                                        {{ \App\Enums\EvtEventOrganizationCategoryEnum::toString($type) }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach

                    </select>

                    @if($errors->has('organization_type'))
                        <div class="text-xs mt-1 text-rose-500 h-2">
                            {{ $errors->first('organization_type') }}
                        </div>
                    @endif
                </div>
            </div>
        @endif


        <div class="w-full">
            <label class="block text-sm font-medium mb-1" for="external_url">
                {{ __('External Url') }}
            </label>
            <input class="form-input w-full"
                   type="text"
                   name="external_url"
                   id="external_url"
                   value="{{old('external_url',$event->external_url)}}"
                   placeholder="https://">
            <p class="text-xs text-gray-400">{{ __('An external URL with more information about the event.') }}</p>
            @if($errors->has('external_url'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('external_url') }}
                </div>
            @endif
        </div>
        <div class="w-full">
            <label class="block text-sm font-medium mb-1" for="editor"> {{ __('Event Information') }} </label>

            <textarea
                name="notes"
                id="editor"
                class="tinymce-editor form-input w-full {{ $errors->has('notes') ? 'border-rose-300' : '' }}"
                rows="3">{{old('notes',$event->notes)}}</textarea>

            @if($errors->has('notes'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('notes') }}
                </div>
            @endif
        </div>
    </div>

</div>

