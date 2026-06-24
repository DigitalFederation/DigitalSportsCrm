<div x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">
    <form wire:submit.prevent="submitApplication">
        <div class="space-y-6">

            {{-- Card 1: General Information --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
                 x-show="loaded"
                 x-transition:enter="transition ease-out duration-500 delay-100"
                 x-transition:enter-start="opacity-0 transform translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0">

                <div class="border-b border-gray-200 bg-gray-50/50 px-5 py-3">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100">
                            <x-heroicon-o-information-circle class="w-4 h-4 text-indigo-600" />
                        </div>
                        <span class="text-sm font-semibold text-gray-700">{{ __('event_applications.sections.general_information') }}</span>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Event Name --}}
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium mb-1" for="event_name">
                                {{ __('event_applications.labels.event_name') }} <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                   id="event_name"
                                   wire:model="event_name"
                                   class="form-input w-full @error('event_name') border-rose-300 @enderror"
                                   placeholder="{{ __('event_applications.placeholders.event_name') }}"
                                   required>
                            @error('event_name')
                                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Event Type --}}
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

                        {{-- Event Category (shown only for organization type) --}}
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

                        {{-- Sport --}}
                        <div>
                            <label class="block text-sm font-medium mb-1" for="sport_id">
                                {{ __('event_applications.labels.sport') }}
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
                    </div>
                </div>
            </div>

            {{-- Card 2: Event Details --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
                 x-show="loaded"
                 x-transition:enter="transition ease-out duration-500 delay-150"
                 x-transition:enter-start="opacity-0 transform translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0">

                <div class="border-b border-gray-200 bg-gray-50/50 px-5 py-3">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-100">
                            <x-heroicon-o-calendar-days class="w-4 h-4 text-emerald-600" />
                        </div>
                        <span class="text-sm font-semibold text-gray-700">{{ __('event_applications.sections.event_details') }}</span>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Start Date --}}
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

                        {{-- End Date --}}
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

                        {{-- District --}}
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

                        {{-- Municipality --}}
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
                    </div>
                </div>
            </div>

            {{-- Card 3: Contact & Audience --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
                 x-show="loaded"
                 x-transition:enter="transition ease-out duration-500 delay-200"
                 x-transition:enter-start="opacity-0 transform translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0">

                <div class="border-b border-gray-200 bg-gray-50/50 px-5 py-3">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100">
                            <x-heroicon-o-user-group class="w-4 h-4 text-blue-600" />
                        </div>
                        <span class="text-sm font-semibold text-gray-700">{{ __('event_applications.sections.responsible_contact') }}</span>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Responsible Name --}}
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

                        {{-- Responsible Phone --}}
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

                        {{-- Target Audience --}}
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

                        {{-- Expected Participants --}}
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
                </div>
            </div>

            {{-- Footer Action Bar --}}
            <div class="bg-gradient-to-r from-slate-50 to-gray-50 rounded-xl border border-gray-200 overflow-hidden"
                 x-show="loaded"
                 x-transition:enter="transition ease-out duration-500 delay-[250ms]"
                 x-transition:enter-start="opacity-0 transform translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0">
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 p-2 rounded-lg bg-white border border-gray-200 shadow-sm">
                                <x-heroicon-o-check-circle class="w-5 h-5 text-gray-600" />
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">{{ __('event_applications.footer.ready_to_save') }}</h3>
                                <p class="mt-0.5 text-sm text-gray-500">{{ __('event_applications.footer.review_before_saving') }}</p>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <button type="button" wire:click="cancel"
                                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200">
                                {{ __('common.cancel') }}
                            </button>

                            <button type="button" wire:click="saveDraft"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200">
                                <x-heroicon-m-bookmark class="w-4 h-4" />
                                <span wire:loading.remove wire:target="saveDraft">{{ __('event_applications.actions.save_draft') }}</span>
                                <span wire:loading wire:target="saveDraft">{{ __('common.saving') }}...</span>
                            </button>

                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-lg shadow-sm hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                <x-heroicon-m-paper-airplane class="w-4 h-4" />
                                <span wire:loading.remove wire:target="submitApplication">{{ __('event_applications.actions.submit_application') }}</span>
                                <span wire:loading wire:target="submitApplication">{{ __('common.submitting') }}...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
