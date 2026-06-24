{{-- Step 1: Event Identification & Characterization (Sections 0 + 1) --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

    {{-- Section 1: Template Information (readonly when template exists) --}}
    <div class="sm:col-span-2 bg-gray-50 rounded-lg p-4 border border-gray-200">
        <h4 class="text-sm font-semibold text-slate-700 mb-3">
            {{ __('event_applications.wizard.sections.template_info') }}
        </h4>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            {{-- Event Name --}}
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium mb-1" for="event_name">
                    {{ __('event_applications.labels.event_name') }} <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="event_name" wire:model="event_name"
                       class="form-input w-full @error('event_name') border-rose-300 @enderror"
                       placeholder="{{ __('event_applications.placeholders.event_name') }}"
                       @if($template) readonly @endif>
                @error('event_name')
                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                @enderror
            </div>

            {{-- Sport --}}
            <div>
                <label class="block text-sm font-medium mb-1" for="sport_id">
                    {{ __('event_applications.labels.sport') }}
                </label>
                <select id="sport_id" wire:model="sport_id"
                        class="form-select w-full @error('sport_id') border-rose-300 @enderror"
                        @if($template) disabled @endif>
                    <option value="">{{ __('common.select') }}</option>
                    @foreach($sports as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
                @error('sport_id')
                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                @enderror
            </div>

            {{-- Template readonly fields --}}
            @if($template?->registration_type)
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-600">
                        {{ __('event_applications.labels.registration_type') }}
                    </label>
                    <p class="text-sm text-slate-800 bg-white rounded-lg px-3 py-2 border border-gray-200">
                        {{ __('event_applications.registration_types.' . $template->registration_type) }}
                    </p>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium mb-1" for="category">
                    {{ __('event_applications.labels.category') }}
                </label>
                @if($template?->category)
                    <p class="text-sm text-slate-800 bg-white rounded-lg px-3 py-2 border border-gray-200">
                        {{ __('event_applications.categories.' . $template->category) }}
                    </p>
                @else
                    <select id="category" wire:model="category"
                            class="form-select w-full">
                        <option value="">{{ __('common.select') }}</option>
                        @foreach(['A', 'B', 'C', 'D'] as $cat)
                            <option value="{{ $cat }}">{{ __('event_applications.categories.' . $cat) }}</option>
                        @endforeach
                    </select>
                @endif
            </div>

            @if($template?->age_group)
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-600">
                        {{ __('event_applications.labels.age_group') }}
                    </label>
                    <p class="text-sm text-slate-800 bg-white rounded-lg px-3 py-2 border border-gray-200">
                        {{ $template->age_group }}
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Section 2: Entity Fields (editable) --}}
    <div class="sm:col-span-2 border-t border-gray-200 pt-4 mt-2">
        <h4 class="text-sm font-semibold text-slate-700 mb-3">
            {{ __('event_applications.wizard.sections.entity_fields') }}
        </h4>
    </div>

    {{-- Proposed Start Date --}}
    <div>
        <label class="block text-sm font-medium mb-1" for="start_date">
            {{ __('event_applications.labels.proposed_start_date') }} <span class="text-rose-500">*</span>
        </label>
        <input type="date" id="start_date" wire:model="start_date"
               class="form-input w-full @error('start_date') border-rose-300 @enderror">
        @error('start_date')
            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
        @enderror
    </div>

    {{-- Proposed End Date --}}
    <div>
        <label class="block text-sm font-medium mb-1" for="end_date">
            {{ __('event_applications.labels.proposed_end_date') }} <span class="text-rose-500">*</span>
        </label>
        <input type="date" id="end_date" wire:model="end_date"
               class="form-input w-full @error('end_date') border-rose-300 @enderror">
        @error('end_date')
            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
        @enderror
    </div>

    {{-- District --}}
    <div>
        <label class="block text-sm font-medium mb-1" for="district_id">
            {{ __('event_applications.labels.district') }}
        </label>
        <select id="district_id" wire:model="district_id"
                class="form-select w-full @error('district_id') border-rose-300 @enderror">
            <option value="">{{ __('common.select') }}</option>
            @foreach($districts as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
        @error('district_id')
            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
        @enderror
    </div>

    {{-- Municipality --}}
    <div>
        <label class="block text-sm font-medium mb-1" for="municipality">
            {{ __('event_applications.labels.municipality') }}
        </label>
        <input type="text" id="municipality" wire:model="municipality"
               class="form-input w-full @error('municipality') border-rose-300 @enderror"
               placeholder="{{ __('event_applications.labels.municipality') }}">
        @error('municipality')
            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
        @enderror
    </div>

    {{-- Address --}}
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1" for="formData_address">
            {{ __('event_applications.wizard.labels.address') }}
        </label>
        <input type="text" id="formData_address" wire:model="formData.address"
               class="form-input w-full"
               placeholder="{{ __('event_applications.wizard.placeholders.address') }}">
    </div>

    {{-- Postal Code --}}
    <div>
        <label class="block text-sm font-medium mb-1" for="formData_postal_code">
            {{ __('event_applications.wizard.labels.postal_code') }}
        </label>
        <input type="text" id="formData_postal_code" wire:model="formData.postal_code"
               class="form-input w-full"
               placeholder="{{ __('event_applications.wizard.placeholders.postal_code') }}">
    </div>

    {{-- Location --}}
    <div>
        <label class="block text-sm font-medium mb-1" for="formData_location">
            {{ __('event_applications.wizard.labels.location') }}
        </label>
        <input type="text" id="formData_location" wire:model="formData.location"
               class="form-input w-full"
               placeholder="{{ __('event_applications.wizard.placeholders.location') }}">
    </div>

    {{-- Target Audience --}}
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1" for="target_audience">
            {{ __('event_applications.labels.target_audience') }}
        </label>
        <textarea id="target_audience" wire:model="target_audience" rows="2"
                  class="form-textarea w-full"
                  placeholder="{{ __('event_applications.placeholders.target_audience') }}"></textarea>
    </div>

    {{-- Section Comments --}}
    @if($application)
        <div class="sm:col-span-2">
            @include('web.entity.event-applications.components.section-comments', [
                'application' => $application,
                'section' => 'event_location',
            ])
        </div>
    @endif
</div>
