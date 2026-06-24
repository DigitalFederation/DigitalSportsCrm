<div class="sm:flex sm:space-x-4">
    <div class="mb-8 sm:w-full">
        <div class="bg-white shadow-lg rounded-sm flex flex-col md:flex-row md:-mr-px">
            <div class="grow">

                <!-- Panel body -->
                <div class="p-6 space-y-6">

                    <section>

                        <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">

                            <div class="sm:w-1/3">
                                <label class="block text-sm font-medium mb-1"
                                       for="license_id"> {{ __('License') }} <span
                                        class="text-rose-500">*</span></label>
                                <select name="license_id" id="license_id"
                                        class="form-input w-full {{ $errors->has('license_id') ? 'border-rose-300' : '' }}"
                                        required>
                                    <option value="" selected disabled> {{ __('-- Select an option --') }} </option>
                                    @foreach($licenses as $license)
                                        <option value="{{ $license->id }}"
                                                @if(old('license_id') == $license->id) selected @endif>{{ $license->name }}</option>
                                    @endforeach
                                </select>

                                @if($errors->has('license_id'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('license_id') }}
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1" for="entity_id"> {{ __('Entity') }}
                                    <span class="text-rose-500">*</span></label>
                                    <select name="entity_id" id="entity_id" class="form-input w-full {{ $errors->has('entity_id') ? 'border-rose-300' : '' }}">
                                    <option value="" selected disabled> {{ __('-- Select an option --') }} </option>
                                    @foreach($entities as $entity)
                                        <option value="{{ $entity->id }}" @if(old('entity_id') == $entity->id) selected @endif>{{ $entity->name }}</option>
                                    @endforeach
                                </select>

                                @if($errors->has('entity_id'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('entity_id') }}
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1" for="individual_id"> {{ __('Individual') }}
                                    <span class="text-rose-500">*</span></label>
                                    <select name="individual_id" id="individual_id" class="form-input w-full {{ $errors->has('individual_id') ? 'border-rose-300' : '' }}">
                                    <option value="" selected disabled> {{ __('-- Select an option --') }} </option>
                                    @foreach($entities as $entity)
                                        <option value="{{ $entity->id }}" @if(old('individual_id') == $entity->id) selected @endif>{{ $entity->name }}</option>
                                    @endforeach
                                </select>

                                @if($errors->has('individual_id'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('individual_id') }}
                                    </div>
                                @endif
                            </div>

                        </div>

                        <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 mt-5">

                            <div>
                                <label class="block text-sm font-medium mb-1"
                                       for="current_term_starts_at"> {{ __('Issue Date') }}</label>
                                <input type="date" name="current_term_starts_at" id="current_term_starts_at"
                                       class="form-input w-full {{ $errors->has('current_term_starts_at') ? 'border-rose-300' : '' }}"
                                       value="{{ old('current_term_starts_at') }}">

                                @if($errors->has('current_term_starts_at'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('current_term_starts_at') }}
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1"
                                       for="current_term_ends_at"> {{ __('Expiration Date') }}</label>
                                <input type="date" name="current_term_ends_at" id="current_term_ends_at"
                                       class="form-input w-full {{ $errors->has('current_term_ends_at') ? 'border-rose-300' : '' }}"
                                       value="{{ old('current_term_ends_at') }}" min="{{ Date('Y-m-d') }}">

                                @if($errors->has('current_term_ends_at'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('current_term_ends_at') }}
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1"
                                       for="cmas_license_code"> {{ __('main.Member Code') }}</label>
                                <input type="text" name="cmas_license_code" id="cmas_license_code"
                                       class="form-input w-full {{ $errors->has('cmas_license_code') ? 'border-rose-300' : '' }}"
                                       value="{{ old('cmas_license_code') }}" min="{{ Date('Y-m-d') }}">
                                <div class="text-xs mt-1"> {{ __('Internal international code') }} </div>
                                @if($errors->has('cmas_license_code'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('cmas_license_code') }}
                                    </div>
                                @endif
                            </div>

                        </div>

                        <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                            <div class="sm:w-1/3">
                                <label class="block text-sm font-medium mb-1" for="notes"> {{ __('Notes') }}</label>
                                <textarea name="notes" id="notes" rows="4" class="form-textarea w-full focus:border-slate-300 {{ $errors->has('notes') ? 'border-rose-300' : '' }}">{{ old('notes') }}</textarea>

                                @if($errors->has('date_expire'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('date_expire') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                    </section>

                </div>

                <!-- Panel footer -->
                <footer>
                    <div class="flex flex-col px-6 py-5 border-t border-slate-200">
                        <div class="flex self-end">
                            <a class="btn self-center bg-slate-500 text-white"
                               href="{{ route(Request::segment(1).'.certification-attributed.index') }}"> {{ __('Back') }} </a>
                            <button type="submit" class="btn btn-action">
                                {{__('Save record')}}
                            </button>
                        </div>
                    </div>
                </footer>

            </div>
        </div>
    </div>
</div>
