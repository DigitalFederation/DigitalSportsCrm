@props(['federation', 'federations', 'countries', 'zones' => null])

<section>

    <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
        <x-forms.input-profile-avatar label="{{ __('Federation Logo') }}" :old="$federation"/>
    </div>

    <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">

        <div class="sm:w-1/2">
            <label class="block text-sm font-medium mb-1" for="name"> {{ __('Federation Name' )}} <span class="text-rose-500">*</span></label>
            <input id="name"
                    class="form-input w-full {{ $errors->has('name') ? 'border-rose-300' : '' }}"
                    type="text" name="name"
                    value="{{ old('name', $federation->name ?? null) }}" required/>
            <div class="text-xs mt-1 text-gray-500">E.g., French Federation</div> <!-- Help Text -->

            @if($errors->has('name'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('name') }}
                </div>
            @endif
        </div>


        <div class="sm:w-1/2">
            <label class="block text-sm font-medium mb-1" for="legal-name"> {{ __('Federation Native/Legal Name') }} <span class="text-rose-500">*</span></label>

            <input id="legal-name"
                class="form-input w-full {{ $errors->has('legal_name') ? 'border-rose-300' : '' }}"
                type="text"
                name="legal_name"
                value="{{ old('legal_name', $federation->legal_name ?? null) }}"
                required/>
            <div class="text-xs mt-1 text-gray-500">E.g., Fédération Française</div> <!-- Help Text -->

            @if($errors->has('legal_name'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('legal_name') }}
                </div>
            @endif
        </div>



    </div>

    <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
        <div class="sm:w-1/3">
            <label class="block text-sm font-medium mb-1" for="member_code"> {{ __('main.Member Code' )}} <span class="text-rose-500">*</span></label>
            <input id="member_code"
                    class="form-input w-full {{ $errors->has('member_code') ? 'border-rose-300' : '' }}"
                    type="text" name="member_code"
                    value="{{ old('member_code', $federation->member_code ?? null) }}" required/>


            @if($errors->has('member_code'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('member_code') }}
                </div>
            @endif
        </div>

        <div class="sm:w-1/3">
            <label class="block text-sm font-medium mb-1" for="vat_number"> {{ __('Tax identification Number' )}} </label>
            <input id="vat_number"
                    class="form-input w-full {{ $errors->has('vat_number') ? 'border-rose-300' : '' }}"
                    type="text" name="vat_number"
                    value="{{ old('vat_number',$federation->vat_number ?? null) }}"/>
           <div class="text-xs mt-1 text-gray-500">E.g., FR123456789</div> <!-- Help Text -->


            @if($errors->has('vat_number'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('vat_number') }}
                </div>
            @endif
        </div>


    </div>

    <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
        <div class="sm:w-5/5">
            <label class="block text-sm font-medium mb-1" for="parent"> {{ __('Main National Federation') }} </label>

            <select name="parent_id" id="parent" class="form-select w-full {{ $errors->has('parent_id') ? 'border-rose-300' : '' }}">
                <option selected></option>
                @foreach($federations as $fed)
                    <option value="{{ $fed->id }}" @if(old('parent_id',$federation->parent_id ?? null)==$fed->id) selected @endif>
                        {{ $fed->name ?? null }}</option>
                @endforeach
            </select>
            <div class="text-xs mt-1 text-gray-500">*Only if applicable. Select if your federation is under a national federation.</div> <!-- Help Text -->


            @if($errors->has('parent_id'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('parent_id') }}
                </div>
            @endif

        </div>
    </div>

    <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
        <div class="sm:w-5/5">

            <label class="flex items-center">
                <input type="checkbox" name="is_local" class="form-checkbox {{ $errors->has('is_local') ? 'border-rose-300' : '' }}" value="1" @if(old('is_local', $federation->is_local ?? null)) checked @endif />
                <span class="text-sm ml-2"> {{ __('This is a federation or organization under a main federation') }}</span>
            </label>

            @if($errors->has('is_local'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('is_local') }}
                </div>
            @endif

            <div class="flex information-box items-center w-full py-1 text-xs mt-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-4" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="#9e9e9e" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <circle cx="12" cy="12" r="9"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                    <polyline points="11 12 12 12 12 16 13 16"/>
                </svg>
                <p class="text-xs">
                    Select in case the federation or organisation is regional, territorial, state or a federation with representation of diving, science or one or more underwater sports dependent on a main federation with representation from that country in the {{ config('branding.international.name', 'International Federation') }}.
                </p>
            </div>
        </div>
    </div>

    <!-- Category and Manual Fields -->
    <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
        <div class="sm:w-1/2">
            <label class="block text-sm font-medium mb-1" for="category">{{ __('federation.category') }}</label>
            <select id="category" name="category" class="form-select w-full {{ $errors->has('category') ? 'border-rose-300' : '' }}">
                <option value="">{{ __('federation.select_category') }}</option>
                <option value="{{ \Domain\Federations\Enums\SportOrClassAssociationCategory::class }}"
                    @if(old('category', $federation->category ?? null) === \Domain\Federations\Enums\SportOrClassAssociationCategory::class) selected @endif>
                    {{ __('federation.sport_or_class_association') }}
                </option>
                <option value="{{ \Domain\Federations\Enums\TerritorialAssociationCategory::class }}"
                    @if(old('category', $federation->category ?? null) === \Domain\Federations\Enums\TerritorialAssociationCategory::class) selected @endif>
                    {{ __('federation.territorial_association') }}
                </option>
            </select>
            <div class="text-xs mt-1 text-gray-500">{{ __('federation.category_help') }}</div>
            @if($errors->has('category'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('category') }}
                </div>
            @endif
        </div>

        <div class="sm:w-1/2">
            <label class="flex items-center mt-6">
                <input type="checkbox" name="is_manual" class="form-checkbox {{ $errors->has('is_manual') ? 'border-rose-300' : '' }}" value="1"
                    @if(old('is_manual', $federation->is_manual ?? null)) checked @endif />
                <span class="text-sm ml-2">{{ __('federation.manual_approval_required') }}</span>
            </label>
            <div class="text-xs mt-1 text-gray-500">{{ __('federation.manual_approval_help') }}</div>
            @if($errors->has('is_manual'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('is_manual') }}
                </div>
            @endif
        </div>
    </div>

