<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title"> {{ __('federation.create_individual') }} </h1>
        </div>


        <form action="{{ route('federation.individual.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="sm:flex sm:space-x-4">
                <div class="mb-8 w-full">
                    <div class="card">
                        <div class="grow flex flex-col gap-y-4">

                            <!-- Assignment -->
                            <section>
                                <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">
                                    {{ __('federation.individual_information') }}
                                </h3>

                                <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                                    <x-forms.input-profile-avatar label="{{ __('main.photo_avatar') }}"
                                                                  :old="$individual" />
                                </div>
                                <p class="text-xs text-slate-500 mt-2">{{ __('individual.photo_max_size_hint') }}</p>

                                <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                                    <div class="sm:w-1/3">
                                        <label class="block text-sm font-medium mb-1" for="name">{{ __('main.first_name') }}
                                            <span
                                                class="text-rose-500">*</span></label>
                                        <input id="name"
                                               class="form-input w-full {{ $errors->has('name') ? 'border-rose-300' : '' }}"
                                               type="text" name="name"
                                               value="{{ old('name', $individual->name ?? '') }}"
                                               required />

                                        @if ($errors->has('name'))
                                            <div class="text-xs mt-1 text-rose-500 h-2">
                                                {{ $errors->first('name') }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="sm:w-1/3">
                                        <label class="block text-sm font-medium mb-1" for="surname">
                                            {{ __('main.surname') }} <span
                                                class="text-rose-500">*</span></label>

                                        <input id="surname"
                                               class="form-input w-full {{ $errors->has('surname') ? 'border-rose-300' : '' }}"
                                               type="text" name="surname"
                                               value="{{ old('surname', $individual->surname ?? '') }}" required />

                                        @if ($errors->has('surname'))
                                            <div class="text-xs mt-1 text-rose-500 h-2">
                                                {{ $errors->first('surname') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">

                                    <div class="sm:w-1/3">
                                        <label class="block text-sm font-medium mb-1"
                                               for="country_id"> {{ __('main.nationality') }}
                                            <span class="text-rose-500">*</span></label>
                                        <select name="country_id" id="country_id"
                                                class="form-input w-full {{ $errors->has('country_id') ? 'border-rose-300' : '' }}"
                                                required>
                                            <option value="0" selected
                                                    disabled>{{ __('main.select_option') }}</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->id }}"
                                                        @if (old('country_id', $individual->country_id ?? '') == $country->id) selected @endif>{{ $country->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('country_id'))
                                            <div class="text-xs mt-1 text-rose-500 h-2">
                                                {{ $errors->first('country_id') }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="sm:w-1/3">
                                        <label class="block text-sm font-medium mb-1" for="birthdate">
                                            {{ __('main.birthdate') }} <span class="text-rose-500">*</span></label>
                                        <input id="birthdate"
                                               class="form-input w-full {{ $errors->has('birthdate') ? 'border-rose-300' : '' }}"
                                               type="date" name="birthdate"
                                               value="{{ old('birthdate', $individual->birthdate ?? '') }}"
                                               max="{{ date('Y-m-d') }}" required />

                                        @if ($errors->has('birthdate'))
                                            <div class="text-xs mt-1 text-rose-500 h-2">
                                                {{ $errors->first('birthdate') }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="sm:w-1/3">
                                        <label class="block text-sm font-medium mb-1" for="gender">
                                            {{ __('main.gender') }}
                                            <span class="text-rose-500">*</span></label>
                                        <select name="gender" id="gender"
                                                class="form-select w-full {{ $errors->has('gender') ? 'border-rose-300' : '' }}"
                                                required>
                                            <option value="" selected disabled>{{ __('main.select_option') }}</option>
                                            <option value="male"
                                                    @if (old('gender', $individual->gender) == 'male') selected @endif>
                                                {{ __('main.male') }}
                                            </option>
                                            <option value="female"
                                                    @if (old('gender', $individual->gender) == 'female') selected @endif>
                                                {{ __('main.female') }}
                                            </option>
                                        </select>

                                        @if ($errors->has('gender'))
                                            <div class="text-xs mt-1 text-rose-500 h-2">
                                                {{ $errors->first('gender') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                                    <div class="sm:w-1/2">
                                        <label class="block text-sm font-medium mb-1" for="vat_number">
                                            {{ __('main.vat_number') ?? 'VAT Number' }}
                                        </label>
                                        <input id="vat_number"
                                               class="form-input w-full {{ $errors->has('vat_number') ? 'border-rose-300' : '' }}"
                                               type="text" name="vat_number"
                                               value="{{ old('vat_number', $individual->vat_number ?? '') }}" />
                                        @if($errors->has('vat_number'))
                                            <div class="text-xs mt-1 text-rose-500 h-2">
                                                {{ $errors->first('vat_number') }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="sm:w-1/2">
                                        <label class="block text-sm font-medium mb-1" for="phone">
                                            {{ __('main.phone') ?? 'Phone' }}
                                        </label>
                                        <input id="phone"
                                               class="form-input w-full {{ $errors->has('phone') ? 'border-rose-300' : '' }}"
                                               type="text" name="phone"
                                               value="{{ old('phone', $individual->phone ?? '') }}" />
                                        @if($errors->has('phone'))
                                            <div class="text-xs mt-1 text-rose-500 h-2">
                                                {{ $errors->first('phone') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                                    <div class="sm:w-1/3">
                                        <input type="hidden" name="federation_id" value="{{ $federation->id }}">
                                        <label class=" block text-sm font-medium mb-1"
                                               for="national_federation_number">{{ __('federation.national_federation_number') }}</label>
                                        <input id="national_federation_number"
                                               class="form-input w-full {{ $errors->has('national_federation_number') ? 'border-rose-300' : '' }}"
                                               type="text" name="national_federation_number"
                                               value="{{ old('national_federation_number', $individual->national_federation_number ?? '') }}" />

                                        @if($errors->has('national_federation_number'))
                                            <div class="text-xs mt-1 text-rose-500 h-2">
                                                {{ $errors->first('national_federation_number') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- District -->
                                <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                                    <div class="sm:w-1/2">
                                        <label class="block text-sm font-medium mb-1" for="district_id">
                                            {{ __('main.district') }} <span class="text-rose-500">*</span>
                                        </label>
                                        <select id="district_id"
                                                name="district_id"
                                                class="form-select w-full {{ $errors->has('district_id') ? 'border-rose-300' : '' }}"
                                                required>
                                            <option value="">{{ __('main.select_district') }}</option>
                                            <option value="outside_portugal" {{ old('district_id') == 'outside_portugal' ? 'selected' : '' }}>
                                                {{ __('main.outside_portugal') }}
                                            </option>
                                            @foreach($districts as $district)
                                                <option value="{{ $district->id }}" {{ old('district_id') == $district->id ? 'selected' : '' }}>
                                                    {{ $district->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($errors->has('district_id'))
                                            <div class="text-xs mt-1 text-rose-500">{{ $errors->first('district_id') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">

                                    <div class="sm:w-3/6">
                                        <label class="block text-sm font-medium mb-1"
                                               for="address">{{ __('main.address') }}</label>
                                        <input id="address"
                                               class="form-input w-full {{ $errors->has('address') ? 'border-rose-300' : '' }}"
                                               type="text" name="address"
                                               value="{{ old('address', $individual->address ?? '') }}"
                                        />

                                        @if($errors->has('address'))
                                            <div class="text-xs mt-1 text-rose-500 h-2">
                                                {{ $errors->first('address') }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="sm:w-2/6">
                                        <label class="block text-sm font-medium mb-1"
                                               for="location">{{ __('main.location') }}</label>
                                        <input id="location"
                                               class="form-input w-full {{ $errors->has('location') ? 'border-rose-300' : '' }}"
                                               type="text" name="location"
                                               value="{{ old('location', $individual->location ?? '') }}"
                                        />

                                        @if($errors->has('location'))
                                            <div class="text-xs mt-1 text-rose-500 h-2">
                                                {{ $errors->first('location') }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="sm:w-1/6">
                                        <label class="block text-sm font-medium mb-1"
                                               for="postal_code">{{ __('main.postal_code') }}</label>
                                        <input id="postal_code"
                                               class="form-input w-full {{ $errors->has('postal_code') ? 'border-rose-300' : '' }}"
                                               type="text" name="postal_code"
                                               value="{{ old('postal_code', $individual->postal_code ?? '') }}"
                                        />

                                        @if($errors->has('postal_code'))
                                            <div class="text-xs mt-1 text-rose-500 h-2">
                                                {{ $errors->first('postal_code') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                            </section>

                            <!-- Member Categories -->
                            <section>
                                <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">
                                    {{ __('main.member_category') }}
                                </h3>
                                <div class="mt-5">
                                    <label class="block text-sm font-medium mb-1">
                                        {{ __('main.select_member_categories') }} <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="member_categories[]" value="sport_practitioner" 
                                                   class="form-checkbox" {{ in_array('sport_practitioner', old('member_categories', [])) ? 'checked' : '' }}>
                                            <span class="ml-2">{{ __('main.sport_practitioner') }}</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="member_categories[]" value="coach_referee" 
                                                   class="form-checkbox" {{ in_array('coach_referee', old('member_categories', [])) ? 'checked' : '' }}>
                                            <span class="ml-2">{{ __('main.coach_referee_judge') }}</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="member_categories[]" value="diving_professional" 
                                                   class="form-checkbox" {{ in_array('diving_professional', old('member_categories', [])) ? 'checked' : '' }}>
                                            <span class="ml-2">{{ __('main.diving_professional') }}</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="member_categories[]" value="recreational_diver" 
                                                   class="form-checkbox" {{ in_array('recreational_diver', old('member_categories', [])) ? 'checked' : '' }}>
                                            <span class="ml-2">{{ __('main.recreational_diver') }}</span>
                                        </label>
                                    </div>
                                    @if($errors->has('member_categories'))
                                        <div class="text-xs mt-1 text-rose-500">
                                            {{ $errors->first('member_categories') }}
                                        </div>
                                    @endif
                                </div>
                            </section>


                            <!-- Federations -->
                            @if (!empty($federation->entities))
                                <section>
                                    <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1"> {{ __('federation.entity_affiliation') }} </h3>
                                    <p class="text-gray-500 text-sm">{{ __('federation.entity_affiliation_info') }}</p>
                                    <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">

                                        <div class="sm:w-1/2">
                                            <label class="block text-sm font-medium mb-1"
                                                   for="entity_id">{{ __('federation.entity') }}
                                            </label>

                                            <select name="entity_id" id="entity_id"
                                                    class="form-select w-full {{ $errors->has('mainFederation_id') ? 'border-rose-300' : '' }}">
                                                <option value="" selected>{{ __('main.select_option') }}</option>
                                                @foreach ($federation->entities as $entity)
                                                    <option value="{{ $entity->id }}"
                                                            @if ($entity->id == old('entity_id')) selected @endif>{{ $entity->name }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            @if ($errors->has('entity_id'))
                                                <div class="text-xs mt-1 text-rose-500 h-2">
                                                    {{ $errors->first('entity_id') }}
                                                </div>
                                            @endif
                                        </div>

                                    </div>
                                </section>
                            @endif

                            <!-- Documents -->
                            <section>

                                <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">
                                    {{ __('main.identification_document') }} </h3>

                                <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                                    <div class="sm:w-1/3">
                                        <label class="block text-sm font-medium mb-1" for="doc_ref_type">
                                            {{ __('main.identification_document_type') }}
                                        </label>
                                        <select id="doc_ref_type"
                                                class="form-input w-full {{ $errors->has('doc_ref_type') ? 'border-rose-300' : '' }}"
                                                name="doc_ref_type">
                                            <option value="">{{ __('main.select_type') }}</option>
                                            <option value="identity_card"
                                                    @if (old('doc_ref_type', $individual->doc_ref_type ?? '') == 'identity_card') selected @endif>
                                                {{ __('main.identity_card') ?? 'Identity Card' }}</option>
                                            <option value="citizen_card"
                                                    @if (old('doc_ref_type', $individual->doc_ref_type ?? '') == 'citizen_card') selected @endif>
                                                {{ __('main.citizen_card') ?? 'Citizen Card' }}</option>
                                            <option value="foreign_identity_card"
                                                    @if (old('doc_ref_type', $individual->doc_ref_type ?? '') == 'foreign_identity_card') selected @endif>
                                                {{ __('main.foreign_identity_card') ?? 'Foreign Identity Card' }}</option>
                                            <option value="permanent_residence_card"
                                                    @if (old('doc_ref_type', $individual->doc_ref_type ?? '') == 'permanent_residence_card') selected @endif>
                                                {{ __('main.permanent_residence_card') ?? 'Permanent Residence Card' }}</option>
                                            <option value="passport"
                                                    @if (old('doc_ref_type', $individual->doc_ref_type ?? '') == 'passport') selected @endif>
                                                {{ __('main.passport') ?? 'Passport' }}</option>
                                            <!-- Keep old values for backward compatibility -->
                                            <option value="national_id_number"
                                                    @if (old('doc_ref_type', $individual->doc_ref_type ?? '') == 'national_id_number') selected @endif>
                                                {{ __('National Id Number') }}</option>
                                            <option value="passport_number"
                                                    @if (old('doc_ref_type', $individual->doc_ref_type ?? '') == 'passport_number') selected @endif>
                                                {{ __('Passport Number') }}</option>
                                        </select>
                                        @if ($errors->has('doc_ref_type'))
                                            <div class="text-xs mt-1 text-rose-500 h-2">
                                                {{ $errors->first('doc_ref_type') }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="sm:w-1/3">
                                        <label class="block text-sm font-medium mb-1" for="doc_ref">
                                            {{ __('main.identification_document_number') }}
                                        </label>
                                        <input id="doc_ref"
                                               class="form-input w-full {{ $errors->has('doc_ref', $individual->doc_ref ?? '') ? 'border-rose-300' : '' }}"
                                               type="text" name="doc_ref"
                                               value="{{ old('doc_ref', $individual->doc_ref) }}" />

                                        @if ($errors->has('doc_ref'))
                                            <div class="text-xs mt-1 text-rose-500 h-2">
                                                {{ $errors->first('doc_ref') }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="sm:w-1/3">
                                        <label class="block text-sm font-medium mb-1"
                                               for="doc_ref_validation_date"> {{ __('main.expire_date') }}
                                        </label>
                                        <input id="doc_ref_validation_date"
                                               class="form-input w-full {{ $errors->has('doc_ref_validation_date') ? 'border-rose-300' : '' }}"
                                               type="date" name="doc_ref_validation_date"
                                               value="{{ old('doc_ref_validation_date', $individual->doc_ref_validation_date) }}"
                                        />

                                        @if($errors->has('doc_ref_validation_date'))
                                            <div class="text-xs mt-1 text-rose-500 h-2">
                                                {{ $errors->first('doc_ref_validation_date') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </section>

                            {{-- Social Media Links --}}
                            <section>
                                <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">{{ __('main.social_media_links') }}</h3>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mt-5">
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="facebook_url">{{__('Facebook URL') }}</label>
                                        <input id="facebook_url"
                                            class="form-input w-full {{ $errors->has('facebook_url') ? 'border-rose-300' : '' }}"
                                            type="url" name="facebook_url" placeholder="https://facebook.com/yourprofile"
                                            value="{{ old('facebook_url', $individual->facebook_url) }}" />
                                        @if($errors->has('facebook_url'))
                                            <div class="text-xs mt-1 text-rose-500 h-2">{{ $errors->first('facebook_url') }}</div>
                                        @endif
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="x_url">{{__('X URL') }}</label>
                                        <input id="x_url"
                                            class="form-input w-full {{ $errors->has('x_url') ? 'border-rose-300' : '' }}"
                                            type="url" name="x_url" placeholder="https://x.com/yourprofile"
                                            value="{{ old('x_url', $individual->x_url) }}" />
                                        @if($errors->has('x_url'))
                                            <div class="text-xs mt-1 text-rose-500 h-2">{{ $errors->first('x_url') }}</div>
                                        @endif
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="instagram_url">{{__('Instagram URL') }}</label>
                                        <input id="instagram_url"
                                            class="form-input w-full {{ $errors->has('instagram_url') ? 'border-rose-300' : '' }}"
                                            type="url" name="instagram_url" placeholder="https://instagram.com/yourprofile"
                                            value="{{ old('instagram_url', $individual->instagram_url) }}" />
                                        @if($errors->has('instagram_url'))
                                            <div class="text-xs mt-1 text-rose-500 h-2">{{ $errors->first('instagram_url') }}</div>
                                        @endif
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="linkedin_url">{{__('LinkedIn URL') }}</label>
                                        <input id="linkedin_url"
                                            class="form-input w-full {{ $errors->has('linkedin_url') ? 'border-rose-300' : '' }}"
                                            type="url" name="linkedin_url" placeholder="https://linkedin.com/in/yourprofile"
                                            value="{{ old('linkedin_url', $individual->linkedin_url) }}" />
                                        @if($errors->has('linkedin_url'))
                                            <div class="text-xs mt-1 text-rose-500 h-2">{{ $errors->first('linkedin_url') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </section>

                            <section class="mt-4">
                                <h3 class="text-xl leading-snug text-slate-800 font-bold">
                                    {{ __('federation.user_login_information') }} </h3>
                                <p class="text-gray-500 text-sm">{{ __('federation.user_login_information_desc') }}</p>
                                <div class="mt-2 w-1/3">
                                    <label class="block text-sm font-medium mb-1"
                                           for="email"> {{ __('main.login_email') }}
                                        <span class="text-rose-500">*</span></label>
                                    <input id="email"
                                           class="form-input w-full {{ $errors->has('email') ? 'border-rose-300' : '' }}"
                                           type="text" name="email" value="{{ old('email') }}" />
                                    <p class="text-xs mt-1">{{ __('federation.email_credential_desc') }}</p>
                                    @if ($errors->has('email'))
                                        <div class="text-xs mt-1 text-rose-500 h-2">
                                            {{ $errors->first('email') }}
                                        </div>
                                    @endif
                                </div>
                            </section>


                            <!-- Panel footer -->
                            <x-forms.card-form-submit :backRoute="Request::segment(1) . '.individual.index'"
                                                      :buttonText="__('main.save_record')" />


                        </div>
                    </div>
                </div>
            </div>


        </form>


    </div>

</x-layout>
