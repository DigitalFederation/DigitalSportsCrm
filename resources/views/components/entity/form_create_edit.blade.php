@php
    // Determine if user can edit zones and Nº de Filiado
    // Only the main federation without a parent or platform admins can edit these fields.
    $user = auth()->user();
    $isAdmin = $user->hasRole('admin');
    $isPrimaryFederation = false;
    if ($user->group->code === 'FEDERATION') {
        $userFed = $user->federations()->first();
        $isPrimaryFederation = $userFed && $userFed->parent_id === null;
    }
    $canEditRestrictedFields = $isAdmin || $isPrimaryFederation;
@endphp

<div class="sm:flex sm:space-x-4">

    <div class="card flex flex-col md:flex-row md:-mr-px mb-8">
        <div class="grow">

            <!-- Panel body -->

            <!-- Assignment -->
            <section>
                <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">{{ __('entity.information') }}</h3>

                <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                    <x-forms.input-profile-avatar label="{{ __('entity.entity_logo') }}" :old="$entity" />
                </div>
                <p class="text-xs text-slate-500 mt-2">{{ __('individual.photo_max_size_hint') }}</p>

                {{-- Show Federation selector only for admin users --}}

                <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5 items-end">
                    <livewire:admin.entity-federation-selector :entity="$entity" />
                </div>


                <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                    <div class="sm:w-1/2">
                        <label class="block text-sm font-medium mb-1" for="name">{{ __('entity.club_school_center_name') }}
                            <span
                                class="text-rose-500">*</span></label>
                        <input id="name" class="form-input w-full {{ $errors->has('name') ? 'border-rose-300' : '' }}"
                               type="text" name="name" value="{{ old('name', $entity->name) }}" required />

                        @if($errors->has('name'))
                            <div class="text-xs mt-1 text-rose-500 h-2">
                                {{ $errors->first('name') }}
                            </div>
                        @endif
                    </div>
                    <div class="sm:w-1/2">
                        <label class="block text-sm font-medium mb-1"
                               for="legal-name"> {{ __('entity.legal_name') }}
                            <span class="text-rose-500">*</span></label>

                        <input id="legal-name"
                               class="form-input w-full {{ $errors->has('legal_name') ? 'border-rose-300' : '' }}"
                               type="text" name="legal_name" value="{{ old('legal_name', $entity->legal_name) }}"
                               required />

                        @if($errors->has('legal_name'))
                            <div class="text-xs mt-1 text-rose-500 h-2">
                                {{ $errors->first('legal_name') }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                    <div class="sm:w-1/3">
                        <label class="block text-sm font-medium mb-1"
                               for="legal_responsible_person"> {{ __('entity.responsible_person_name') }}</label>

                        <input id="legal_responsible_person"
                               class="form-input w-full {{ $errors->has('legal_responsible_person') ? 'border-rose-300' : '' }}"
                               type="text" name="legal_responsible_person"
                               value="{{ old('legal_responsible_person', $entity->legal_responsible_person) }}" />

                        @if($errors->has('legal_responsible_person'))
                            <div class="text-xs mt-1 text-rose-500 h-2">
                                {{ $errors->first('legal_responsible_person') }}
                            </div>
                        @endif
                    </div>


                    <div class="sm:w-1/3">
                        <label class="block text-sm font-medium mb-1"
                               for="vat_number"> {{ __('entity.nif') }}</label>

                        <input type="text" id="vat_number" name="vat_number"
                               class="form-input w-full {{ $errors->has('vat_number') ? 'border-rose-300' : '' }}"
                               value="{{ old('vat_number', $entity->vat_number) }}">

                        @if($errors->has('vat_number'))
                            <div class="text-xs mt-1 text-rose-500 h-2">
                                {{ $errors->first('vat_number') }}
                            </div>
                        @endif
                    </div>

                    <div class="sm:w-1/3">
                        <label class="block text-sm font-medium mb-1"
                               for="member_number"> {{ __('entity.affiliate_nr') }}</label>

                        @if($canEditRestrictedFields)
                            <input type="text" id="member_number" name="member_number"
                                   class="form-input w-full {{ $errors->has('member_number') ? 'border-rose-300' : '' }}"
                                   value="{{ old('member_number', $entity->member_number) }}">
                        @else
                            <input type="text" id="member_number"
                                   class="form-input w-full bg-gray-100 cursor-not-allowed"
                                   value="{{ $entity->member_number }}"
                                   disabled>
                            <p class="text-xs text-gray-500 mt-1">{{ __('entity.zone_edit_restricted') }}</p>
                        @endif

                        @if($errors->has('member_number'))
                            <div class="text-xs mt-1 text-rose-500 h-2">
                                {{ $errors->first('member_number') }}
                            </div>
                        @endif
                    </div>
                </div>

            </section>

            <!-- Location -->
            <section class="my-8">
                <h3 class="text-xl leading-snug text-slate-800 font-bold"> {{ __('entity.hq_location') }} </h3>

                <!-- Geographic Organization (District and Zone) -->
                @php
                    $isFederationFlow = auth()->user()->group->code === 'FEDERATION';
                    $federationZone = null;
                    if ($isFederationFlow) {
                        $userFederation = auth()->user()->federations()->with('zones')->first();
                        $federationZone = $userFederation?->zones?->first();
                    }
                @endphp

                <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-4">
                    <div class="sm:w-1/2">
                        <livewire:geographic.district-selector
                            :model="$entity"
                            :label="__('entity.district')"
                            :required="false"
                            :zone-id="$federationZone?->id" />
                    </div>
                    <div class="sm:w-1/2">
                        @if($isFederationFlow && !isset($edit))
                            {{-- Federation CREATE flow: hide zone selector, show info message --}}
                            <label class="block text-sm font-medium mb-2">{{ __('entity.zones') }}</label>
                            <div class="p-4 bg-blue-50 border border-blue-200 rounded-md">
                                <div class="flex items-start">
                                    <svg class="h-5 w-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="text-sm text-blue-700">
                                        <p class="font-medium">{{ __('entity.zone_auto_assigned') }}</p>
                                        @if($federationZone)
                                            <p class="mt-1">{{ __('entity.zone_will_be') }}: <strong>{{ $federationZone->name }}</strong></p>
                                            <input type="hidden" name="zone_ids[]" value="{{ $federationZone->id }}">
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @elseif(isset($edit) && !$canEditRestrictedFields)
                            {{-- Edit mode without permission: show read-only zones --}}
                            <label class="block text-sm font-medium mb-2">{{ __('entity.zones') }}</label>
                            <div class="p-3 bg-gray-100 border border-gray-200 rounded-md">
                                @if($entity->zones->count() > 0)
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($entity->zones as $zone)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                                                {{ $zone->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500">{{ __('entity.no_zones_assigned') }}</p>
                                @endif
                                <p class="text-xs text-gray-500 mt-2">{{ __('entity.zone_edit_restricted') }}</p>
                            </div>
                        @else
                            {{-- Admin flow or Edit with permission: show zone selector --}}
                            <livewire:geographic.zone-selector
                                :model="$entity"
                                :label="__('entity.zones')"
                                :allow-multiple="true" />
                        @endif
                    </div>
                </div>

                <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-4">
                    <div class="sm:w-2/4">
                        <label class="block text-sm font-medium mb-1" for="address">{{ __('entity.address') }}</label>
                        <input id="address"
                               class="form-input w-full {{ $errors->has('address') ? 'border-rose-300' : '' }}"
                               type="text" name="address" value="{{ old('address', $entity->address) }}" />

                        @if($errors->has('address'))
                            <div class="text-xs mt-1 text-rose-500 h-2">
                                {{ $errors->first('address') }}
                            </div>
                        @endif

                    </div>
                    <div class="sm:w-1/4">
                        <label class="block text-sm font-medium mb-1" for="location">{{ __('entity.location') }}</label>
                        <input id="location"
                               class="form-input w-full {{ $errors->has('location') ? 'border-rose-300' : '' }}"
                               type="text" name="location" value="{{ old('location', $entity->location) }}" />

                        @if($errors->has('location'))
                            <div class="text-xs mt-1 text-rose-500 h-2">
                                {{ $errors->first('location') }}
                            </div>
                        @endif
                    </div>
                    <div class="sm:w-1/4">
                        <label class="block text-sm font-medium mb-1" for="postal_code">{{ __('entity.zip_code') }}</label>
                        <input id="postal_code"
                               class="form-input w-full {{ $errors->has('postal_code') ? 'border-rose-300' : '' }}"
                               type="text" name="postal_code" value="{{ old('postal_code', $entity->postal_code) }}" />

                        @if($errors->has('postal_code'))
                            <div class="text-xs mt-1 text-rose-500 h-2">
                                {{ $errors->first('postal_code') }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                    <div class="sm:w-2/4">
                        <label class="block text-sm font-medium mb-1" for="country">{{ __('entity.country') }} <span
                                class="text-rose-500">*</span></label>
                        <select name="country_id" id="country"
                                class="form-select w-full {{ $errors->has('country_id') ? 'border-rose-300' : '' }}"
                                required>
                            <option value="" selected disabled> {{ __('entity.select_option') }} </option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}"
                                        @if(old('country_id', $entity->country_id)==$country->id) selected @endif>
                                    {{ $country->name }}</option>
                            @endforeach
                        </select>

                        @if($errors->has('country_id'))
                            <div class="text-xs mt-1 text-rose-500 h-2">
                                {{ $errors->first('country_id') }}
                            </div>
                        @endif
                    </div>

                    <div class="sm:w-2/4">

                        <livewire:widgets.location-picker
                            :initial-lat="old('lat', $entity->lat)"
                            :initial-lng="old('lng', $entity->lng)"
                            lat-field="lat"
                            lng-field="lng" />

                    </div>
                </div>

                <!-- Contact Information -->
                <h3 class="text-xl leading-snug text-slate-800 font-bold mt-8 mb-4"> {{ __('entity.public_contacts') }} </h3>
                <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4">

                    <div class="sm:w-1/3">
                        <label class="block text-sm font-medium mb-1" for="email">{{ __('entity.contact_email') }}</label>
                        <input id="email" class="form-input w-full {{ $errors->has('email') ? 'border-rose-300' : '' }}"
                               type="email" name="email" value="{{ old('email', $entity->email) }}" />

                        @if($errors->has('email'))
                            <div class="text-xs mt-1 text-rose-500 h-2">
                                {{ $errors->first('email') }}
                            </div>
                        @endif

                    </div>

                    <div class="sm:w-1/3">
                        <label class="block text-sm font-medium mb-1" for="website">{{ __('entity.website') }}</label>
                        <input id="website"
                               class="form-input w-full {{ $errors->has('website') ? 'border-rose-300' : '' }}"
                               type="text" name="website" value="{{ old('website', $entity->website) }}" />

                        @if($errors->has('website'))
                            <div class="text-xs mt-1 text-rose-500 h-2">
                                {{ $errors->first('website') }}
                            </div>
                        @endif
                    </div>

                    <div class="sm:w-1/3">
                        <label class="block text-sm font-medium mb-1" for="phone_number">{{ __('entity.phone_number') }}</label>
                        <input id="phone_number"
                               class="form-input w-full {{ $errors->has('phone_number') ? 'border-rose-300' : '' }}"
                               type="text" name="phone" value="{{ old('phone', $entity->phone) }}" />

                        @if($errors->has('phone'))
                            <div class="text-xs mt-1 text-rose-500 h-2">
                                {{ $errors->first('phone') }}
                            </div>
                        @endif
                    </div>

                </div>

                {{-- Social Media --}}
                <h3 class="form-section-title">{{ __('entity.social_media_links') }}</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium mb-1" for="facebook_url">{{ __('entity.facebook_url') }}</label>
                        <input id="facebook_url"
                            class="form-input w-full {{ $errors->has('facebook_url') ? 'border-rose-300' : '' }}"
                            type="url" name="facebook_url" placeholder="https://facebook.com/yourpage"
                            value="{{ old('facebook_url', $entity->facebook_url) }}" />
                        @if($errors->has('facebook_url'))
                            <div class="text-xs mt-1 text-rose-500 h-2">{{ $errors->first('facebook_url') }}</div>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="x_url">{{ __('entity.x_url') }}</label>
                        <input id="x_url"
                            class="form-input w-full {{ $errors->has('x_url') ? 'border-rose-300' : '' }}"
                            type="url" name="x_url" placeholder="https://x.com/yourprofile"
                            value="{{ old('x_url', $entity->x_url) }}" />
                        @if($errors->has('x_url'))
                            <div class="text-xs mt-1 text-rose-500 h-2">{{ $errors->first('x_url') }}</div>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="instagram_url">{{ __('entity.instagram_url') }}</label>
                        <input id="instagram_url"
                            class="form-input w-full {{ $errors->has('instagram_url') ? 'border-rose-300' : '' }}"
                            type="url" name="instagram_url" placeholder="https://instagram.com/yourprofile"
                            value="{{ old('instagram_url', $entity->instagram_url) }}" />
                        @if($errors->has('instagram_url'))
                            <div class="text-xs mt-1 text-rose-500 h-2">{{ $errors->first('instagram_url') }}</div>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="linkedin_url">{{ __('entity.linkedin_url') }}</label>
                        <input id="linkedin_url"
                            class="form-input w-full {{ $errors->has('linkedin_url') ? 'border-rose-300' : '' }}"
                            type="url" name="linkedin_url" placeholder="https://linkedin.com/company/yourcompany"
                            value="{{ old('linkedin_url', $entity->linkedin_url) }}" />
                        @if($errors->has('linkedin_url'))
                            <div class="text-xs mt-1 text-rose-500 h-2">{{ $errors->first('linkedin_url') }}</div>
                        @endif
                    </div>
                </div>

                {{-- Admin portal Access (for the Admin namespace) --}}
                @if(in_array(Request::segment(1), ['cmas', 'admin']))
                <div class="mt-8">
                    <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">{{ __('entity.cmas_portal_access') }}</h3>
                    <div class="mt-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox"
                                   name="has_international_portal"
                                   id="has_international_portal"
                                   value="1"
                                   class="form-checkbox"
                                   {{ old('has_international_portal', $entity->has_international_portal ?? false) ? 'checked' : '' }}>
                            <span class="ml-2">{{ __('entity.has_international_portal_account') }}</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1">{{ __('entity.cmas_portal_description') }}</p>
                    </div>
                </div>
                @endif

            </section>


            <!-- Add this section before the form submit buttons -->
            @if(!isset($edit))
                <section class="border-t border-slate-200 mt-6 pt-6">
                    <h3 class="text-xl leading-snug text-slate-800 font-bold mb-4">{{ __('entity.terms_policies') }}</h3>

                    <!-- Terms of Service -->
                    <div class="flex flex-col">
                        <label class="flex items-start text-sm font-medium mb-1">
                            <input id="terms"
                                   class="mr-2 mt-1"
                                   type="checkbox"
                                   name="terms"
                                   value="1"
                                   required
                                {{ old('terms') ? 'checked' : '' }}>
                            <span>
                {{ __('entity.terms_confirm') }}
                <a href="{{ route('terms-of-service') }}"
                   target="_blank"
                   class="text-blue-600 hover:underline">
                    {{ __('entity.terms_of_service') }}
                </a>
                {{ __('entity.and') }}
                <a href="{{ route('privacy-policy') }}"
                   target="_blank"
                   class="text-blue-600 hover:underline">
                    {{ __('entity.privacy_policy') }}
                </a>
            </span>
                            <span class="text-rose-500">*</span>
                        </label>
                        <x-input-error for="terms" />
                    </div>

                    <!-- Data Sharing -->
                    <div class="flex flex-col">
                        <label class="flex items-start text-sm font-medium mb-1">
                            <input id="dataSharing"
                                   class="mr-2 mt-1"
                                   type="checkbox"
                                   name="data_sharing"
                                   value="1"
                                   required
                                {{ old('data_sharing') ? 'checked' : '' }}>
                            <span>
                {{ __('entity.data_sharing_confirm') }}
                <a href="{{ route('data-sharing-policy') }}"
                   target="_blank"
                   class="text-blue-600 hover:underline">
                    {{ __('entity.data_sharing_policy') }}
                </a>
            </span>
                            <span class="text-rose-500">*</span>
                        </label>
                        <x-input-error for="data_sharing" />
                    </div>
                </section>
            @endif
            <!-- Panel footer -->
            <x-forms.card-form-submit :backRoute="Request::segment(1).'.entity.index'"
                                      :buttonText="__('entity.save_record')"></x-forms.card-form-submit>


        </div>
    </div>
    @if(!isset($edit))
        <div class="mb-8 sm:w-1/3 flex gap-y-4 flex-col">

            @if(!isset($edit))
                <div class="card">
                    <div class="flex information-box items-center w-full py-1 text-sm mt-1">
                        <x-svg.info class="h-6 w-6 text-slate-700 mr-2" />
                        <p class="text-xs">
                            {{ __('entity.entity_creation_info') }}
                        </p>
                    </div>
                </div>
            @endif

            @if(!isset($edit))
                <div class="card">
                    <section>
                        <h3 class="text-xl leading-snug text-slate-800 font-bold"> {{ __('entity.user_login_information') }} </h3>
                        <p class="text-gray-500 text-sm">{{ __('entity.user_login_info_description') }}</p>
                        <div class="mt-2 w-full">
                            <label class="block text-sm font-medium mb-1" for="user_email"> {{ __('entity.user_login_email') }}
                                <span class="text-rose-500">*</span></label>
                            <input id="user_email"
                                   class="form-input w-full {{ $errors->has('user_email') ? 'border-rose-300' : '' }}"
                                   type="text" name="user_email" value="{{ old('user_email') }}" />
                            <p class="text-xs mt-1">{{ __('entity.email_credential_hint') }}</p>
                            @if($errors->has('user_email'))
                                <div class="text-xs mt-1 text-rose-500 h-2">
                                    {{ $errors->first('user_email') }}
                                </div>
                            @endif
                        </div>
                        <div class="mt-2 w-full">
                            <label class="block text-sm font-medium mb-1"
                                   for="confirm_email"> {{ __('entity.confirm_user_login_email') }} <span
                                    class="text-rose-500">*</span></label>
                            <input id="confirm_user_email"
                                   class="form-input w-full {{ $errors->has('confirm_user_email') ? 'border-rose-300' : '' }}"
                                   type="text" name="confirm_user_email" value="{{ old('confirm_user_email') }}" />
                            <p class="text-xs mt-1">{{ __('entity.confirm_email_address') }}</p>
                            @if ($errors->has('confirm_user_email'))
                                <div class="text-xs mt-1 text-rose-500 h-2">
                                    {{ $errors->first('confirm_user_email') }}
                                </div>
                            @endif
                        </div>
                    </section>
                </div>
            @endif

        </div>
    @endif

</div>
