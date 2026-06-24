<div class="bg-white shadow-lg rounded-sm flex flex-col md:flex-row md:-mr-px">
    <div class="grow">
        <div class="p-6 space-y-6">

            <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">

                <div class="sm:w-1/2">
                    <label class="block text-sm font-medium mb-1" for="name"> {{ __('Name') }} <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="name" class="form-input w-full {{ $errors->has('name') ? 'border-rose-300' : '' }}" value="{{old('name', $type->name)}}" required>

                    @if($errors->has('name'))
                        <div class="text-xs mt-1 text-rose-500 h-2">
                            {{ $errors->first('name') }}
                        </div>
                    @endif
                </div>

                <div class="sm:w-1/2">
                    <label class="block text-sm font-medium mb-1" for="ref"> {{ __('Ref') }} <span class="text-rose-500">*</span></label>
                    <input type="text" name="ref" id="ref" class="form-input w-full {{ $errors->has('ref') ? 'border-rose-300' : '' }}" value="{{old('ref', $type->ref)}}" required>

                    @if($errors->has('ref'))
                        <div class="text-xs mt-1 text-rose-500 h-2">
                            {{ $errors->first('ref') }}
                        </div>
                    @endif
                </div>




            </div>
        </div>
        <!-- Panel footer -->
        <footer>
            <div class="flex flex-col px-6 py-5 border-t border-slate-200">
                <div class="flex gap-4 self-end">
                    <a class="btn self-center bg-slate-500 text-white" href="{{ route('admin.certification-type.index') }}"> {{ __('Back') }} </a>
                    <button type="submit" class="btn btn-action">
                        {{__('Save record')}}
                    </button>
                </div>
            </div>
        </footer>
    </div>
</div>
