@php
    $tabLookup = [
        'name' => 'settings',
        'acronym' => 'settings',
        'committee_id' => 'settings',
        'certification_category' => 'settings',
        'professional_role_id' => 'settings',
        'certification_view' => 'settings',
        'license_id' => 'instructor',
        'parent_id' => 'instructor',
        'parent' => 'instructor',
        'is_available' => 'pricing',
        'requester_model' => 'pricing',
        'allow_entity_group_request' => 'pricing',
        'requires_admin_validation' => 'pricing',
        'unit_value' => 'pricing',
        'unit_value_individual' => 'pricing',
        'unit_value_entity' => 'pricing',
        'tax_percentage' => 'pricing',
        'tax_value' => 'pricing',
        'offset_initial' => 'numbering',
        'offset_current' => 'numbering',
        'minimum_age' => 'quality',
        'theoretical_sessions' => 'quality',
        'confined_water_sessions' => 'quality',
        'open_water_sessions' => 'quality',
        'roles' => 'roles',
    ];

    $activeTab = 'settings';
    foreach ($errors->keys() as $errorKey) {
        $baseKey = \Illuminate\Support\Str::before($errorKey, '.');
        if (isset($tabLookup[$baseKey])) {
            $activeTab = $tabLookup[$baseKey];
            break;
        }
    }
@endphp

