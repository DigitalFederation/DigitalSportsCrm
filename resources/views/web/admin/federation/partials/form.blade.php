<div class="sm:flex sm:space-x-4">

    <div class="card sm:w-2/3 flex flex-col md:flex-row md:-mr-px mb-8">
        <div class="grow">
            <x-federation.form_create_information :federation="$federation" :federations="$federations"
                                                  :countries="$countries" :zones="$zones ?? null" />
        </div>
    </div>


    <div class="mb-8 sm:w-1/3 flex gap-y-4 flex-col">

        <div class="card">
            <section>
                <h3 class="text-xl leading-snug text-slate-800 font-bold">
                    {{ __('User login information') }} </h3>
                <p class="text-gray-500 text-sm">
                    {{ __('After choosing the user email address, an email will be sent in order for the person to register their own credentials.')}}</p>
                <div class="mt-2 w-full">
                    <label class="block text-sm font-medium mb-1" for="user_email"> {{ __('User Login email') }} <span
                            class="text-rose-500">*</span></label>
                    <input id="user_email"
                           class="form-input w-full {{ $errors->has('user_email') ? 'border-rose-300' : '' }}"
                           type="text" name="user_email" value="{{ old('user_email') }}" />
                    <p class="text-xs mt-1">{{ __('Email credential for the Federation to login')}}</p>
                    @if ($errors->has('user_email'))
                        <div class="text-xs mt-1 text-rose-500 h-2">
                            {{ $errors->first('user_email') }}
                        </div>
                    @endif
                </div>

                <div class="mt-2 w-full">
                    <label class="block text-sm font-medium mb-1"
                           for="confirm_email"> {{ __('Confirm User login email') }} <span
                            class="text-rose-500">*</span></label>
                    <input id="confirm_user_email"
                           class="form-input w-full {{ $errors->has('confirm_user_email') ? 'border-rose-300' : '' }}"
                           type="text" name="confirm_user_email" value="{{ old('confirm_user_email') }}" />
                    <p class="text-xs mt-1">{{ __('Confirm the email address')}}</p>
                    @if ($errors->has('confirm_user_email'))
                        <div class="text-xs mt-1 text-rose-500 h-2">
                            {{ $errors->first('confirm_user_email') }}
                        </div>
                    @endif
                </div>
            </section>
        </div>

    </div>
</div>
