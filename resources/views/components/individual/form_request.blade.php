<div class="sm:flex sm:space-x-4">
    <div class="mb-8">
        <div class="bg-white shadow-lg rounded-md flex flex-col md:flex-row md:-mr-px">
            <div class="grow">

                <!-- Panel body -->
                <div class="p-6 space-y-6">

                    <!-- Assignment -->
                    <section>
                        <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                            <div>
                                <label class="block text-sm font-medium mb-1" for="member_code">{{ __('main.Member Code') }} <span class="text-rose-500">*</span></label>
                                <input id="member_code" class="form-input w-full {{ $errors->has('member_code') ? 'border-rose-300' : '' }}" type="text" name="member_code" value="{{ old('member_code') }}" required/>

                                @if($errors->has('member_code'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('member_code') }}
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
                            <button type="submit" class="btn btn-action">
                                {{__('Send Request')}}
                            </button>
                        </div>
                    </div>
                </footer>

            </div>
        </div>
    </div>
</div>
