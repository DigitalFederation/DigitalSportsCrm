<div class="sm:flex sm:space-x-4">
    <div class="mb-8 sm:w-full">
        <div class="bg-white shadow-lg rounded-sm flex flex-col md:flex-row md:-mr-px">
            <div class="grow">

                <!-- Panel body -->
                <div class="p-6 space-y-6">

                    <!-- Assignment -->
                    <section>
                        <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">{{ __('Basic Information') }}</h3>

                        <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                            <div class="sm:w-1/3">
                                <label class="block text-sm font-medium mb-1" for="parent_id"> {{ __('Certification Parent') }}</label>
                                <select name="parent_id" id="parent_id" class="form-input w-full {{ $errors->has('parent_id') ? 'border-rose-300' : '' }}">
                                    <option value="" selected disabled> {{ __('-- Select an option --') }} </option>
                                    @foreach($parents as $parent)
                                        <option value="{{ $parent->id }}" @if(old('parent_id', $certification->parent_id) == $parent->id) selected @endif>{{ $parent->name }}</option>
                                    @endforeach
                                </select>

                                @if($errors->has('parent_id'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('parent_id') }}
                                    </div>
                                @endif
                            </div>

                            <div class="sm:w-1/3">
                                <label class="block text-sm font-medium mb-1" for="name"> {{ __('Name') }} <span class="text-rose-500">*</span></label>
                                <input type="text" name="name" id="name" class="form-input w-full {{ $errors->has('name') ? 'border-rose-300' : '' }}" value="{{old('name', $certification->name)}}" required>

                                @if($errors->has('name'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('name') }}
                                    </div>
                                @endif
                            </div>

                            <div class="sm:w-1/3">
                                <label class="block text-sm font-medium mb-1" for="committee_id">{{ __('Committee') }} <span class="text-rose-500">*</span></label>
                                <select name="committee_id" id="committee_id" class="form-input w-full {{ $errors->has('committee_id') ? 'border-rose-300' : '' }}" required>
                                    <option value="" selected disabled> {{ __('-- Select an option --') }} </option>
                                    @foreach($committees as $committee)
                                        <option value="{{ $committee->id }}" @if(old('committee_id', $certification->committee_id) == $committee->id) selected @endif>{{ $committee->name }}</option>
                                    @endforeach
                                </select>

                                @if($errors->has('committee_id'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('committee_id') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                            <div class="sm:w-1/3">
                                <label class="block text-sm font-medium mb-1" for="certification_type"> {{ __('Type') }} <span class="text-rose-500">*</span></label>

                                <select name="type_id" id="certification_type" class="form-input w-full {{ $errors->has('type_id') ? 'border-rose-300' : '' }}" required>
                                    <option value="" selected disabled> {{ __('-- Select an option --') }} </option>
                                    @foreach($certificationTypes as $type)
                                        <option value="{{ $type->id }}" @if(old('type_id', $certification->type_id) == $type->id) selected @endif>{{ $type->name }}</option>
                                    @endforeach
                                </select>

                                @if($errors->has('type_id'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('type_id') }}
                                    </div>
                                @endif
                            </div>

                            <div class="sm:w-1/3">
                                <label class="block text-sm font-medium mb-1" for="price"> {{ __('Price') }} ({{ currency_symbol() }})</label>
                                <input type="text" name="price" id="price" class="form-input w-full {{ $errors->has('price') ? 'border-rose-300' : '' }}" pattern="^\\$?(([1-9](\\d*|\\d{0,2}(,\\d{3})*))|0)(\\.\\d{1,2})?$" value="{{ old('price', $certification->price) }}">

                                @if($errors->has('price'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('price') }}
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
                            <a class="btn self-center bg-slate-500 text-white" href="{{ route(Request::segment(1).'.certification.index') }}"> {{ __('Cancel') }} </a>
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
