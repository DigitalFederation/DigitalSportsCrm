<div class="card">
    <div class="flex gap-x-2 items-center border-b border-gray-300 pb-2 mb-4">
        <x-svg.info class="w-6 h-6 text-slate-600" />
        <span class="font-bold">{{ __('events.competition_information') }}</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1" for="competition_number">
                {{ __('events.competition_number') }}
                <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="competition[number]"
                value="{{ old('competition[number]', optional($event->competition)->number) }}"
                id="competition_number" min="0" class="form-input w-full" required>
            @error('competition[number]')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1" for="sport_id">
                {{ __('events.sport') }}
                <span class="text-rose-500">*</span>
            </label>
            <select name="competition[sport_id]" id="sport_id"
                class="form-input w-full {{ $errors->has('sport_id') ? 'border-rose-300' : '' }}" required>
                <option value="" selected>{{ __('events.select_option') }}</option>
                @foreach ($sports as $key => $sport)
                    <option value="{{ $key }}" @selected(old('competition[sport_id]', optional($event->competition)->sport_id) == $key)>
                        {{ $sport }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400 mt-1">{{ __('events.sport_help') }}</p>
            @error('competition[sport_id]')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div x-cloak>
            <label class="block text-sm font-medium mb-1" for="event_type">
                {{ __('events.event_type') }}
                <span class="text-rose-500">*</span>
            </label>
            <select name="event_type" id="event_type"
                class="form-input w-full {{ $errors->has('event_type') ? 'border-rose-300' : '' }}" required>
                <option value="" selected>{{ __('events.select_option') }}</option>
                @foreach (\App\Enums\EvtEventTypeEnum::cases() as $type)
                    <option value="{{ $type->name }}" @selected(old('event_type', $event->event_type) == $type->name)>
                        {{ $type->value }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400 mt-1">{{ __('events.event_type_help') }}</p>
            @error('event_type')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="mt-4">
        <label class="block text-sm font-medium mb-1" for="competition_types">
            {{ __('events.competition_type') }}
        </label>
        <div class="max-h-32 overflow-y-auto border border-gray-200 rounded-lg p-3">
            @php
                $currentTypes = old(
                    'competition[types]',
                    optional($event->competition)->types?->pluck('competition_type')->all() ?? [],
                );
            @endphp
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                @foreach (App\Enums\EvtCompetitionTypeEnum::cases() as $type)
                    <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 rounded p-1">
                        <input type="checkbox" name="competition[types][]" value="{{ $type->value }}"
                            class="rounded text-blue-600 focus:ring-blue-500"
                            @checked(in_array($type->value, $currentTypes))>
                        <span class="text-sm text-gray-700">{{ App\Enums\EvtCompetitionTypeEnum::toString($type->value) }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-1">{{ __('events.competition_type_help') }}</p>
    </div>
</div>

<div class="card">
    <div class="flex gap-x-2 items-center border-b border-gray-300 pb-2 mb-4">
        <x-svg.calendar-event class="w-6 h-6 text-slate-600" />
        <span class="font-bold">{{ __('events.competition_dates') }}</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-4">
            <h3 class="text-sm font-semibold text-slate-500">{{ __('events.event_dates') }}</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1" for="competition_start_date">
                        {{ __('events.competition_start_date') }}
                        <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="start_date" id="competition_start_date"
                        value="{{ old('start_date', $event->start_date?->format('Y-m-d')) }}"
                        class="form-input w-full">
                    @error('start_date')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1" for="competition_end_date">
                        {{ __('events.competition_end_date') }}
                        <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="end_date" id="competition_end_date"
                        value="{{ old('end_date', $event->end_date?->format('Y-m-d')) }}"
                        class="form-input w-full">
                    @error('end_date')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <h3 class="text-sm font-semibold text-slate-500">{{ __('events.registration_dates') }}</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1" for="registration_start_date">
                        {{ __('events.registration_start_date') }}
                    </label>
                    <input type="date" name="start_registration" id="registration_start_date"
                        value="{{ old('start_registration', $event->start_registration?->format('Y-m-d')) }}"
                        class="form-input w-full">
                    @error('start_registration')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1" for="registration_end_date">
                        {{ __('events.registration_end_date') }}
                    </label>
                    <input type="date" name="end_registration" id="registration_end_date"
                        value="{{ old('end_registration', $event->end_registration?->format('Y-m-d')) }}"
                        class="form-input w-full">
                    @error('end_registration')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="flex gap-x-2 items-center border-b border-gray-300 pb-2 mb-4">
        <x-svg.toggles class="w-6 h-6 text-slate-600" />
        <span class="font-bold">{{ __('events.competition_settings') }}</span>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1" for="rounds_total">
                {{ __('events.rounds_total') }}
            </label>
            <input type="number" name="competition[rounds_total]" id="rounds_total"
                class="form-input w-full"
                value="{{ old('competition[rounds_total]', optional($event->competition)->rounds_total) }}">
            @error('competition[rounds_total]')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1" for="cat_age">
                {{ __('events.category_age') }}
            </label>
            <input type="text" id="cat_age" class="form-input w-full" name="competition[cat_age]"
                value="{{ old('competition[cat_age]', optional($event->competition)->cat_age) }}">
            @error('competition[cat_age]')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1" for="cat_competition">
                {{ __('events.competition_category') }}
            </label>
            <select id="cat_competition" name="competition[cat_competition]" class="form-input w-full">
                @foreach (\App\Enums\EvtCompetitionCategoryEnum::cases() as $cat_competition)
                    <option value="{{ $cat_competition->name }}"
                        @selected(old('competition[cat_competition]', optional($event->competition)->cat_competition) == $cat_competition->name)>
                        {{ $cat_competition->name }}
                    </option>
                @endforeach
            </select>
            @error('competition[cat_competition]')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1" for="environment">
                {{ __('events.environment') }}
            </label>
            <select id="environment" name="competition[environment]" class="form-input w-full">
                @foreach (\App\Enums\EvtCompetitionEnvironmentEnum::cases() as $environment)
                    <option value="{{ $environment->name }}"
                        @selected(old('competition[environment]', optional($event->competition)->environment) == $environment->name)>
                        {{ \App\Enums\EvtCompetitionEnvironmentEnum::toString($environment->value) }}
                    </option>
                @endforeach
            </select>
            @error('competition[environment]')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="mt-6 pt-4 border-t border-gray-200">
        <h3 class="text-sm font-semibold text-slate-500 mb-3">{{ __('events.discipline_group') }}</h3>
        <p class="text-xs text-gray-400 mb-3">{{ __('events.discipline_group_help') }}</p>
        <div>
            <label class="block text-sm font-medium mb-1" for="discipline_template_id">
                {{ __('events.choose_discipline_group') }}
            </label>
            <select id="discipline_template_id" name="competition[discipline_template_id]" class="form-input w-full md:w-1/2">
                @foreach ($disciplineTemplates as $template)
                    <option value="{{ $template->id }}"
                        @selected(old('competition[discipline_template_id]', optional($event->competition)->discipline_template_id) == $template->id)>
                        {{ $template->name }}
                        @if ($template->description)
                            - {{ $template->description }}
                        @endif
                    </option>
                @endforeach
            </select>
            @error('competition[discipline_template_id]')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>
</div>

<div class="card">
    <div class="flex gap-x-2 items-center border-b border-gray-300 pb-2 mb-4">
        <x-svg.people-group class="w-6 h-6 text-slate-600" />
        <span class="font-bold">{{ __('events.enrollment_settings') }}</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-semibold text-slate-500">{{ __('events.form.enrollment_options') }}</h3>
                <p class="text-xs text-gray-400 mt-1">{{ __('events.coach_referee_enrollment_help') }}</p>
            </div>
            <div class="space-y-3 pl-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="allow_coach_enrollment" id="allow_coach_enrollment" value="1"
                        class="rounded text-blue-600 focus:ring-blue-500"
                        @checked(old('allow_coach_enrollment', $event->allow_coach_enrollment ?? false))>
                    <span class="text-sm">{{ __('events.allow_coach_enrollment') }}</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="allow_referee_enrollment" id="allow_referee_enrollment" value="1"
                        class="rounded text-blue-600 focus:ring-blue-500"
                        @checked(old('allow_referee_enrollment', $event->allow_referee_enrollment ?? false))>
                    <span class="text-sm">{{ __('events.allow_referee_enrollment') }}</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="allow_individual_enrollment" id="allow_individual_enrollment" value="1"
                        class="rounded text-blue-600 focus:ring-blue-500"
                        @checked(old('allow_individual_enrollment', $event->allow_individual_enrollment ?? false))>
                    <span class="text-sm">{{ __('events.allow_individual_enrollment') }}</span>
                </label>
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-semibold text-slate-500">{{ __('events.form.visibility_options') }}</h3>
                <p class="text-xs text-gray-400 mt-1">{{ __('events.public_lists_help') }}</p>
            </div>
            <div class="space-y-3 pl-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="public_athlete_list" id="public_athlete_list" value="1"
                        class="rounded text-blue-600 focus:ring-blue-500"
                        @checked(old('public_athlete_list', $event->public_athlete_list ?? false))>
                    <span class="text-sm">{{ __('events.public_athlete_list') }}</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="public_coach_list" id="public_coach_list" value="1"
                        class="rounded text-blue-600 focus:ring-blue-500"
                        @checked(old('public_coach_list', $event->public_coach_list ?? false))>
                    <span class="text-sm">{{ __('events.public_coach_list') }}</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="public_referee_list" id="public_referee_list" value="1"
                        class="rounded text-blue-600 focus:ring-blue-500"
                        @checked(old('public_referee_list', $event->public_referee_list ?? false))>
                    <span class="text-sm">{{ __('events.public_referee_list') }}</span>
                </label>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="flex gap-x-2 items-center border-b border-gray-300 pb-2 mb-4">
        <x-svg.queue-list class="w-6 h-6 text-slate-600" />
        <span class="font-bold">{{ __('events.enrollment_limits') }}</span>
    </div>

    <p class="text-xs text-gray-400 mb-4">{{ __('events.leave_empty_unlimited') }}</p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1" for="max_disciplines_per_athlete">
                {{ __('events.max_disciplines_per_athlete') }}
            </label>
            <input type="number" name="competition[max_disciplines_per_athlete]"
                id="max_disciplines_per_athlete" min="0" class="form-input w-full"
                value="{{ old('competition.max_disciplines_per_athlete', $event->competition?->max_disciplines_per_athlete) }}">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1" for="max_relays_per_athlete">
                {{ __('events.max_relays_per_athlete') }}
            </label>
            <input type="number" name="competition[max_relays_per_athlete]" id="max_relays_per_athlete"
                min="0" class="form-input w-full"
                value="{{ old('competition.max_relays_per_athlete', $event->competition?->max_relays_per_athlete) }}">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1" for="max_teams_per_athlete">
                {{ __('events.max_teams_per_athlete') }}
            </label>
            <input type="number" name="competition[max_teams_per_athlete]" id="max_teams_per_athlete"
                min="0" class="form-input w-full"
                value="{{ old('competition.max_teams_per_athlete', $event->competition?->max_teams_per_athlete) }}">
        </div>
    </div>
</div>

<div class="card">
    <div class="flex gap-x-2 items-center border-b border-gray-300 pb-2 mb-4">
        <x-svg.check class="w-6 h-6 text-slate-600" />
        <span class="font-bold">{{ __('events.registration_filter_requirements') }}</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-6">
            <div>
                <h3 class="text-sm font-semibold text-slate-500 mb-3">{{ __('events.form.athlete_enrollment_section') }}</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('events.athlete_licenses') }}</label>
                        <livewire:input.select-multiple
                            :inputSelected="$event->competition?->required_athlete_licenses ?? []"
                            identifier="athlete_licenses"
                            :items="$licenses"
                            inputId="athlete_licenses"
                            inputName="competition[required_athlete_licenses][]" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('events.coach_certifications') }}</label>
                        <livewire:input.select-multiple
                            :inputSelected="$event->competition?->requiredCoachCertifications->pluck('id')->toArray() ?? []"
                            identifier="coach_certifications"
                            :items="$certifications"
                            inputId="coach_certifications"
                            inputName="competition[required_coach_certifications][]" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('events.referee_certifications') }}</label>
                        <livewire:input.select-multiple
                            :inputSelected="$event->competition?->requiredRefereeCertifications->pluck('id')->toArray() ?? []"
                            identifier="referee_certifications"
                            :items="$certifications"
                            inputId="referee_certifications"
                            inputName="competition[required_referee_certifications][]" />
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div>
                <h3 class="text-sm font-semibold text-slate-500 mb-3">{{ __('events.adel_certification_requirements') }}</h3>
                <div class="space-y-3 pl-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="competition[requires_athlete_adel]" id="requires_athlete_adel"
                            value="1" class="rounded text-blue-600 focus:ring-blue-500"
                            @checked(old('competition.requires_athlete_adel', $event->competition?->requires_athlete_adel ?? false))>
                        <span class="text-sm">{{ __('events.require_adel_athletes') }}</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="competition[requires_coach_adel]" id="requires_coach_adel"
                            value="1" class="rounded text-blue-600 focus:ring-blue-500"
                            @checked(old('competition.requires_coach_adel', $event->competition?->requires_coach_adel ?? false))>
                        <span class="text-sm">{{ __('events.require_adel_coaches') }}</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="competition[requires_referee_adel]" id="requires_referee_adel"
                            value="1" class="rounded text-blue-600 focus:ring-blue-500"
                            @checked(old('competition.requires_referee_adel', $event->competition?->requires_referee_adel ?? false))>
                        <span class="text-sm">{{ __('events.require_adel_referees') }}</span>
                    </label>
                </div>
            </div>

            <div>
                <div class="space-y-4 pl-1">
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="competition[requires_athlete_entity_sport_registration]"
                                id="requires_athlete_entity_sport_registration" value="1"
                                class="rounded text-blue-600 focus:ring-blue-500"
                                @checked(old('competition.requires_athlete_entity_sport_registration', $event->competition?->requires_athlete_entity_sport_registration ?? true))>
                            <span class="text-sm">{{ __('evt.requires_athlete_entity_sport_registration') }}</span>
                        </label>
                        <p class="text-xs text-gray-400 mt-1 ml-6">{{ __('evt.requires_athlete_entity_sport_registration_hint') }}</p>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="competition[requires_coach_entity_sport_registration]"
                                id="requires_coach_entity_sport_registration" value="1"
                                class="rounded text-blue-600 focus:ring-blue-500"
                                @checked(old('competition.requires_coach_entity_sport_registration', $event->competition?->requires_coach_entity_sport_registration ?? true))>
                            <span class="text-sm">{{ __('evt.requires_coach_entity_sport_registration') }}</span>
                        </label>
                        <p class="text-xs text-gray-400 mt-1 ml-6">{{ __('evt.requires_coach_entity_sport_registration_hint') }}</p>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="competition[requires_local_federation_affiliation]"
                                id="requires_local_federation_affiliation" value="1"
                                class="rounded text-blue-600 focus:ring-blue-500"
                                @checked(old('competition.requires_local_federation_affiliation', $event->competition?->requires_local_federation_affiliation ?? false))>
                            <span class="text-sm">{{ __('evt.requires_local_federation_affiliation') }}</span>
                        </label>
                        <p class="text-xs text-gray-400 mt-1 ml-6">{{ __('evt.requires_local_federation_affiliation_hint') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
