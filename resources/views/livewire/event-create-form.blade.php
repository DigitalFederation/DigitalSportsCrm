<div>
    <form wire:submit.prevent="submitForm">

        {{-- Section 1: Event Type Selection --}}
        <div class="card mb-6">
            <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-200">
                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-bold text-sm">1</div>
                <div>
                    <h3 class="font-bold text-slate-800">{{ __('events.form.event_type_section') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('events.form.event_type_section_desc') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Event Category --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="event_category">
                        {{ __('events.form.category') }} <span class="text-rose-500">*</span>
                    </label>
                    <select name="event_category"
                            id="event_category"
                            wire:model.live="category_selected"
                            class="form-select w-full @error('event_category') border-rose-300 @enderror"
                            required>
                        <option value="">{{ __('common.select') }}</option>
                        @foreach($event_categories_options as $category)
                            <option value="{{ $category->name }}" @selected(old('event_category', $event->event_category) == $category->name)>
                                {{ __('events.category.' . $category->name) }}
                            </option>
                        @endforeach
                    </select>
                    @error('event_category')
                        <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Enrollment Type --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="enrollment_type">
                        {{ __('events.form.enrollment_type') }} <span class="text-rose-500">*</span>
                    </label>
                    <select name="enrollment_type"
                            id="enrollment_type"
                            wire:model="enrollment_type"
                            class="form-select w-full @error('enrollment_type') border-rose-300 @enderror"
                            required>
                        <option value="">{{ __('common.select') }}</option>
                        @foreach($event_enrollment_types_options as $type)
                            <option value="{{ $type->name }}" @selected(old('enrollment_type', $event->enrollment_type) == $type->name)>
                                {{ $type->value }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-500 mt-1">{{ __('events.form.enrollment_type_hint') }}</p>
                    @error('enrollment_type')
                        <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Competition-specific fields --}}
            @if($category_selected === 'competition')
                <div class="mt-6 pt-4 border-t border-slate-100">
                    <h4 class="text-sm font-semibold text-slate-700 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        {{ __('events.form.competition_details') }}
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        {{-- Sport --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1" for="sport_id">
                                {{ __('events.form.sport') }} <span class="text-rose-500">*</span>
                            </label>
                            <select name="sport_id"
                                    id="sport_id"
                                    wire:model.live="sport_id"
                                    class="form-select w-full @error('sport_id') border-rose-300 @enderror"
                                    required>
                                <option value="">{{ __('common.select') }}</option>
                                @foreach($sport_options as $key => $sport)
                                    <option value="{{ $key }}" @selected(old('sport_id') == $key)>{{ $sport }}</option>
                                @endforeach
                            </select>
                            @error('sport_id')
                                <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Event Type --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1" for="event_type">
                                {{ __('events.form.event_type') }} <span class="text-rose-500">*</span>
                            </label>
                            <select name="event_type"
                                    id="event_type"
                                    wire:model="event_type"
                                    class="form-select w-full @error('event_type') border-rose-300 @enderror"
                                    required>
                                <option value="">{{ __('common.select') }}</option>
                                @foreach($event_type_options as $type)
                                    <option value="{{ $type->name }}" @selected(old('event_type') == $type->name)>{{ $type->value }}</option>
                                @endforeach
                            </select>
                            @error('event_type')
                                <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Geographical Coverage --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1" for="event_geographical_coverage">
                                {{ __('events.form.scope') }} <span class="text-rose-500">*</span>
                            </label>
                            <select name="event_geographical_coverage"
                                    id="event_geographical_coverage"
                                    wire:model="event_geographical_coverage"
                                    class="form-select w-full @error('event_geographical_coverage') border-rose-300 @enderror"
                                    required>
                                <option value="">{{ __('common.select') }}</option>
                                @foreach($event_geographical_coverage_options as $scope)
                                    <option value="{{ $scope->name }}" @selected(old('event_geographical_coverage') == $scope->name)>
                                        {{ __('events.geographical_coverage_' . strtolower($scope->name)) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('event_geographical_coverage')
                                <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Competition Type --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1" for="competition_types">
                                {{ __('events.form.competition_type') }}
                            </label>
                            <livewire:input.select-multiple
                                identifier="competition_types"
                                :inputSelected="$competition_types"
                                :items="$competition_type_options"
                                inputId="competition_types"
                                inputName="competition_types[]" />
                        </div>
                    </div>
                </div>
            @endif

            {{-- Organization-specific fields --}}
            @if($category_selected === 'organization')
                <div class="mt-6 pt-4 border-t border-slate-100">
                    <h4 class="text-sm font-semibold text-slate-700 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        {{ __('events.form.organization_details') }}
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Organization Type --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1" for="organization_type">
                                {{ __('events.form.organization_type') }} <span class="text-rose-500">*</span>
                            </label>
                            <select name="organization_type"
                                    id="organization_type"
                                    wire:model="organization_type"
                                    class="form-select w-full @error('organization_type') border-rose-300 @enderror"
                                    required>
                                <option value="">{{ __('common.select') }}</option>
                                @foreach($event_organization_types_options as $group => $types)
                                    <optgroup label="{{ $group }}">
                                        @foreach($types as $type)
                                            <option value="{{ $type }}" @selected(old('organization_type') == $type)>
                                                {{ \App\Enums\EvtEventOrganizationCategoryEnum::toString($type) }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('organization_type')
                                <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Fee Type --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1" for="event_fee_type">
                                {{ __('events.form.fee_type') }}
                            </label>
                            <select name="event_fee_type"
                                    id="event_fee_type"
                                    wire:model="event_fee_type"
                                    class="form-select w-full @error('event_fee_type') border-rose-300 @enderror">
                                <option value="">{{ __('common.select') }}</option>
                                @foreach($event_fee_type_options as $key => $value)
                                    <option value="{{ $key }}" @selected(old('event_fee_type') == $value)>{{ $value }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-500 mt-1">{{ __('events.form.fee_type_hint') }}</p>
                            @error('event_fee_type')
                                <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Section 2: Event Information --}}
        <div class="card mb-6">
            <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-200">
                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-bold text-sm">2</div>
                <div>
                    <h3 class="font-bold text-slate-800">{{ __('events.form.event_info_section') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('events.form.event_info_section_desc') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left: Name and Notes --}}
                <div class="lg:col-span-2 space-y-4">
                    {{-- Event Name --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="name">
                            {{ __('events.form.name') }} <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               id="name"
                               wire:model="name"
                               class="form-input w-full @error('name') border-rose-300 @enderror"
                               placeholder="{{ __('events.form.name_placeholder') }}"
                               value="{{ old('name', $event->name) }}"
                               required>
                        @error('name')
                            <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="notes">
                            {{ __('events.form.notes') }}
                        </label>
                        <textarea name="notes"
                                  id="notes"
                                  wire:model="notes"
                                  rows="4"
                                  class="form-textarea w-full @error('notes') border-rose-300 @enderror"
                                  placeholder="{{ __('events.form.notes_placeholder') }}">{{ old('notes', $event->notes) }}</textarea>
                        @error('notes')
                            <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- External URL --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="external_url">
                            {{ __('events.form.external_url') }}
                        </label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 rounded-l border border-r-0 border-slate-200 bg-slate-50 text-slate-500 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                            </span>
                            <input type="url"
                                   name="external_url"
                                   id="external_url"
                                   wire:model="external_url"
                                   class="form-input w-full rounded-l-none @error('external_url') border-rose-300 @enderror"
                                   placeholder="https://example.test/event">
                        </div>
                        <p class="text-xs text-slate-500 mt-1">{{ __('events.form.external_url_hint') }}</p>
                        @error('external_url')
                            <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Moloni Reference --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="moloni_reference">
                            {{ __('moloni.product_reference') }}
                        </label>
                        <input type="text"
                               name="moloni_reference"
                               id="moloni_reference"
                               wire:model="moloni_reference"
                               class="form-input w-full @error('moloni_reference') border-rose-300 @enderror"
                               maxlength="50">
                        <p class="text-xs text-slate-500 mt-1">{{ __('moloni.product_reference_help') }}</p>
                        @error('moloni_reference')
                            <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Right: Status and Dates --}}
                <div class="space-y-4">
                    {{-- Event Status --}}
                    <div class="p-4 bg-slate-50 rounded-lg">
                        <label class="block text-sm font-medium text-slate-700 mb-2" for="status_class">
                            {{ __('events.form.status') }} <span class="text-rose-500">*</span>
                        </label>
                        <select name="status_class"
                                id="status_class"
                                wire:model.live="status_class_selected"
                                class="form-select w-full @error('status_class') border-rose-300 @enderror"
                                required>
                            <option value="">{{ __('common.select') }}</option>
                            <option value="{{ \Domain\EvtEvents\States\PreparationEventState::class }}">
                                {{ __('events.status.preparation') }}
                            </option>
                            <option value="{{ \Domain\EvtEvents\States\ActiveEventState::class }}">
                                {{ __('events.status.active') }}
                            </option>
                        </select>
                        <p class="text-xs text-slate-500 mt-2">{{ __('events.form.status_hint') }}</p>
                        @error('status_class')
                            <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Organizer (only for Active events) --}}
                    @if($status_class_selected == \Domain\EvtEvents\States\ActiveEventState::class)
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1" for="organizer_id">
                                {{ __('events.form.organizer') }}
                            </label>
                            <select wire:model="organizer_id"
                                    id="organizer_id"
                                    class="form-select w-full">
                                <option value="">{{ __('common.select') }}</option>
                                @foreach($organizer_options as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Dates (for organization events) --}}
                    @if($category_selected === 'organization')
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1" for="start_date">
                                    {{ __('events.form.start_date') }}
                                </label>
                                <input type="date"
                                       name="start_date"
                                       id="start_date"
                                       wire:model="start_date"
                                       class="form-input w-full @error('start_date') border-rose-300 @enderror">
                                @error('start_date')
                                    <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1" for="end_date">
                                    {{ __('events.form.end_date') }}
                                </label>
                                <input type="date"
                                       name="end_date"
                                       id="end_date"
                                       wire:model="end_date"
                                       class="form-input w-full @error('end_date') border-rose-300 @enderror">
                                @error('end_date')
                                    <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Section 3: Location & Venue --}}
        <div class="card mb-6">
            <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-200">
                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-bold text-sm">3</div>
                <div>
                    <h3 class="font-bold text-slate-800">{{ __('events.form.location_section') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('events.form.location_section_desc') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Venue Information --}}
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ __('events.form.venue') }}
                    </h4>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="venue">
                            {{ __('events.form.venue_name') }}
                        </label>
                        <input type="text"
                               name="venue"
                               id="venue"
                               wire:model="venue"
                               class="form-input w-full"
                               placeholder="{{ __('events.form.venue_name_placeholder') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="venue_address">
                            {{ __('events.form.venue_address') }}
                        </label>
                        <input type="text"
                               name="venue_address"
                               id="venue_address"
                               wire:model="venue_address"
                               class="form-input w-full"
                               placeholder="{{ __('events.form.venue_address_placeholder') }}">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1" for="venue_city">
                                {{ __('events.form.venue_city') }}
                            </label>
                            <input type="text"
                                   name="venue_city"
                                   id="venue_city"
                                   wire:model="venue_city"
                                   class="form-input w-full"
                                   placeholder="{{ __('events.form.venue_city_placeholder') }}">
                        </div>

                        @if($category_selected === 'organization')
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1" for="location">
                                    {{ __('events.form.location') }}
                                </label>
                                <input type="text"
                                       name="location"
                                       id="location"
                                       wire:model="location"
                                       class="form-input w-full"
                                       value="{{ old('location', $event->location) }}">
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Geographic Filters --}}
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('events.form.geographic_filters') }}
                    </h4>
                    <p class="text-xs text-slate-500">{{ __('events.form.geographic_filters_hint') }}</p>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="geo_zone_id">
                            {{ __('events.form.geo_zone') }}
                        </label>
                        <livewire:input.select-multiple
                            :inputSelected="$selected_geo_zones"
                            identifier="geo_zones"
                            :items="$geo_zone_options"
                            inputId="geo_zone_id"
                            inputName="geo_zone_id[]" />
                        @error('selected_geo_zones')
                            <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="country_id">
                            {{ __('events.form.countries') }}
                        </label>
                        <livewire:input.select-multiple
                            :inputSelected="$selected_countries"
                            identifier="countries"
                            :items="$country_options"
                            inputId="country_id"
                            inputName="country_id[]" />
                        @error('selected_countries')
                            <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 4: Additional Settings (conditional) --}}
        @if($category_selected)
            <div class="card mb-6">
                <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-200">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-bold text-sm">4</div>
                    <div>
                        <h3 class="font-bold text-slate-800">{{ __('events.form.settings_section') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('events.form.settings_section_desc') }}</p>
                    </div>
                </div>

                @if($category_selected === 'competition')
                    {{-- Competition Settings --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Referee Certifications --}}
                        <div class="p-4 bg-slate-50 rounded-lg">
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                    </svg>
                                    {{ __('events.form.referee_certifications') }}
                                </span>
                            </label>
                            <p class="text-xs text-slate-500 mb-3">{{ __('events.form.referee_certifications_hint') }}</p>
                            <livewire:input.select-multiple
                                identifier="referee_certifications"
                                :inputSelected="$selected_referee_certifications"
                                :items="$referee_certifications_options"
                                inputId="referee_certifications"
                                inputName="referee_certifications[]" />
                        </div>

                        {{-- Coach Certifications --}}
                        <div class="p-4 bg-slate-50 rounded-lg">
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                    </svg>
                                    {{ __('events.form.coach_certifications') }}
                                </span>
                            </label>
                            <p class="text-xs text-slate-500 mb-3">{{ __('events.form.coach_certifications_hint') }}</p>
                            <livewire:input.select-multiple
                                identifier="coach_certifications"
                                :inputSelected="$selected_coach_certifications"
                                :items="$coach_certifications_options"
                                inputId="coach_certifications"
                                inputName="coach_certifications[]" />
                        </div>
                    </div>

                @elseif($category_selected === 'organization')
                    {{-- Organization Settings --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Professional Roles --}}
                        <div class="p-4 bg-slate-50 rounded-lg">
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    {{ __('events.form.professional_roles') }}
                                </span>
                            </label>
                            <p class="text-xs text-slate-500 mb-3">{{ __('events.form.professional_roles_hint') }}</p>
                            <livewire:input.select-multiple
                                identifier="professional_roles"
                                :inputSelected="$selected_professional_roles"
                                :items="$professional_roles_options"
                                inputId="professional_roles"
                                inputName="professional_roles[]" />
                            @error('selected_professional_roles')
                                <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Enrollment Attributes --}}
                        <div class="p-4 bg-slate-50 rounded-lg">
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                    {{ __('events.form.enrollment_attributes') }}
                                </span>
                            </label>
                            <p class="text-xs text-slate-500 mb-3">{{ __('events.form.enrollment_attributes_hint') }}</p>
                            <div class="space-y-2 max-h-48 overflow-y-auto">
                                @foreach($all_attribute_groups as $attribute_group)
                                    <label class="flex items-center justify-between p-2 bg-white rounded border border-slate-200 cursor-pointer hover:border-indigo-300 transition-colors">
                                        <span class="text-sm text-slate-700">{{ $attribute_group->name }}</span>
                                        <input type="checkbox"
                                               value="{{ $attribute_group->id }}"
                                               wire:model="selected_attribute_groups"
                                               class="form-checkbox h-4 w-4 text-indigo-600 rounded">
                                    </label>
                                @endforeach
                            </div>
                            @error('selected_attribute_groups')
                                <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Submit Section --}}
        <div class="card">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="text-sm text-slate-500">
                    <span class="text-rose-500">*</span> {{ __('events.form.required_fields') }}
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('admin.evt-events.events.index') }}" class="btn btn-secondary">
                        {{ __('common.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $edit ? __('events.form.update_event') : __('events.form.create_event') }}
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>
