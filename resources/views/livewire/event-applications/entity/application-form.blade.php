<div>
    <form wire:submit.prevent="submitApplication" class="card">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            <!-- Event Name -->
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium mb-1" for="event_name">
                    {{ __('event_applications.labels.event_name') }} <span class="text-rose-500">*</span>
                </label>
                <input type="text"
                       id="event_name"
                       wire:model="event_name"
                       class="form-input w-full @error('event_name') border-rose-300 @enderror"
                       placeholder="{{ __('event_applications.placeholders.event_name') }}"
                       @if($template) readonly @endif
                       required>
                @error('event_name')
                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                @enderror
            </div>

            <!-- Event Type -->
            <div>
                <label class="block text-sm font-medium mb-1" for="event_type">
                    {{ __('event_applications.labels.event_type') }} <span class="text-rose-500">*</span>
                </label>
                <select id="event_type"
                        wire:model.live="event_type"
                        class="form-select w-full @error('event_type') border-rose-300 @enderror"
                        @if($template) disabled @endif
                        required>
                    <option value="">{{ __('common.select') }}</option>
                    @foreach($eventTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('event_type')
                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                @enderror
            </div>

            <!-- Event Category (shown only for organization type) -->
            <div x-show="$wire.event_type === 'organization'" x-transition>
                <label class="block text-sm font-medium mb-1" for="event_category">
                    {{ __('event_applications.labels.event_category') }} <span class="text-rose-500">*</span>
                </label>
                <select id="event_category"
                        wire:model="event_category"
                        class="form-select w-full @error('event_category') border-rose-300 @enderror"
                        @if($template) disabled @endif>
                    <option value="">{{ __('common.select') }}</option>
                    @foreach($eventCategories as $group => $categories)
                        <optgroup label="{{ $group }}">
                            @foreach($categories as $category)
                                <option value="{{ $category }}">{{ \App\Enums\EvtEventOrganizationCategoryEnum::toString($category) }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('event_category')
                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                @enderror
            </div>

            <!-- Sport -->
            <div>
                <label class="block text-sm font-medium mb-1" for="sport_id">
                    {{ __('Sport') }}
                </label>
                <select id="sport_id"
                        wire:model="sport_id"
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

            <!-- Start Date -->
            <div>
                <label class="block text-sm font-medium mb-1" for="start_date">
                    {{ __('event_applications.labels.start_date') }} <span class="text-rose-500">*</span>
                </label>
                <input type="date"
                       id="start_date"
                       wire:model="start_date"
                       class="form-input w-full @error('start_date') border-rose-300 @enderror"
                       required>
                @error('start_date')
                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                @enderror
            </div>

            <!-- End Date -->
            <div>
                <label class="block text-sm font-medium mb-1" for="end_date">
                    {{ __('event_applications.labels.end_date') }} <span class="text-rose-500">*</span>
                </label>
                <input type="date"
                       id="end_date"
                       wire:model="end_date"
                       class="form-input w-full @error('end_date') border-rose-300 @enderror"
                       required>
                @error('end_date')
                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                @enderror
            </div>

            <!-- District -->
            <div>
                <label class="block text-sm font-medium mb-1" for="district_id">
                    {{ __('event_applications.labels.district') }}
                </label>
                <select id="district_id"
                        wire:model="district_id"
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

            <!-- Municipality -->
            <div>
                <label class="block text-sm font-medium mb-1" for="municipality">
                    {{ __('event_applications.labels.municipality') }}
                </label>
                <input type="text"
                       id="municipality"
                       wire:model="municipality"
                       class="form-input w-full @error('municipality') border-rose-300 @enderror"
                       placeholder="{{ __('event_applications.labels.municipality') }}">
                @error('municipality')
                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                @enderror
            </div>

            <!-- Responsible Name -->
            <div>
                <label class="block text-sm font-medium mb-1" for="responsible_name">
                    {{ __('event_applications.labels.responsible_name') }} <span class="text-rose-500">*</span>
                </label>
                <input type="text"
                       id="responsible_name"
                       wire:model="responsible_name"
                       class="form-input w-full @error('responsible_name') border-rose-300 @enderror"
                       placeholder="{{ __('event_applications.placeholders.responsible_name') }}"
                       required>
                @error('responsible_name')
                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                @enderror
            </div>

            <!-- Responsible Phone -->
            <div>
                <label class="block text-sm font-medium mb-1" for="responsible_phone">
                    {{ __('event_applications.labels.responsible_phone') }} <span class="text-rose-500">*</span>
                </label>
                <input type="tel"
                       id="responsible_phone"
                       wire:model="responsible_phone"
                       class="form-input w-full @error('responsible_phone') border-rose-300 @enderror"
                       placeholder="{{ __('event_applications.placeholders.responsible_phone') }}"
                       required>
                @error('responsible_phone')
                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                @enderror
            </div>

            <!-- Target Audience -->
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium mb-1" for="target_audience">
                    {{ __('event_applications.labels.target_audience') }}
                </label>
                <textarea id="target_audience"
                          wire:model="target_audience"
                          rows="3"
                          class="form-textarea w-full @error('target_audience') border-rose-300 @enderror"
                          placeholder="{{ __('event_applications.placeholders.target_audience') }}"></textarea>
                @error('target_audience')
                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                @enderror
            </div>

            <!-- Expected Participants -->
            <div>
                <label class="block text-sm font-medium mb-1" for="expected_participants">
                    {{ __('event_applications.labels.expected_participants') }}
                </label>
                <input type="number"
                       id="expected_participants"
                       wire:model="expected_participants"
                       min="1"
                       class="form-input w-full @error('expected_participants') border-rose-300 @enderror"
                       placeholder="{{ __('event_applications.placeholders.expected_participants') }}">
                @error('expected_participants')
                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                @enderror
            </div>

        </div>

        <!-- Form Actions -->
        <div class="flex flex-wrap justify-end space-x-2 mt-6">
            <button type="button" wire:click="cancel" class="btn btn-secondary">
                {{ __('common.cancel') }}
            </button>

            <button type="button" wire:click="saveDraft" class="btn btn-info" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="saveDraft">{{ __('event_applications.actions.save_draft') }}</span>
                <span wire:loading wire:target="saveDraft">{{ __('common.saving') }}...</span>
            </button>

            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="submitApplication">
                    {{ $mode === 'edit' ? __('event_applications.actions.submit_application') : __('event_applications.actions.submit_application') }}
                </span>
                <span wire:loading wire:target="submitApplication">{{ __('common.submitting') }}...</span>
            </button>
        </div>
    </form>
</div>