<div x-data="{ activeTab: @js($activeTab) }" class="card">
    <div class="border-b border-slate-200">
        <nav class="flex flex-wrap -mb-px">
            <button
                type="button"
                @click="activeTab = 'settings'"
                :class="activeTab === 'settings' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-600 hover:text-slate-800'"
                class="px-4 py-3 text-sm font-medium border-b-2 transition-colors">
                {{ __('certifications.form.tabs.settings') }}
            </button>
            <button
                type="button"
                @click="activeTab = 'instructor'"
                :class="activeTab === 'instructor' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-600 hover:text-slate-800'"
                class="px-4 py-3 text-sm font-medium border-b-2 transition-colors">
                {{ __('certifications.form.tabs.instructor') }}
            </button>
            <button
                type="button"
                @click="activeTab = 'roles'"
                :class="activeTab === 'roles' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-600 hover:text-slate-800'"
                class="px-4 py-3 text-sm font-medium border-b-2 transition-colors">
                {{ __('certifications.form.tabs.roles_permissions') }}
            </button>
            <button
                type="button"
                @click="activeTab = 'pricing'"
                :class="activeTab === 'pricing' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-600 hover:text-slate-800'"
                class="px-4 py-3 text-sm font-medium border-b-2 transition-colors">
                {{ __('certifications.form.tabs.pricing') }}
            </button>
            <button
                type="button"
                @click="activeTab = 'numbering'"
                :class="activeTab === 'numbering' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-600 hover:text-slate-800'"
                class="px-4 py-3 text-sm font-medium border-b-2 transition-colors">
                {{ __('certifications.form.tabs.numbering') }}
            </button>
            <button
                type="button"
                @click="activeTab = 'quality'"
                :class="activeTab === 'quality' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-600 hover:text-slate-800'"
                class="px-4 py-3 text-sm font-medium border-b-2 transition-colors">
                {{ __('certifications.form.tabs.quality') }}
            </button>
        </nav>
    </div>

    <div class="p-6 space-y-8">
        <div x-show="activeTab === 'settings'" x-cloak x-transition.opacity class="space-y-6">
            <div class="information-box flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="#9e9e9e" fill="none" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <circle cx="12" cy="12" r="9" />
                    <line x1="12" y1="8" x2="12.01" y2="8" />
                    <polyline points="11 12 12 12 12 16 13 16" />
                </svg>
                <p class="text-sm leading-relaxed">
                    {{ __('Use the following form to create a certification setting') }}<br>
                    <strong>{{ __('Choose the correct Committee for the certification.') }}</strong>
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1" for="name">
                        {{ __('certifications.form.fields.certification_name') }}
                        <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" class="form-input w-full @error('name') border-rose-300 @enderror" value="{{ old('name', $certification->name) }}" required autofocus>
                    @error('name')
                        <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" for="acronym">{{ __('certifications.form.fields.acronym') }}</label>
                    <input type="text" name="acronym" id="acronym" class="form-input w-full @error('acronym') border-rose-300 @enderror" value="{{ old('acronym', $certification->acronym ?? '') }}" maxlength="10">
                    @error('acronym')
                        <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" for="committee_id">
                        {{ __('certifications.form.fields.committee') }}
                        <span class="text-rose-500">*</span>
                    </label>
                    <select name="committee_id" id="committee_id" x-ref="committeeSelect" class="form-select w-full @error('committee_id') border-rose-300 @enderror" required>
                        <option value="" disabled {{ old('committee_id', $certification->committee_id) ? '' : 'selected' }}>
                            {{ __('-- Select an option --') }}
                        </option>
                        @foreach ($committees as $committee)
                            <option value="{{ $committee->id }}" data-international="{{ $committee->is_international ? '1' : '0' }}" @selected(old('committee_id', $certification->committee_id) == $committee->id)>
                                {{ $committee->name }} {{ $committee->is_international ? '(' . config('branding.international.short_name', 'IF') . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('committee_id')
                        <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" for="certification_category">
                        {{ __('certifications.form.fields.certification_category') }}
                        <span class="text-rose-500">*</span>
                    </label>
                    <select name="certification_category" id="certification_category" class="form-select w-full @error('certification_category') border-rose-300 @enderror" required>
                        <option value="" disabled {{ old('certification_category', $certification->certification_category) ? '' : 'selected' }}>
                            {{ __('-- Select an option --') }}
                        </option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->value }}" @selected(old('certification_category', $certification->certification_category) == $category->value)>
                                {{ $category->value }}
                            </option>
                        @endforeach
                    </select>
                    @error('certification_category')
                        <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2 xl:col-span-1">
                    <div class="flex items-start gap-2 pt-1"
                         x-data="{
                            committeeData: @js($committees->keyBy('id')->map(fn($c) => $c->is_international)),
                            get isInternational() {
                                const select = document.getElementById('committee_id');
                                return select ? this.committeeData[select.value] || false : false;
                            }
                         }"
                         x-init="document.getElementById('committee_id').addEventListener('change', () => $el.dispatchEvent(new Event('update')))">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                  :class="isInternational ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'"
                                  @update.window="$el.textContent = isInternational ? '{{ __('International') }}' : '{{ __('National') }}'">
                                <span x-text="isInternational ? '{{ __('International') }}' : '{{ __('National') }}'"></span>
                            </span>
                        </div>
                        <div class="text-sm">
                            <span class="font-medium">{{ __('certifications.form.fields.international_label') }}</span>
                            <span class="block text-xs text-slate-500 mt-1">
                                {{ __('Determined by the selected committee') }}
                            </span>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" for="professional_role_id">{{ __('certifications.form.fields.professional_role') }}</label>
                    <select name="professional_role_id" id="professional_role_id" class="form-select w-full @error('professional_role_id') border-rose-300 @enderror">
                        <option value="" {{ old('professional_role_id', $certification->professional_role_id) ? '' : 'selected' }}></option>
                        @foreach ($professionalRoles as $type)
                            <option value="{{ $type->id }}" @selected(old('professional_role_id', $certification->professional_role_id) == $type->id)>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('professional_role_id')
                        <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" for="certification_view_input">{{ __('certifications.form.fields.certification_view') }}</label>
                    @if (!empty($certification->certification_view))
                        <img width="52" class="mb-2" src="{{ Storage::disk('public')->url('img/cards/' . $certification->certification_view) }}" alt="{{ $certification->name }}">
                    @endif
                    <input name="certification_view" id="certification_view_input" type="file" class="form-input w-full @error('certification_view') border-rose-300 @enderror">
                    @error('certification_view')
                        <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'instructor'" x-cloak x-transition.opacity class="space-y-6">
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-slate-800">{{ __('certifications.form.sections.instructor') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if ($licenses->count() > 0)
                        <div>
                            <label class="block text-sm font-medium mb-1" for="license_id">{{ __('certifications.form.fields.related_license') }}</label>
                            <select name="license_id" id="license_id" class="form-select w-full @error('license_id') border-rose-300 @enderror">
                                <option value="" {{ old('license_id', $certification->license_id) ? '' : 'selected' }}></option>
                                @foreach ($licenses as $license)
                                    <option value="{{ $license->id }}" @selected(old('license_id', $certification->license_id) == $license->id)>
                                        {{ $license->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('license_id')
                                <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    @if (optional(Request()->query('filter'))['committee'] != 'sport')
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1" for="parent_id">{{ __('certifications.form.fields.instructor_certification') }}</label>
                            <livewire:input.select-multiple
                                wire:model.live="parent"
                                :items="$parents->pluck('name', 'id')"
                                inputId="parent_id"
                                inputName="parent_id[]"
                                :inputSelected="!empty($certification->parents) ? $certification->parents->pluck('id')->toArray() : null"
                                :multiple="true"
                            />
                            @error('parent_id')
                                <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'roles'" x-cloak x-transition.opacity class="space-y-6">
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-slate-800">{{ __('certifications.form.tabs.roles_permissions') }}</h3>

                <div class="information-box flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="#9e9e9e" fill="none" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <circle cx="12" cy="12" r="9" />
                        <line x1="12" y1="8" x2="12.01" y2="8" />
                        <polyline points="11 12 12 12 12 16 13 16" />
                    </svg>
                    <p class="text-sm leading-relaxed">
                        {{ __('certifications.form.helpers.roles_help') }}
                    </p>
                </div>

                @php
                    $selectedRoles = old('roles', isset($certification->id) ? $certification->roles->pluck('id')->toArray() : []);
                @endphp

                <div class="bg-slate-50 rounded-lg p-4">
                    @if(isset($roles) && $roles->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($roles as $role)
                                <label class="flex items-start">
                                    <input type="checkbox"
                                           name="roles[]"
                                           value="{{ $role->id }}"
                                           class="form-checkbox h-4 w-4 text-blue-600 mt-0.5 @error('roles') border-rose-300 @enderror"
                                           @if(in_array($role->id, $selectedRoles)) checked @endif>
                                    <div class="ml-2">
                                        <span class="text-sm font-medium text-slate-700">{{ $role->name }}</span>
                                        @if($role->description)
                                            <p class="text-xs text-slate-500 mt-0.5">{{ $role->description }}</p>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-500">{{ __('certifications.form.helpers.no_roles_available') }}</p>
                    @endif
                </div>

                @error('roles')
                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div x-show="activeTab === 'pricing'" x-cloak x-transition.opacity class="space-y-8">
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-slate-800">{{ __('certifications.form.sections.availability') }}</h3>
                <input type="hidden" name="requester_model" value="Entity">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <div class="flex items-start gap-2">
                            <input type="hidden" name="is_available" value="0">
                            <input type="checkbox" name="is_available" id="is_available" value="1" class="form-checkbox mt-1 @error('is_available') border-rose-300 @enderror" {{ old('is_available', $certification->is_available ?? true) ? 'checked' : '' }}>
                            <label class="text-sm" for="is_available">
                                <span class="font-medium">{{ __('certifications.form.fields.available_for_purchase') }}</span>
                            </label>
                        </div>
                        @error('is_available')
                            <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <div class="flex items-start gap-2">
                            <input type="hidden" name="allow_entity_group_request" value="0">
                            <input type="checkbox" name="allow_entity_group_request" id="allow_entity_group_request" value="1" class="form-checkbox mt-1 @error('allow_entity_group_request') border-rose-300 @enderror" {{ old('allow_entity_group_request', $certification->allow_entity_group_request ?? false) ? 'checked' : '' }}>
                            <label class="text-sm" for="allow_entity_group_request">
                                <span class="font-medium">{{ __('certifications.form.fields.allow_group_purchase') }}</span>
                                <span class="block text-xs text-slate-500 mt-1">{{ __('certifications.form.fields.allow_group_purchase_help') }}</span>
                            </label>
                        </div>
                        @error('allow_entity_group_request')
                            <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <div class="flex items-start gap-2">
                            <input type="hidden" name="requires_admin_validation" value="0">
                            <input type="checkbox" name="requires_admin_validation" id="requires_admin_validation" value="1" class="form-checkbox mt-1 @error('requires_admin_validation') border-rose-300 @enderror" {{ old('requires_admin_validation', $certification->requires_admin_validation ?? false) ? 'checked' : '' }}>
                            <label class="text-sm" for="requires_admin_validation">
                                <span class="font-medium">{{ __('certifications.form.fields.requires_admin_validation') }}</span>
                                <span class="block text-xs text-slate-500 mt-1">{{ __('certifications.form.fields.requires_admin_validation_help') }}</span>
                            </label>
                        </div>
                        @error('requires_admin_validation')
                            <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-slate-800">{{ __('certifications.form.sections.pricing') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1" for="digital_price">{{ __('certifications.form.fields.digital_price') }} (€)</label>
                        <input type="number" name="digital_price" id="digital_price" step="0.01" min="0" class="form-input w-full @error('digital_price') border-rose-300 @enderror" value="{{ old('digital_price', $certification->digital_price ?? '0.00') }}" placeholder="0.00">
                        <p class="text-xs text-slate-500 mt-1">{{ __('certifications.form.helpers.digital_price_help') }}</p>
                        @error('digital_price')
                            <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="digital_plus_card_price">{{ __('certifications.form.fields.digital_plus_card_price') }} (€)</label>
                        <input type="number" name="digital_plus_card_price" id="digital_plus_card_price" step="0.01" min="0" class="form-input w-full @error('digital_plus_card_price') border-rose-300 @enderror" value="{{ old('digital_plus_card_price', $certification->digital_plus_card_price) }}" placeholder="{{ __('certifications.form.helpers.leave_empty_no_card') }}">
                        <p class="text-xs text-slate-500 mt-1">{{ __('certifications.form.helpers.digital_plus_card_price_help') }}</p>
                        @error('digital_plus_card_price')
                            <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="tax_percentage">{{ __('certifications.form.fields.tax_percentage') }} (%)</label>
                        <input type="number" name="tax_percentage" id="tax_percentage" step="0.01" min="0" max="100" class="form-input w-full @error('tax_percentage') border-rose-300 @enderror" value="{{ old('tax_percentage', $certification->tax_percentage ?? '0.00') }}" placeholder="0.00">
                        @error('tax_percentage')
                            <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium mb-1" for="moloni_reference">{{ __('moloni.product_reference') }}</label>
                    <input type="text" name="moloni_reference" id="moloni_reference" maxlength="50" class="form-input w-full max-w-xs @error('moloni_reference') border-rose-300 @enderror" value="{{ old('moloni_reference', $certification->moloni_reference ?? '') }}">
                    <p class="text-xs text-slate-500 mt-1">{{ __('moloni.product_reference_help') }}</p>
                    @error('moloni_reference')
                        <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'numbering'" x-cloak x-transition.opacity class="space-y-6">
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-slate-800">{{ __('certifications.form.sections.numbering') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1" for="offset_initial">{{ __('certifications.form.fields.offset_initial') }}</label>
                        <input name="offset_initial" id="offset_initial" type="text" class="form-input w-full @error('offset_initial') border-rose-300 @enderror" value="{{ old('offset_initial', $certification->offset_initial) }}">
                        @error('offset_initial')
                            <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="offset_current">{{ __('certifications.form.fields.offset_current') }}</label>
                        <input name="offset_current" id="offset_current" type="text" class="form-input w-full @error('offset_current') border-rose-300 @enderror" value="{{ old('offset_current', $certification->offset_current) }}">
                        @error('offset_current')
                            <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'quality'" x-cloak x-transition.opacity class="space-y-6">
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-slate-800">{{ __('certifications.form.sections.quality_control') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1" for="minimum_age">{{ __('certifications.form.fields.minimum_age') }}</label>
                        <input type="text" name="minimum_age" id="minimum_age" class="form-input w-full @error('minimum_age') border-rose-300 @enderror" value="{{ old('minimum_age', $certification->minimum_age) }}" min="0">
                        @error('minimum_age')
                            <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="theoretical_sessions">{{ __('certifications.form.fields.theoretical_sessions') }}</label>
                        <input type="text" name="theoretical_sessions" id="theoretical_sessions" class="form-input w-full @error('theoretical_sessions') border-rose-300 @enderror" value="{{ old('theoretical_sessions', $certification->theoretical_sessions) }}" min="0">
                        @error('theoretical_sessions')
                            <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="confined_water_sessions">{{ __('certifications.form.fields.confined_water_sessions') }}</label>
                        <input type="text" name="confined_water_sessions" id="confined_water_sessions" class="form-input w-full @error('confined_water_sessions') border-rose-300 @enderror" value="{{ old('confined_water_sessions', $certification->confined_water_sessions) }}" min="0">
                        @error('confined_water_sessions')
                            <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="open_water_sessions">{{ __('certifications.form.fields.open_water_sessions') }}</label>
                        <input type="text" name="open_water_sessions" id="open_water_sessions" class="form-input w-full @error('open_water_sessions') border-rose-300 @enderror" value="{{ old('open_water_sessions', $certification->open_water_sessions) }}" min="0">
                        @error('open_water_sessions')
                            <p class="text-xs mt-1 text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap justify-end gap-3 pt-4 border-t border-slate-200">
            <a class="btn btn-secondary" href="{{ route(Request::segment(1) . '.certification.index') }}">
                {{ __('common.back') }}
            </a>
            <button type="submit" class="btn btn-primary">
                {{ __('common.save') }}
            </button>
        </div>
    </div>
</div>
