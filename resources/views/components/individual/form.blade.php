<div class="sm:flex sm:space-x-4">
    <div class="mb-8 sm:w-full">
        <div class="card">
            <div class="grow flex flex-col gap-y-4">

                <!-- Assignment -->
                <section>
                    <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">{{ __('Individual Information') }}
                    </h3>

                    <div class="sm:flex items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">


                        <x-forms.input-profile-avatar label="{{ __('Photo / Avatar') }}" :old="$individual" />

                        @if(auth()->user()->group->code == 'FEDERATION')
                            <div class="sm:w-1/3">
                                <label class="block text-sm font-medium mb-1"
                                       for="member_number">{{ __('individuals.member_number') }}</label>
                                <input id="member_number"
                                       class="form-input w-full {{ $errors->has('member_number') ? 'border-rose-300' : '' }}"
                                       type="text" name="member_number"
                                       value="{{ old('member_number', $individual->member_number ?? '') }}" />

                                <div class="text-xs mt-1"> {{ __('individuals.federation_edit_only') }} </div>
                                @if($errors->has('member_number'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('member_number') }}
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if(auth()->user()->group->code == 'ADMIN')
                            <div class="sm:w-1/3">
                                <label class="block text-sm font-medium mb-1"
                                       for="member_number">{{ __('Member Number') }}</label>
                                <input id="member_number"
                                       class="form-input w-full {{ $errors->has('member_number') ? 'border-rose-300' : '' }}"
                                       type="text" name="member_number"
                                       value="{{ old('member_number', $individual->member_number ?? '') }}" />

                                <div class="text-xs mt-1"> {{ __('*Only the International Federation can edit this information') }} </div>
                                @if($errors->has('member_number'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('member_number') }}
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-2">{{ __('individual.photo_max_size_hint') }}</p>

                    {{-- Show Federation selector only for admin users --}}
                    @if(auth()->user()->group->code == 'ADMIN' && $individual->id)
                        <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5 items-end">
                            <livewire:admin.individual-federation-selector :individual="$individual" />
                        </div>
                    @endif

                    <!-- if(auth()->user()->group->code == 'ADMIN') -->
                    <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                        <div class="sm:w-1/3">
                            <label class="block text-sm font-medium mb-1" for="name">{{ __('Name') }} <span
                                    class="text-rose-500">*</span></label>
                            <input id="name"
                                   class="form-input w-full {{ $errors->has('name') ? 'border-rose-300' : '' }}"
                                   type="text" name="name" value="{{ old('name', $individual->name ?? '') }}"
                                   required />

                            @if ($errors->has('name'))
                                <div class="text-xs mt-1 text-rose-500 h-2">
                                    {{ $errors->first('name') }}
                                </div>
                            @endif
                        </div>
                        <div class="sm:w-1/3">
                            <label class="block text-sm font-medium mb-1" for="surname">
                                {{ __('Family Name') }} <span
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
                        <div class="sm:w-full">
                            <label class="block text-sm font-medium mb-1" for="native_name">
                                {{ __('individual.full_name') }} <span class="text-rose-500">*</span></label>
                            <input id="native_name"
                                   class="form-input w-full {{ $errors->has('native_name') ? 'border-rose-300' : '' }}"
                                   type="text" name="native_name"
                                   value="{{ old('native_name', $individual->native_name ?? '') }}"
                                   required />

                            @if ($errors->has('native_name'))
                                <div class="text-xs mt-1 text-rose-500 h-2">
                                    {{ $errors->first('native_name') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">

                        <div class="sm:w-1/3">
                            <label class="block text-sm font-medium mb-1" for="country_id"> {{ __('Nationality') }}
                                <span class="text-rose-500">*</span></label>
                            <select name="country_id" id="country_id"
                                    class="form-input w-full {{ $errors->has('country_id') ? 'border-rose-300' : '' }}"
                                    required>
                                <option value="0" selected disabled>{{ __('common.select_option') }}</option>
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
                            @php
                                $isFederationEdit = auth()->user()->group->code == 'FEDERATION' && isset($individual) && $individual->id;
                            @endphp
                            <label class="block text-sm font-medium mb-1" for="birthdate">
                                {{ __('Birthdate') }} @if(!$isFederationEdit)<span class="text-rose-500">*</span>@endif</label>
                            <input id="birthdate"
                                   class="form-input w-full {{ $errors->has('birthdate') ? 'border-rose-300' : '' }}"
                                   type="date" name="birthdate"
                                   value="{{ old('birthdate', isset($individual) && $individual->birthdate ? $individual->birthdate->format('Y-m-d') : '') }}"
                                   max="{{ date('Y-m-d') }}" {{ $isFederationEdit ? '' : 'required' }} />

                            @if ($errors->has('birthdate'))
                                <div class="text-xs mt-1 text-rose-500 h-2">
                                    {{ $errors->first('birthdate') }}
                                </div>
                            @endif
                        </div>

                        <div class="sm:w-1/3">
                            <label class="block text-sm font-medium mb-1" for="gender">
                                {{ __('Gender') }}
                                <span class="text-rose-500">*</span></label>
                            <select name="gender" id="gender"
                                    class="form-select w-full {{ $errors->has('gender') ? 'border-rose-300' : '' }}"
                                    required>
                                <option value="" selected disabled>{{ __('common.select_option') }}</option>
                                <option value="male"
                                        @if (old('gender', $individual->gender) == 'male') selected @endif>{{ __('main.male') }}
                                </option>
                                <option value="female"
                                        @if (old('gender', $individual->gender) == 'female') selected @endif>{{ __('main.female') }}
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

                    <!-- hidden default fields -->


                    <!-- end hidden default fields -->


                    <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">


                        <div class="sm:w-3/6">
                            <label class="block text-sm font-medium mb-1"
                                   for="address">{{ __('Address') }}</label>
                            <input id="address"
                                   class="form-input w-full {{ $errors->has('address') ? 'border-rose-300' : '' }}"
                                   type="text" name="address" value="{{ old('address', $individual->address ?? '') }}"
                            />

                            @if($errors->has('address'))
                                <div class="text-xs mt-1 text-rose-500 h-2">
                                    {{ $errors->first('address') }}
                                </div>
                            @endif
                        </div>

                        <div class="sm:w-2/6">
                            <label class="block text-sm font-medium mb-1"
                                   for="location">{{ __('Location') }}</label>
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
                                   for="postal_code">{{ __('Postal Code') }}</label>
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

                    <!-- Geographic Organization -->
                    <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                        <div class="sm:w-1/2">
                            <livewire:geographic.district-selector 
                                :model="$individual" 
                                :label="__('District')" 
                                :required="false" />
                        </div>
                        <div class="sm:w-1/2">
                            <livewire:geographic.zone-selector 
                                :model="$individual" 
                                :label="__('Zone')" 
                                :allow-multiple="false" />
                        </div>
                    </div>

                </section>

                @if(empty($individual->id))
                    <!-- Federations -->
                    <section>

                        <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1"> {{ __('Affiliation') }} </h3>
                        <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">

                            @if (!empty($federations) && $federations->count() > 0)
                                <livewire:input.select-federation-and-local
                                    :errors="$errors"
                                    :federations="$federations"
                                    :selectedMainFederation="old('main_federation_id', $mainFederation)"
                                    :selectedLocalFederation="old('local_federation_id', $localFederation)" />

                            @else
                                <input type="hidden" name="federation_id"
                                       value="{{ auth()->user()->federations()->value('federation.id') }}">
                            @endif

                            @if(!empty($mainFederation))
                                <div class="sm:w-1/3">
                                    <label
                                        class="block text-sm font-medium mb-1"
                                        for="member_number"> {{ __('individuals.member_number') }}</label>
                                    <input
                                        type="text"
                                        name="member_number"
                                        id="member_number"
                                        value="{{ $individual->member_number ?? '' }}"
                                        class="form-input w-full">
                                </div>
                            @endif

                            @if (!empty($entities))
                                <div class="sm:w-1/2">
                                    <label class="block text-sm font-medium mb-1"
                                           for="entity_id">{{ __('Entity') }}</label>

                                    <select name="entity_id" id="entity_id"
                                            class="form-select w-full {{ $errors->has('mainFederation_id') ? 'border-rose-300' : '' }}">
                                        <option value="" selected>{{ __('common.select_option') }}</option>
                                        @foreach ($entities as $entity)
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
                            @endif
                        </div>
                    </section>
                @endif
                <!-- Documents -->
                <section>

                    <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">
                        {{ __('Identification Document') }} </h3>

                    <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                        <div class="sm:w-1/3">
                            <label class="block text-sm font-medium mb-1" for="doc_ref_type">
                                {{ __('Identification Document Type') }}
                            </label>
                            <select id="doc_ref_type"
                                    class="form-input w-full {{ $errors->has('doc_ref_type') ? 'border-rose-300' : '' }}"
                                    name="doc_ref_type">
                                <option value="">{{ __('Select a type') }}</option>
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
                                {{ __('Identification Document Number') }}
                            </label>
                            <input id="doc_ref"
                                   class="form-input w-full {{ $errors->has('doc_ref', $individual->doc_ref ?? '') ? 'border-rose-300' : '' }}"
                                   type="text"
                                   name="doc_ref"
                                   value="{{ old('doc_ref', $individual->doc_ref) }}" />

                            @if ($errors->has('doc_ref'))
                                <div class="text-xs mt-1 text-rose-500 h-2">
                                    {{ $errors->first('doc_ref') }}
                                </div>
                            @endif
                        </div>

                        <div class="sm:w-1/3">
                            <label class="block text-sm font-medium mb-1"
                                   for="doc_ref_validation_date"> {{ __('Expire date') }}
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

                @if(empty($individual->user_id))
                    <section>
                        <h3 class="text-xl leading-snug text-slate-800 font-bold">
                            {{ __('individual.user_login_information') }} </h3>
                        <p class="text-gray-500 text-sm">{{ __('individual.user_login_description') }}</p>
                        <div class="mt-2 w-1/3">
                            <label class="block text-sm font-medium mb-1" for="email"> {{ __('individual.login_email') }}
                                <span class="text-rose-500">*</span></label>
                            <input id="email"
                                   class="form-input w-full {{ $errors->has('email') ? 'border-rose-300' : '' }}"
                                   type="text" name="email" value="{{ old('email') }}" />
                            <p class="text-xs mt-1">{{ __('individual.email_credential_help') }}</p>
                            @if ($errors->has('email'))
                                <div class="text-xs mt-1 text-rose-500 h-2">
                                    {{ $errors->first('email') }}
                                </div>
                            @endif
                        </div>
                    </section>
                @endif

                {{-- Social Media Links --}}
                <h3 class="form-section-title">{{ __('Social Media Links') }}</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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

                {{-- Admin portal Access (for the Admin namespace) --}}
                @if(in_array(Request::segment(1), ['cmas', 'admin']))
                <section>
                    <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">{{ __('individuals.cmas_portal_access') }}</h3>
                    <div class="mt-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox"
                                   name="has_international_portal"
                                   id="has_international_portal"
                                   value="1"
                                   class="form-checkbox"
                                   {{ old('has_international_portal', $individual->has_international_portal ?? false) ? 'checked' : '' }}>
                            <span class="ml-2">{{ __('individuals.has_international_portal_account') }}</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1">{{ __('individuals.cmas_portal_description') }}</p>
                    </div>
                </section>
                @endif

                <!-- Panel footer -->
                <x-forms.card-form-submit :backRoute="Request::segment(1) . '.individual.index'"
                                          :buttonText="__('Save record')" />


            </div>
        </div>
    </div>
</div>
