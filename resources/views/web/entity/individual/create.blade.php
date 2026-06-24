<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between items-center">
            <h1 class="page-first-title">{{ __('individual.create_individual') }}</h1>
        </div>

        <form action="{{ route('entity.individual.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="federation_id" value="{{ $federation->id }}">
            <input type="hidden" name="entity_id" value="{{ $entity->id }}">

            <div class="space-y-6">

                <!-- Personal Information -->
                <div class="card">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-slate-800 mb-6">
                            {{ __('individual.personal_information') }}
                        </h3>

                        <!-- Photo -->
                        <div class="mb-6">
                            <x-forms.input-profile-avatar label="{{ __('main.photo') }}"
                                                          :old="$individual"
                                                          required />
                            <p class="text-xs text-slate-500 mt-2">{{ __('individual.photo_max_size_hint') }}</p>
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
                                    <option value="" selected disabled>{{ __('common.select_option') }}</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}"
                                                @selected(old('country_id', $individual->country_id ?? '') == $country->id)>
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
                                       value="{{ old('birthdate', $individual->birthdate ?? '') }}"
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
                                    <option value="" selected disabled>{{ __('common.select_option') }}</option>
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
                                    {{ __('individual.phone') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input id="phone"
                                       class="form-input w-full {{ $errors->has('phone') ? 'border-rose-300' : '' }}"
                                       type="tel"
                                       name="phone"
                                       value="{{ old('phone', $individual->phone ?? '') }}"
                                       required />
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
                                {{ __('main.address') }}
                                <span class="text-rose-500">*</span>
                            </label>
                            <input id="address"
                                   class="form-input w-full {{ $errors->has('address') ? 'border-rose-300' : '' }}"
                                   type="text"
                                   name="address"
                                   placeholder="{{ __('individual.address_placeholder') }}"
                                   value="{{ old('address', $individual->address ?? '') }}"
                                   required />
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
                                <select name="district_id"
                                        id="district_id"
                                        class="form-select w-full {{ $errors->has('district_id') ? 'border-rose-300' : '' }}"
                                        required>
                                    <option value="" selected disabled>{{ __('common.select_option') }}</option>
                                    <option value="outside_portugal" @selected(old('district_id') == 'outside_portugal')>
                                        {{ __('main.outside_portugal') }}
                                    </option>
                                    @foreach($districts as $district)
                                        <option value="{{ $district->id }}" @selected(old('district_id') == $district->id)>
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
                                    {{ __('main.location') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input id="location"
                                       class="form-input w-full {{ $errors->has('location') ? 'border-rose-300' : '' }}"
                                       type="text"
                                       name="location"
                                       value="{{ old('location', $individual->location ?? '') }}"
                                       required />
                                @error('location')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Postal Code -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="postal_code">
                                    {{ __('main.postal_code') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input id="postal_code"
                                       class="form-input w-full {{ $errors->has('postal_code') ? 'border-rose-300' : '' }}"
                                       type="text"
                                       name="postal_code"
                                       value="{{ old('postal_code', $individual->postal_code ?? '') }}"
                                       required />
                                @error('postal_code')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fiscal & Identification Documents -->
                <div class="card">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-slate-800 mb-6">
                            {{ __('main.identification_document') }}
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
                                    {{ __('main.identification_document_type') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <select id="doc_ref_type"
                                        class="form-select w-full {{ $errors->has('doc_ref_type') ? 'border-rose-300' : '' }}"
                                        name="doc_ref_type"
                                        required>
                                    <option value="" selected disabled>{{ __('common.select_option') }}</option>
                                    <option value="national_id_number"
                                            @selected(old('doc_ref_type', $individual->doc_ref_type ?? '') === 'national_id_number')>
                                        {{ __('main.national_id_number') }}
                                    </option>
                                    <option value="passport_number"
                                            @selected(old('doc_ref_type', $individual->doc_ref_type ?? '') === 'passport_number')>
                                        {{ __('main.passport_number') }}
                                    </option>
                                </select>
                                @error('doc_ref_type')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Document Number -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="doc_ref">
                                    {{ __('main.identification_document_number') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input id="doc_ref"
                                       class="form-input w-full {{ $errors->has('doc_ref') ? 'border-rose-300' : '' }}"
                                       type="text"
                                       name="doc_ref"
                                       value="{{ old('doc_ref', $individual->doc_ref ?? '') }}"
                                       required />
                                @error('doc_ref')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Expiry Date -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="doc_ref_validation_date">
                                    {{ __('main.expire_date') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input id="doc_ref_validation_date"
                                       class="form-input w-full {{ $errors->has('doc_ref_validation_date') ? 'border-rose-300' : '' }}"
                                       type="date"
                                       name="doc_ref_validation_date"
                                       value="{{ old('doc_ref_validation_date', $individual->doc_ref_validation_date ?? '') }}"
                                       required />
                                @error('doc_ref_validation_date')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Login Information -->
                <div class="card">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-slate-800 mb-2">
                            {{ __('individual.user_login_information') }}
                        </h3>
                        <p class="text-sm text-slate-500 mb-6">{{ __('individual.user_login_description') }}</p>

                        <div class="max-w-md">
                            <label class="block text-sm font-medium mb-1" for="email">
                                {{ __('individual.login_email') }}
                                <span class="text-rose-500">*</span>
                            </label>
                            <input id="email"
                                   class="form-input w-full {{ $errors->has('email') ? 'border-rose-300' : '' }}"
                                   type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required />
                            <p class="text-xs text-slate-500 mt-1">{{ __('individual.email_credential_help') }}</p>
                            @error('email')
                                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Social Media Links -->
                <div class="card">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-slate-800 mb-2">
                            {{ __('main.social_media_links') }}
                        </h3>
                        <p class="text-sm text-slate-500 mb-6">{{ __('individual.social_media_optional') }}</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Facebook -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="facebook_url">
                                    {{ __('main.facebook_url') }}
                                </label>
                                <input id="facebook_url"
                                       class="form-input w-full {{ $errors->has('facebook_url') ? 'border-rose-300' : '' }}"
                                       type="url"
                                       name="facebook_url"
                                       placeholder="https://facebook.com/yourprofile"
                                       value="{{ old('facebook_url', $individual->facebook_url ?? '') }}" />
                                @error('facebook_url')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- X (Twitter) -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="x_url">
                                    {{ __('main.x_url') }}
                                </label>
                                <input id="x_url"
                                       class="form-input w-full {{ $errors->has('x_url') ? 'border-rose-300' : '' }}"
                                       type="url"
                                       name="x_url"
                                       placeholder="https://x.com/yourprofile"
                                       value="{{ old('x_url', $individual->x_url ?? '') }}" />
                                @error('x_url')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Instagram -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="instagram_url">
                                    {{ __('main.instagram_url') }}
                                </label>
                                <input id="instagram_url"
                                       class="form-input w-full {{ $errors->has('instagram_url') ? 'border-rose-300' : '' }}"
                                       type="url"
                                       name="instagram_url"
                                       placeholder="https://instagram.com/yourprofile"
                                       value="{{ old('instagram_url', $individual->instagram_url ?? '') }}" />
                                @error('instagram_url')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- LinkedIn -->
                            <div>
                                <label class="block text-sm font-medium mb-1" for="linkedin_url">
                                    {{ __('main.linkedin_url') }}
                                </label>
                                <input id="linkedin_url"
                                       class="form-input w-full {{ $errors->has('linkedin_url') ? 'border-rose-300' : '' }}"
                                       type="url"
                                       name="linkedin_url"
                                       placeholder="https://linkedin.com/in/yourprofile"
                                       value="{{ old('linkedin_url', $individual->linkedin_url ?? '') }}" />
                                @error('linkedin_url')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms and Privacy Policy Acceptance -->
                <div class="card">
                    <div class="p-6">
                        <div class="border-2 border-amber-300 bg-amber-50 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1">
                                    <h4 class="text-sm font-semibold text-amber-800">{{ __('individual.terms_privacy_title') }}</h4>
                                    <p class="mt-2 text-sm text-gray-700">
                                        {{ __('individual.terms_privacy_text') }}
                                    </p>
                                    <div class="mt-4">
                                        <label class="flex items-start gap-3 cursor-pointer group">
                                            <input type="checkbox"
                                                name="terms_accepted"
                                                id="terms_accepted"
                                                value="1"
                                                class="mt-0.5 h-5 w-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500 cursor-pointer {{ $errors->has('terms_accepted') ? 'border-rose-500' : '' }}"
                                                {{ old('terms_accepted') ? 'checked' : '' }}
                                                required />
                                            <span class="text-sm font-medium text-gray-800 group-hover:text-gray-900">
                                                {{ __('individual.terms_privacy_checkbox') }}
                                            </span>
                                        </label>
                                        @error('terms_accepted')
                                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <x-forms.card-form-submit :backRoute="Request::segment(1) . '.individual.index'"
                                          :buttonText="__('Save record')" />

            </div>
        </form>

    </div>
</x-layout>