</section>

<section class="mb-4">

    <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 mt-5">

        <div class="sm:w-full">
            <label class="block text-sm font-medium mb-1" for="address">{{__('Headquarters Address') }}</label>
            <textarea id="address"
                        class="form-textarea w-full {{ $errors->has('address') ? 'border-rose-300' : '' }}"
                        name="address">{{ old('address', $federation->address ?? null) }}</textarea>
            <div class="text-xs mt-1 text-gray-500">E.g., 123 Main St, Suite 101</div> <!-- Help Text -->

            @if($errors->has('address'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('address') }}
                </div>
            @endif
        </div>
    </div>

    <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
        <div class="sm:w-2/5">
            <label class="block text-sm font-medium mb-1" for="location">{{__('City') }}</label>
            <input id="location"
                    class="form-input w-full {{ $errors->has('location') ? 'border-rose-300' : '' }}"
                    type="text" name="location"
                    value="{{ old('location', $federation->location ?? null) }}"/>

            @if($errors->has('location'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('location') }}
                </div>
            @endif
        </div>

        <div class="sm:w-1/5">
            <label class="block text-sm font-medium mb-1" for="zip_code">{{__('Postal Code') }}</label>
            <input id="zip_code"
                    class="form-input w-full {{ $errors->has('zip_code') ? 'border-rose-300' : '' }}"
                    type="text" name="zip_code"
                    value="{{ old('zip_code', $federation->zip_code ?? null) }}"/>
            <div class="text-xs mt-1 text-gray-500">E.g., 75001</div> <!-- Help Text -->

            @if($errors->has('zip_code'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('zip_code') }}
                </div>
            @endif
        </div>
    </div>

    <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
        <div class="sm:w-3/5">
            <label class="block text-sm font-medium mb-1" for="country">{{__('Country') }} <span class="text-rose-500">*</span></label>
            <select name="country_id" id="country" class="form-select w-full {{ $errors->has('country_id') ? 'border-rose-300' : '' }}" required>
                <option value="" selected disabled> {{ __('-- Select an option --') }} </option>
                @foreach($countries as $country)
                    <option value="{{ $country->id }}"
                            @if(old('country_id', $federation->country_id ?? null) == $country->id) selected @endif>
                        {{ $country->name }}</option>
                @endforeach
            </select>

            @if($errors->has('country_id'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('country_id') }}
                </div>
            @endif
        </div>

        <div class="sm:w-1/5">
            <label class="block text-sm font-medium mb-1" for="lat">{{__('Latitude') }}</label>
            <input id="lat" class="form-input w-full {{ $errors->has('latitude') ? 'border-rose-300' : '' }}"
                    type="text" name="latitude"
                    value="{{ old('latitude', $federation->lat ?? null) }}"/>
            <div class="text-xs mt-1 text-gray-500">E.g., 48.8566</div> <!-- Help Text -->

            @if($errors->has('latitude'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('latitude') }}
                </div>
            @endif
        </div>

        <div class="sm:w-1/5">
            <label class="block text-sm font-medium mb-1" for="lng">{{__('Longitude') }}</label>
            <input id="lng"
                    class="form-input w-full {{ $errors->has('longitude') ? 'border-rose-300' : '' }}"
                    type="text" name="longitude"
                    value="{{ old('longitude', $federation->lng ?? null) }}"/>
            <div class="text-xs mt-1 text-gray-500">E.g., 2.3522</div> <!-- Help Text -->

            @if($errors->has('longitude'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('longitude') }}
                </div>
            @endif
        </div>
    </div>

    @if(isset($zones) && $zones !== null && $zones->count() > 0)
    <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
        <div class="sm:w-full">
            <label class="block text-sm font-medium mb-1" for="zones">{{__('main.Zones') }}</label>
            <select name="zone_ids[]" id="zones" class="form-select w-full {{ $errors->has('zone_ids') ? 'border-rose-300' : '' }}" multiple>
                @foreach($zones as $zone)
                    <option value="{{ $zone->id }}"
                            @if(in_array($zone->id, old('zone_ids', isset($federation->zones) ? $federation->zones->pluck('id')->toArray() : []))) selected @endif>
                        {{ $zone->name }}
                    </option>
                @endforeach
            </select>
            <div class="text-xs mt-1 text-gray-500">{{ __('main.zones_help_text') }}</div>

            @if($errors->has('zone_ids'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('zone_ids') }}
                </div>
            @endif
        </div>
    </div>
    @endif

    <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">

        <div class="sm:w-1/3">
            <label class="block text-sm font-medium mb-1" for="email">{{__('Contact Email') }}</label>
            <input id="email" class="form-input w-full {{ $errors->has('email') ? 'border-rose-300' : '' }}" type="email" name="email" value="{{ old('email', $federation->email ?? null) }}"/>

            @if($errors->has('email'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('email') }}
                </div>
            @endif

        </div>

        <div class="sm:w-1/3">
            <label class="block text-sm font-medium mb-1" for="website">{{__('Website') }}</label>
            <input id="website" class="form-input w-full {{ $errors->has('website') ? 'border-rose-300' : '' }}" type="text" name="website" value="{{ old('website', $federation->website ?? null) }}"/>

            @if($errors->has('website'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('website') }}
                </div>
            @endif

        </div>

        <div class="sm:w-1/3">
            <label class="block text-sm font-medium mb-1" for="phone_number">{{__('Phone Number') }}</label>
            <input id="phone_number"
                    class="form-input w-full {{ $errors->has('phone_number') ? 'border-rose-300' : '' }}"
                    type="text" name="phone" value="{{ old('phone', $federation->phone ?? null) }}"/>

            @if($errors->has('phone'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('phone') }}
                </div>
            @endif
        </div>

    </div>

    <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5 border-t border-slate-200 pt-2">
        <div class="sm:w-1/2">
            <label class="block text-sm font-medium mb-1" for="is_default_federation"> {{ __('Is this the default Federation?' )}} <span class="text-rose-500">*</span></label>
            <select class="form-select" name="is_default_federation">
                <option value="0" default @if(old('is_default_federation',$federation->is_default_federation ?? null)==0) selected @endif> {{ __('No') }} </option>
                <option value="1" @if(old('is_default_federation',$federation->is_default_federation ?? null)==1) selected @endif> {{ __('Yes') }} </option>
            </select>
            <div class="text-xs mt-1 text-gray-500"> {{ __('*Only used to define the virtual international federation. The default in the system') }} </div>
            @if($errors->has('is_default_federation'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('is_default_federation') }}
                </div>
            @endif
        </div>

        <div class="sm:w-1/2">
            <label class="flex items-center mt-6">
                <input type="checkbox" name="can_issue_certifications" class="form-checkbox {{ $errors->has('can_issue_certifications') ? 'border-rose-300' : '' }}" value="1"
                    @if(old('can_issue_certifications', $federation->can_issue_certifications ?? true)) checked @endif />
                <span class="text-sm ml-2">{{ __('federation.can_issue_certifications') }}</span>
            </label>
            <div class="text-xs mt-1 text-gray-500">{{ __('federation.can_issue_certifications_help') }}</div>
            @if($errors->has('can_issue_certifications'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('can_issue_certifications') }}
                </div>
            @endif
        </div>
    </div>

</section>


<!-- Panel footer -->
<x-forms.card-form-submit backRoute="federation.dashboard" :buttonText="__('Save record')"></x-forms.card-form-submit>
