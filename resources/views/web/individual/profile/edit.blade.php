<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between items-center">
            <h1 class="page-first-title">{{ __('profile.edit_profile_of') }} {{ $individual->name . ' ' . $individual->surname }}</h1>
        </div>

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif

        @if (session('success'))
            <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 p-4">
                <p class="text-sm text-emerald-800">{{ session('success') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 p-4">
                <p class="text-sm font-medium text-rose-800 mb-2">{{ __('profile.form_has_errors') }}</p>
                <ul class="list-disc list-inside text-xs text-rose-700 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route(Request::segment(1).'.individual.update') }}" method="POST"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="space-y-6">

                <!-- Personal Information -->
                <div class="card">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-slate-800 mb-6">
                            {{ __('profile.individual_information') }}
                        </h3>

                        <!-- Photo -->
                        <div class="mb-6">
                            <x-forms.input-profile-avatar :label="__('profile.photo_avatar')"
                                                          :old="$individual"
                                                          :required="false" />
                            <p class="text-xs text-slate-500 mt-2">{{ __('individual.photo_max_size_hint') }}</p>
                            @error('logo')
                                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Name Fields -->
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-4">
                            <!-- First Name -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="name">
                                    {{ __('main.first_name') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input id="name"
                                       class="form-input w-full {{ $errors->has('name') ? 'border-rose-300' : '' }}"
                                       type="text"
                                       name="name"
                                       value="{{ old('name', $individual->name ?? '') }}"
                                       required />
                                <p class="text-xs text-slate-500 mt-1">{{ __('individual.single_name_hint') }}</p>
                                @error('name')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Last Name -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="surname">
                                    {{ __('main.last_name') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input id="surname"
                                       class="form-input w-full {{ $errors->has('surname') ? 'border-rose-300' : '' }}"
                                       type="text"
                                       name="surname"
                                       value="{{ old('surname', $individual->surname ?? '') }}"
                                       required />
                                <p class="text-xs text-slate-500 mt-1">{{ __('individual.single_name_hint') }}</p>
                                @error('surname')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Full Name -->
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium mb-1" for="native_name">
                                    {{ __('individual.full_name') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input id="native_name"
                                       class="form-input w-full {{ $errors->has('native_name') ? 'border-rose-300' : '' }}"
                                       type="text"
                                       name="native_name"
                                       value="{{ old('native_name', $individual->native_name ?? '') }}"
                                       required />
                                @error('native_name')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Latin alphabet names (for non-latin original names) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium mb-1" for="first_name_latin">
                                    {{ __('profile.first_name_latin') }}
                                </label>
                                <input id="first_name_latin"
                                       class="form-input w-full {{ $errors->has('first_name_latin') ? 'border-rose-300' : '' }}"
                                       type="text"
                                       name="first_name_latin"
                                       value="{{ old('first_name_latin', $individual->first_name_latin ?? '') }}" />
                                @error('first_name_latin')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1" for="last_name_latin">
                                    {{ __('profile.last_name_latin') }}
                                </label>
                                <input id="last_name_latin"
                                       class="form-input w-full {{ $errors->has('last_name_latin') ? 'border-rose-300' : '' }}"
                                       type="text"
                                       name="last_name_latin"
                                       value="{{ old('last_name_latin', $individual->last_name_latin ?? '') }}" />
                                @error('last_name_latin')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                            <p class="text-xs text-slate-500 sm:col-span-2 -mt-2">{{ __('profile.latin_name_hint') }}</p>
                        </div>

                        <!-- Nationality, Birthdate, Gender -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                            <!-- Nationality -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="country_id">
                                    {{ __('main.nationality') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <select name="country_id"
                                        id="country_id"
                                        class="form-select w-full {{ $errors->has('country_id') ? 'border-rose-300' : '' }}"
                                        required>
                                    <option value="" disabled>{{ __('common.select_option') }}</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}"
                                                @selected(old('country_id', $individual->country_id) == $country->id)>
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('country_id')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Birthdate -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="birthdate">
                                    {{ __('main.birthdate') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input id="birthdate"
                                       class="form-input w-full {{ $errors->has('birthdate') ? 'border-rose-300' : '' }}"
                                       type="date"
                                       name="birthdate"
                                       value="{{ old('birthdate', $individual->birthdate?->format('Y-m-d') ?? '') }}"
                                       max="{{ date('Y-m-d') }}"
                                       required />
                                @error('birthdate')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Gender -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="gender">
                                    {{ __('individual.sex') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <select name="gender"
                                        id="gender"
                                        class="form-select w-full {{ $errors->has('gender') ? 'border-rose-300' : '' }}"
                                        required>
                                    <option value="" disabled>{{ __('common.select_option') }}</option>
                                    <option value="male" @selected(old('gender', $individual->gender) == 'male')>
                                        {{ __('individual.male') }}
                                    </option>
                                    <option value="female" @selected(old('gender', $individual->gender) == 'female')>
                                        {{ __('individual.female') }}
                                    </option>
                                </select>
                                @error('gender')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1" for="phone">
                                    {{ __('profile.phone') }}
                                </label>
                                <input id="phone"
                                       class="form-input w-full {{ $errors->has('phone') ? 'border-rose-300' : '' }}"
                                       type="tel"
                                       name="phone"
                                       value="{{ old('phone', $individual->phone ?? '') }}" />
                                @error('phone')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="card">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-slate-800 mb-6">
                            {{ __('main.address') }}
                        </h3>

                        <!-- Address - Full width -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1" for="address">
                                {{ __('profile.address') }}
                            </label>
                            <input id="address"
                                   class="form-input w-full {{ $errors->has('address') ? 'border-rose-300' : '' }}"
                                   type="text"
                                   name="address"
                                   placeholder="{{ __('individual.address_placeholder') }}"
                                   value="{{ old('address', $individual->address ?? '') }}" />
                            @error('address')
                                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- District, Location, Postal Code -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <!-- District -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="district_id">
                                    {{ __('main.district') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <select id="district_id"
                                        class="form-select w-full {{ $errors->has('district_id') ? 'border-rose-300' : '' }}"
                                        name="district_id"
                                        required>
                                    <option value="" disabled @selected(old('district_id', $individual->district_id) === null)>{{ __('common.select_option') }}</option>
                                    <option value="outside_portugal" @selected(old('district_id') === 'outside_portugal')>
                                        {{ __('main.outside_portugal') }}
                                    </option>
                                    @foreach ($districts as $district)
                                        <option value="{{ $district->id }}"
                                                @selected(old('district_id', $individual->district_id) == $district->id)>
                                            {{ $district->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('district_id')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Location -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="location">
                                    {{ __('profile.location') }}
                                </label>
                                <input id="location"
                                       class="form-input w-full {{ $errors->has('location') ? 'border-rose-300' : '' }}"
                                       type="text"
                                       name="location"
                                       value="{{ old('location', $individual->location ?? '') }}" />
                                @error('location')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Postal Code -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="postal_code">
                                    {{ __('profile.postal_code') }}
                                </label>
                                <input id="postal_code"
                                       class="form-input w-full {{ $errors->has('postal_code') ? 'border-rose-300' : '' }}"
                                       type="text"
                                       name="postal_code"
                                       value="{{ old('postal_code', $individual->postal_code ?? '') }}" />
                                @error('postal_code')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Identification Documents -->
                <div class="card">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-slate-800 mb-6">
                            {{ __('profile.identification_document') }}
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- VAT Number -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="vat_number">
                                    {{ __('individual.vat_number') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input id="vat_number"
                                       class="form-input w-full {{ $errors->has('vat_number') ? 'border-rose-300' : '' }}"
                                       type="text"
                                       name="vat_number"
                                       value="{{ old('vat_number', $individual->vat_number ?? '') }}"
                                       required />
                                @error('vat_number')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Document Type -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="doc_ref_type">
                                    {{ __('profile.identification_document_type') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <select id="doc_ref_type"
                                        class="form-select w-full {{ $errors->has('doc_ref_type') ? 'border-rose-300' : '' }}"
                                        name="doc_ref_type"
                                        required>
                                    <option value="" disabled>{{ __('common.select_option') }}</option>
                                    @foreach (\App\Enums\IndividualDocumentTypeEnum::cases() as $documentType)
                                        <option value="{{ $documentType->value }}"
                                                @selected(old('doc_ref_type', $individual->doc_ref_type ?? '') === $documentType->value)>
                                            {{ __($documentType->translationKey()) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('doc_ref_type')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Document Number -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="doc_ref">
                                    {{ __('profile.identification_document_number') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input id="doc_ref"
                                       class="form-input w-full {{ $errors->has('doc_ref') ? 'border-rose-300' : '' }}"
                                       type="text"
                                       name="doc_ref"
                                       value="{{ old('doc_ref', $individual->doc_ref) }}"
                                       required />
                                @error('doc_ref')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Expiry Date -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="doc_ref_validation_date">
                                    {{ __('profile.validation_date_of_identification_document') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input id="doc_ref_validation_date"
                                       class="form-input w-full {{ $errors->has('doc_ref_validation_date') ? 'border-rose-300' : '' }}"
                                       type="date"
                                       name="doc_ref_validation_date"
                                       value="{{ old('doc_ref_validation_date', $individual->doc_ref_validation_date?->format('Y-m-d')) }}"
                                       required />
                                @error('doc_ref_validation_date')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- National Federation Number -->
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1" for="national_federation_number">
                                    {{ __('main.national_federation_number') }}
                                </label>
                                <input id="national_federation_number"
                                       class="form-input w-full {{ $errors->has('national_federation_number') ? 'border-rose-300' : '' }}"
                                       type="text"
                                       name="national_federation_number"
                                       value="{{ old('national_federation_number', $individual->national_federation_number ?? '') }}" />
                                @error('national_federation_number')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Media Links -->
                <div class="card">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-slate-800 mb-2">
                            {{ __('profile.social_media_links') }}
                        </h3>
                        <p class="text-sm text-slate-500 mb-6">{{ __('individual.social_media_optional') }}</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Facebook -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="facebook_url">
                                    {{ __('profile.facebook_url') }}
                                </label>
                                <input id="facebook_url"
                                       class="form-input w-full {{ $errors->has('facebook_url') ? 'border-rose-300' : '' }}"
                                       type="url"
                                       name="facebook_url"
                                       placeholder="https://facebook.com/yourprofile"
                                       value="{{ old('facebook_url', $individual->facebook_url) }}" />
                                @error('facebook_url')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- X (Twitter) -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="x_url">
                                    {{ __('profile.x_url') }}
                                </label>
                                <input id="x_url"
                                       class="form-input w-full {{ $errors->has('x_url') ? 'border-rose-300' : '' }}"
                                       type="url"
                                       name="x_url"
                                       placeholder="https://x.com/yourprofile"
                                       value="{{ old('x_url', $individual->x_url) }}" />
                                @error('x_url')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Instagram -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="instagram_url">
                                    {{ __('profile.instagram_url') }}
                                </label>
                                <input id="instagram_url"
                                       class="form-input w-full {{ $errors->has('instagram_url') ? 'border-rose-300' : '' }}"
                                       type="url"
                                       name="instagram_url"
                                       placeholder="https://instagram.com/yourprofile"
                                       value="{{ old('instagram_url', $individual->instagram_url) }}" />
                                @error('instagram_url')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- LinkedIn -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="linkedin_url">
                                    {{ __('profile.linkedin_url') }}
                                </label>
                                <input id="linkedin_url"
                                       class="form-input w-full {{ $errors->has('linkedin_url') ? 'border-rose-300' : '' }}"
                                       type="url"
                                       name="linkedin_url"
                                       placeholder="https://linkedin.com/in/yourprofile"
                                       value="{{ old('linkedin_url', $individual->linkedin_url) }}" />
                                @error('linkedin_url')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Privacy / Public Registries Visibility -->
                <div class="card">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-slate-800 mb-2">
                            {{ __('profile.privacy_settings') }}
                        </h3>
                        <p class="text-sm text-slate-500 mb-6">{{ __('profile.privacy_settings_description') }}</p>

                        <div class="space-y-3">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="hidden" name="visible_in_coach_registry" value="0" />
                                <input type="checkbox"
                                       id="visible_in_coach_registry"
                                       name="visible_in_coach_registry"
                                       value="1"
                                       class="form-checkbox mt-0.5"
                                       @checked(old('visible_in_coach_registry', $individual->visible_in_coach_registry)) />
                                <span class="text-sm text-slate-700">{{ __('profile.visible_in_coach_registry') }}</span>
                            </label>

                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="hidden" name="visible_in_technical_official_registry" value="0" />
                                <input type="checkbox"
                                       id="visible_in_technical_official_registry"
                                       name="visible_in_technical_official_registry"
                                       value="1"
                                       class="form-checkbox mt-0.5"
                                       @checked(old('visible_in_technical_official_registry', $individual->visible_in_technical_official_registry)) />
                                <span class="text-sm text-slate-700">{{ __('profile.visible_in_technical_official_registry') }}</span>
                            </label>

                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="hidden" name="visible_in_diving_professional_registry" value="0" />
                                <input type="checkbox"
                                       id="visible_in_diving_professional_registry"
                                       name="visible_in_diving_professional_registry"
                                       value="1"
                                       class="form-checkbox mt-0.5"
                                       @checked(old('visible_in_diving_professional_registry', $individual->visible_in_diving_professional_registry)) />
                                <span class="text-sm text-slate-700">{{ __('profile.visible_in_diving_professional_registry') }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <x-forms.card-form-submit :backRoute="Request::segment(1) . '.individual.show'"
                                          :buttonText="__('profile.save_record')" />

            </div>

        </form>

    </div>

</x-layout>
