@section('title', __('individual.registration_title'))
<x-public-layout>
    <main class="relative min-h-screen">
        <section class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">

            <div class="pt-10">
                <x-authentication-card-logo />
            </div>
            <div class="w-full md:w-4/5 lg:w-3/4 md:mx-auto">

                <div class="px-4 sm:px-6 lg:px-8 py-8 w-full mx-auto">
                    <!-- Page header -->
                    <div class="mb-8 text-center">
                        <h1 class="text-2xl md:text-3xl text-slate-600 font-bold">{{ __('individual.individual_registration') }}</h1>
                    </div>

                    @include('components.layout.banner_message')

                    <form class="w-full" action="{{ route('public.individual.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <x-honeypot />

                        <div class="space-y-6">

                            <!-- Photo Section -->
                            <div class="card">
                                <div class="p-6">
                                    <h3 class="text-lg font-semibold text-slate-800 mb-6">
                                        {{ __('individual.photo') }}
                                    </h3>

                                    <div class="mb-2">
                                        <x-forms.input-profile-avatar label="{{ __('individual.photo') }} *" />
                                    </div>
                                    <p class="text-xs text-slate-500">{{ __('individual.photo_max_size_hint') }}</p>
                                </div>
                            </div>

                            <!-- Personal Information -->
                            <div class="card" x-data="{
                                firstName: '{{ old('name', '') }}',
                                lastName: '{{ old('surname', '') }}',
                                nativeName: '{{ old('native_name', '') }}',
                                updateNativeName() {
                                    const expected = (this.firstName + ' ' + this.lastName).trim();
                                    if (!this.nativeName || this.nativeName === expected || this.nativeName === (this.firstName.slice(0, -1) + ' ' + this.lastName).trim() || this.nativeName === (this.firstName + ' ' + this.lastName.slice(0, -1)).trim()) {
                                        this.nativeName = expected;
                                    }
                                }
                            }">
                                <div class="p-6">
                                    <h3 class="text-lg font-semibold text-slate-800 mb-6">
                                        {{ __('individual.personal_information') }}
                                    </h3>

                                    <!-- Name Fields -->
                                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-4">
                                        <!-- First Name -->
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="name">
                                                {{ __('individual.first_name') }}
                                                <span class="text-rose-500">*</span>
                                            </label>
                                            <input id="name"
                                                   class="form-input w-full {{ $errors->has('name') ? 'border-rose-300' : '' }}"
                                                   type="text"
                                                   name="name"
                                                   x-model="firstName"
                                                   @input="updateNativeName()"
                                                   required />
                                            <p class="text-xs text-slate-500 mt-1">{{ __('individual.single_name_hint') }}</p>
                                            @error('name')
                                                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Last Name -->
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="surname">
                                                {{ __('common.surname') }}
                                                <span class="text-rose-500">*</span>
                                            </label>
                                            <input id="surname"
                                                   class="form-input w-full {{ $errors->has('surname') ? 'border-rose-300' : '' }}"
                                                   type="text"
                                                   name="surname"
                                                   x-model="lastName"
                                                   @input="updateNativeName()"
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
                                                   x-model="nativeName"
                                                   required />
                                            @error('native_name')
                                                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Gender, Birthdate, Nationality -->
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                                        <!-- Gender -->
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="gender">
                                                {{ __('main.gender') }}
                                                <span class="text-rose-500">*</span>
                                            </label>
                                            <select name="gender"
                                                    id="gender"
                                                    class="form-select w-full {{ $errors->has('gender') ? 'border-rose-300' : '' }}"
                                                    required>
                                                <option value="" selected disabled>{{ __('common.select_option') }}</option>
                                                <option value="male" @selected(old('gender') == 'male')>{{ __('individual.male') }}</option>
                                                <option value="female" @selected(old('gender') == 'female')>{{ __('individual.female') }}</option>
                                            </select>
                                            @error('gender')
                                                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Birthdate -->
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="birthdate">
                                                {{ __('common.birthdate') }}
                                                <span class="text-rose-500">*</span>
                                            </label>
                                            <input id="birthdate"
                                                   class="form-input w-full {{ $errors->has('birthdate') ? 'border-rose-300' : '' }}"
                                                   type="date"
                                                   name="birthdate"
                                                   value="{{ old('birthdate') }}"
                                                   max="{{ date('Y-m-d') }}"
                                                   required />
                                            @error('birthdate')
                                                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Nationality -->
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="individual_country_id">
                                                {{ __('common.nationality') }}
                                                <span class="text-rose-500">*</span>
                                            </label>
                                            <select id="individual_country_id"
                                                    class="form-select w-full {{ $errors->has('individual_country_id') ? 'border-rose-300' : '' }}"
                                                    name="individual_country_id"
                                                    required>
                                                <option disabled selected>{{ __('common.select_option') }}</option>
                                                @foreach($countries as $key => $country)
                                                    <option value="{{ $key }}" @selected(old('individual_country_id') == $key)>{{ $country }}</option>
                                                @endforeach
                                            </select>
                                            @error('individual_country_id')
                                                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Phone -->
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="phone">
                                                {{ __('common.phone') }}
                                            </label>
                                            <input id="phone"
                                                   class="form-input w-full {{ $errors->has('phone') ? 'border-rose-300' : '' }}"
                                                   type="tel"
                                                   name="phone"
                                                   value="{{ old('phone') }}" />
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
                                            {{ __('individual.address') }}
                                        </label>
                                        <input id="address"
                                               class="form-input w-full {{ $errors->has('address') ? 'border-rose-300' : '' }}"
                                               type="text"
                                               name="address"
                                               placeholder="{{ __('individual.address_placeholder') }}"
                                               value="{{ old('address') }}" />
                                        @error('address')
                                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- District, Location, Postal Code -->
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <!-- District -->
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="district_id">
                                                {{ __('individual.district') }}
                                                <span class="text-rose-500">*</span>
                                            </label>
                                            <select id="district_id"
                                                    class="form-select w-full {{ $errors->has('district_id') ? 'border-rose-300' : '' }}"
                                                    name="district_id"
                                                    required>
                                                <option disabled selected>{{ __('common.select_option') }}</option>
                                                <option value="outside_portugal" @selected(old('district_id') == 'outside_portugal')>
                                                    {{ __('main.outside_portugal') }}
                                                </option>
                                                @foreach($districts as $key => $district)
                                                    <option value="{{ $key }}" @selected(old('district_id') == $key)>{{ $district }}</option>
                                                @endforeach
                                            </select>
                                            @error('district_id')
                                                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Location -->
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="location">
                                                {{ __('individual.location') }}
                                            </label>
                                            <input id="location"
                                                   class="form-input w-full {{ $errors->has('location') ? 'border-rose-300' : '' }}"
                                                   type="text"
                                                   name="location"
                                                   value="{{ old('location') }}" />
                                            @error('location')
                                                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Postal Code -->
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="postal_code">
                                                {{ __('individual.postal_code') }}
                                            </label>
                                            <input id="postal_code"
                                                   class="form-input w-full {{ $errors->has('postal_code') ? 'border-rose-300' : '' }}"
                                                   type="text"
                                                   name="postal_code"
                                                   value="{{ old('postal_code') }}" />
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
                                        {{ __('individual.identification_document') }}
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
                                                   value="{{ old('vat_number') }}"
                                                   required />
                                            @error('vat_number')
                                                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Document Type -->
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="doc_ref_type">
                                                {{ __('individual.document_type') }}
                                                <span class="text-rose-500">*</span>
                                            </label>
                                            <select id="doc_ref_type"
                                                    class="form-select w-full {{ $errors->has('doc_ref_type') ? 'border-rose-300' : '' }}"
                                                    name="doc_ref_type"
                                                    required>
                                                <option value="" disabled selected>{{ __('common.select_option') }}</option>
                                                <option value="identity_card" @selected(old('doc_ref_type') == 'identity_card')>
                                                    {{ __('individual.doc_types.identity_card') }}
                                                </option>
                                                <option value="citizen_card" @selected(old('doc_ref_type') == 'citizen_card')>
                                                    {{ __('individual.doc_types.citizen_card') }}
                                                </option>
                                                <option value="foreign_identity_card" @selected(old('doc_ref_type') == 'foreign_identity_card')>
                                                    {{ __('individual.doc_types.foreign_identity_card') }}
                                                </option>
                                                <option value="permanent_residence_card" @selected(old('doc_ref_type') == 'permanent_residence_card')>
                                                    {{ __('individual.doc_types.permanent_residence_card') }}
                                                </option>
                                                <option value="passport" @selected(old('doc_ref_type') == 'passport')>
                                                    {{ __('individual.doc_types.passport') }}
                                                </option>
                                            </select>
                                            @error('doc_ref_type')
                                                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Document Number -->
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="doc_ref">
                                                {{ __('individual.document_number') }}
                                                <span class="text-rose-500">*</span>
                                            </label>
                                            <input id="doc_ref"
                                                   class="form-input w-full {{ $errors->has('doc_ref') ? 'border-rose-300' : '' }}"
                                                   type="text"
                                                   name="doc_ref"
                                                   value="{{ old('doc_ref') }}"
                                                   required />
                                            @error('doc_ref')
                                                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Expiry Date -->
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="doc_ref_validation_date">
                                                {{ __('individual.expiry_date') }}
                                                <span class="text-rose-500">*</span>
                                            </label>
                                            <input id="doc_ref_validation_date"
                                                   class="form-input w-full {{ $errors->has('doc_ref_validation_date') ? 'border-rose-300' : '' }}"
                                                   type="date"
                                                   name="doc_ref_validation_date"
                                                   value="{{ old('doc_ref_validation_date') }}"
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
                                        {{ __('individual.login_credentials') }}
                                    </h3>
                                    <p class="text-sm text-slate-500 mb-6">{{ __('individual.login_credentials_description') }}</p>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <!-- Email -->
                                        <div>
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
                                            @error('email')
                                                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Password -->
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="password">
                                                {{ __('individual.password') }}
                                                <span class="text-rose-500">*</span>
                                            </label>
                                            <input id="password"
                                                   class="form-input w-full {{ $errors->has('password') ? 'border-rose-300' : '' }}"
                                                   type="password"
                                                   name="password"
                                                   required />
                                            @error('password')
                                                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Password Confirmation -->
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="password_confirmation">
                                                {{ __('individual.confirm_password') }}
                                                <span class="text-rose-500">*</span>
                                            </label>
                                            <input id="password_confirmation"
                                                   class="form-input w-full {{ $errors->has('password_confirmation') ? 'border-rose-300' : '' }}"
                                                   type="password"
                                                   name="password_confirmation"
                                                   required />
                                            @error('password_confirmation')
                                                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Entity Affiliation -->
                            <div class="card">
                                <div class="p-6">
                                    <h3 class="text-lg font-semibold text-slate-800 mb-2">
                                        {{ __('main.entity_affiliation') }}
                                    </h3>
                                    <p class="text-sm text-slate-500 mb-6">{{ __('main.entity_affiliation_info') }}</p>

                                    <div class="max-w-md">
                                        <label class="block text-sm font-medium mb-1" for="entity_id">
                                            {{ __('main.select_entity') }}
                                        </label>
                                        <select id="entity_id"
                                                name="entity_id"
                                                class="form-select w-full">
                                            <option value="">{{ __('main.no_entity_selected') }}</option>
                                            @if(isset($entities))
                                                @foreach($entities as $key => $entity)
                                                    <option value="{{ $key }}" @selected(old('entity_id') == $key)>{{ $entity }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('entity_id')
                                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Terms and Conditions -->
                            <div class="card">
                                <div class="p-6">
                                    <h3 class="text-lg font-semibold text-slate-800 mb-6">
                                        {{ __('individual.terms_and_conditions') }}
                                    </h3>

                                    <div class="space-y-4">
                                        <div class="flex items-start">
                                            <input id="terms"
                                                   class="form-checkbox mt-1"
                                                   type="checkbox"
                                                   name="terms"
                                                   required />
                                            <label for="terms" class="ml-2 text-sm">
                                                {{ __('individual.terms_declaration_prefix') }}
                                                <a href="{{ route('terms-of-service') }}" target="_blank" class="text-indigo-500 hover:text-indigo-600">{{ __('individual.terms_of_service') }}</a>
                                                {{ __('individual.terms_declaration_middle') }}
                                                <a href="{{ route('privacy-policy') }}" target="_blank" class="text-indigo-500 hover:text-indigo-600">{{ __('individual.privacy_policy') }}</a>.
                                                <span class="text-rose-500">*</span>
                                            </label>
                                        </div>
                                        @error('terms')
                                            <div class="text-xs text-rose-500">{{ $message }}</div>
                                        @enderror

                                        <div class="flex items-start">
                                            <input id="dataSharing"
                                                   class="form-checkbox mt-1"
                                                   type="checkbox"
                                                   name="data_sharing"
                                                   required />
                                            <label for="dataSharing" class="ml-2 text-sm">
                                                {{ __('individual.data_sharing_declaration_prefix') }}
                                                <a href="{{ route('data-sharing-policy') }}" target="_blank" class="text-indigo-500 hover:text-indigo-600">{{ __('individual.data_sharing_policy') }}</a>.
                                                <span class="text-rose-500">*</span>
                                            </label>
                                        </div>
                                        @error('data_sharing')
                                            <div class="text-xs text-rose-500">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end">
                                <button type="submit" class="btn btn-action px-8">
                                    {{ __('individual.submit_registration') }}
                                </button>
                            </div>

                        </div>

                    </form>

                </div>
            </div>
        </section>

    </main>
</x-public-layout>
