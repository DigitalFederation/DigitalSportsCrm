@section('title', __('federation.edit_profile'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title"> {{ __('federation.edit_profile') }} </h1>
        </div>


        <form action="{{ route('federation.profile.update', $federation->id) }}" method="POST"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="sm:flex sm:space-x-4">

                <div class="card sm:w-2/3 flex flex-col md:flex-row md:-mr-px mb-8">
                    <div class="grow">
                        <section>

                            <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                                <x-forms.input-profile-avatar label="{{ __('federation.logo') }}" :old="$federation" />
                            </div>

                            <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">


                                <div class="sm:w-1/2">
                                    <label class="block text-sm font-medium mb-1"
                                           for="legal-name"> {{ __('federation.legal_name') }} <span
                                            class="text-rose-500">*</span></label>

                                    <input id="legal-name"
                                           class="form-input w-full {{ $errors->has('legal_name') ? 'border-rose-300' : '' }}"
                                           type="text"
                                           name="legal_name"
                                           value="{{ old('legal_name', $federation->legal_name ?? null) }}"
                                           required />
                                    <div class="text-xs mt-1 text-gray-500">{{ __('federation.legal_name_example') }}</div>

                                    @if($errors->has('legal_name'))
                                        <div class="text-xs mt-1 text-rose-500 h-2">
                                            {{ $errors->first('legal_name') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="sm:w-1/3">
                                    <label class="block text-sm font-medium mb-1"
                                           for="vat_number"> {{ __('federation.vat_number') }} </label>
                                    <input id="vat_number"
                                           class="form-input w-full {{ $errors->has('vat_number') ? 'border-rose-300' : '' }}"
                                           type="text" name="vat_number"
                                           value="{{ old('vat_number',$federation->vat_number ?? null) }}" />
                                    <div class="text-xs mt-1 text-gray-500">{{ __('federation.vat_number_example') }}</div>

                                    @if($errors->has('vat_number'))
                                        <div class="text-xs mt-1 text-rose-500 h-2">
                                            {{ $errors->first('vat_number') }}
                                        </div>
                                    @endif
                                </div>
                            </div>


                        </section>

                        <section class="mb-4">

                            <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 mt-5">

                                <div class="sm:w-full">
                                    <label class="block text-sm font-medium mb-1"
                                           for="address">{{ __('federation.headquarters_address') }}</label>
                                    <textarea id="address"
                                              class="form-textarea w-full {{ $errors->has('address') ? 'border-rose-300' : '' }}"
                                              name="address">{{ old('address', $federation->address ?? null) }}</textarea>
                                    <div class="text-xs mt-1 text-gray-500">{{ __('federation.address_example') }}</div>

                                    @if($errors->has('address'))
                                        <div class="text-xs mt-1 text-rose-500 h-2">
                                            {{ $errors->first('address') }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                                <div class="sm:w-2/5">
                                    <label class="block text-sm font-medium mb-1" for="location">{{ __('federation.city') }}</label>
                                    <input id="location"
                                           class="form-input w-full {{ $errors->has('location') ? 'border-rose-300' : '' }}"
                                           type="text" name="location"
                                           value="{{ old('location', $federation->location ?? null) }}" />

                                    @if($errors->has('location'))
                                        <div class="text-xs mt-1 text-rose-500 h-2">
                                            {{ $errors->first('location') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="sm:w-1/5">
                                    <label class="block text-sm font-medium mb-1"
                                           for="zip_code">{{ __('federation.postal_code') }}</label>
                                    <input id="zip_code"
                                           class="form-input w-full {{ $errors->has('zip_code') ? 'border-rose-300' : '' }}"
                                           type="text" name="zip_code"
                                           value="{{ old('zip_code', $federation->zip_code ?? null) }}" />
                                    <div class="text-xs mt-1 text-gray-500">{{ __('federation.postal_code_example') }}</div>

                                    @if($errors->has('zip_code'))
                                        <div class="text-xs mt-1 text-rose-500 h-2">
                                            {{ $errors->first('zip_code') }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5">

                                <div class="sm:w-1/5">
                                    <label class="block text-sm font-medium mb-1" for="lat">{{ __('federation.latitude') }}</label>
                                    <input id="lat"
                                           class="form-input w-full {{ $errors->has('latitude') ? 'border-rose-300' : '' }}"
                                           type="text" name="latitude"
                                           value="{{ old('latitude', $federation->lat ?? null) }}" />
                                    <div class="text-xs mt-1 text-gray-500">{{ __('federation.latitude_example') }}</div>

                                    @if($errors->has('latitude'))
                                        <div class="text-xs mt-1 text-rose-500 h-2">
                                            {{ $errors->first('latitude') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="sm:w-1/5">
                                    <label class="block text-sm font-medium mb-1" for="lng">{{ __('federation.longitude') }}</label>
                                    <input id="lng"
                                           class="form-input w-full {{ $errors->has('longitude') ? 'border-rose-300' : '' }}"
                                           type="text" name="longitude"
                                           value="{{ old('longitude', $federation->lng ?? null) }}" />
                                    <div class="text-xs mt-1 text-gray-500">{{ __('federation.longitude_example') }}</div>

                                    @if($errors->has('longitude'))
                                        <div class="text-xs mt-1 text-rose-500 h-2">
                                            {{ $errors->first('longitude') }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">

                                <div class="sm:w-1/3">
                                    <label class="block text-sm font-medium mb-1"
                                           for="email">{{ __('federation.contact_email') }}</label>
                                    <input id="email"
                                           class="form-input w-full {{ $errors->has('email') ? 'border-rose-300' : '' }}"
                                           type="email" name="email"
                                           value="{{ old('email', $federation->email ?? null) }}" />

                                    @if($errors->has('email'))
                                        <div class="text-xs mt-1 text-rose-500 h-2">
                                            {{ $errors->first('email') }}
                                        </div>
                                    @endif

                                </div>

                                <div class="sm:w-1/3">
                                    <label class="block text-sm font-medium mb-1"
                                           for="website">{{ __('federation.website') }}</label>
                                    <input id="website"
                                           class="form-input w-full {{ $errors->has('website') ? 'border-rose-300' : '' }}"
                                           type="text" name="website"
                                           value="{{ old('website', $federation->website ?? null) }}" />

                                    @if($errors->has('website'))
                                        <div class="text-xs mt-1 text-rose-500 h-2">
                                            {{ $errors->first('website') }}
                                        </div>
                                    @endif

                                </div>

                                <div class="sm:w-1/3">
                                    <label class="block text-sm font-medium mb-1"
                                           for="phone_number">{{ __('federation.phone_number') }}</label>
                                    <input id="phone_number"
                                           class="form-input w-full {{ $errors->has('phone') ? 'border-rose-300' : '' }}"
                                           type="text"
                                           name="phone"
                                           value="{{ old('phone', $federation->phone ?? null) }}" />

                                    @if($errors->has('phone'))
                                        <div class="text-xs mt-1 text-rose-500 h-2">
                                            {{ $errors->first('phone') }}
                                        </div>
                                    @endif
                                </div>

                            </div>

                        </section>

                        <!-- Panel footer -->
                        <x-forms.card-form-submit backRoute="federation.dashboard"
                                                  :buttonText="__('federation.save_record')"></x-forms.card-form-submit>

                    </div>
                </div>


                <div class="mb-8 sm:w-1/3 flex gap-y-4 flex-col">

                    <div class="card">

                        <!-- Board Members -->
                        <section>
                            <h3 class="text-xl leading-snug text-slate-800 dark:text-white font-bold mb-1">{{ __('federation.board_members') }}</h3>
                            <div class="text-xs mt-1 text-gray-500 dark:text-gray-400 mb-4">
                                {{ __('federation.board_members_description') }}
                            </div>

                            <livewire:federation.board-members-form />
                        </section>

                    </div>

                </div>

            </div>

        </form>


    </div>

</x-layout>
