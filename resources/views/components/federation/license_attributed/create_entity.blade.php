<div>
    <form action="{{ route('federation.license-attributed.store') }}" method="POST">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">
        <input type="hidden" name="for" value="{{ $for }}">

        <div class="sm:flex sm:space-x-4">

            <div class="mb-8 sm:w-full">
                <div class="bg-white shadow-lg rounded-sm flex flex-col md:flex-row md:-mr-px">

                    <div class="grow">

                        <!-- Panel body -->
                        <section class="p-6 space-y-6">

                            <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">{{ ucwords($type) }}</h3>

                            <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                                <div class="sm:w-1/3">
                                    <label for="license_id"
                                           class="block text-sm font-medium mb-1">{{ __('License') }} <span
                                            class="text-rose-500">*</span></label>
                                    <select name="license_id" id="license_id" class="form-input w-full" required>
                                        <option hidden selected>{{ __('Choose a license') }}</option>
                                        @foreach($licenses as $license)
                                            <option value="{{ $license->id }}">{{ $license->name }}</option>
                                        @endforeach
                                    </select>

                                    @if($errors->has('license_id'))
                                        <div class="text-xs mt-1 text-rose-500 h-2">
                                            {{ $errors->first('license_id') }}
                                        </div>
                                    @endif
                                    <div
                                        class="text-xs mt-1">{{ __('Choose the license to be attributed on this request') }}</div>
                                </div>
                            </div>
                            <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                                <div class="sm:w-1/3">
                                    <label for="entity_id"
                                           class="block text-sm font-medium mb-1">{{ __('Entity') }} <span
                                            class="text-rose-500">*</span></label>
                                    <select name="entity_id" id="license_id" class="form-input w-full" required>
                                        <option hidden selected>{{ __('Choose a entity') }}</option>
                                        @foreach($entities as $entity)
                                            <option value="{{ $entity->id }}">{{ $entity->name }}</option>
                                        @endforeach
                                    </select>

                                    @if($errors->has('entity_id'))
                                        <div class="text-xs mt-1 text-rose-500 h-2">
                                            {{ $errors->first('entity_id') }}
                                        </div>
                                    @endif
                                    <div
                                        class="text-xs mt-1">{{ __('Choose the entity to be attributed on this request') }}</div>
                                </div>
                            </div>

                            <div
                                class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5 pt-4 border-stone-300 border-t-2">

                                <div>
                                    <label class="block text-sm font-medium mb-1"
                                           for="current_term_starts_at"> {{ __('Start date') }}</label>
                                    <input type="date" name="current_term_starts_at" id="current_term_starts_at"
                                           class="form-input w-full" value="{{ old('current_term_starts_at') }}">
                                    <div class="text-xs mt-1">When does the license start</div>

                                    @if($errors->has('current_term_starts_at'))
                                        <div class="text-xs mt-1 text-rose-500 h-2">
                                            {{ $errors->first('current_term_starts_at') }}
                                        </div>
                                    @endif
                                </div>

                            </div>

                            <div
                                class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5 pt-4 border-stone-300 border-t-2">

                                <div class="sm:w-1/3">
                                    <label class="block text-sm font-medium mb-1"
                                           for="license_id"> {{ __('Notes') }}</label>
                                    <textarea class="form-textarea w-full" rows="2" name="notes"></textarea>
                                    <div class="text-xs mt-1">Add some notes to the current request if needed</div>
                                </div>

                            </div>

                        </section>


                        <!-- Panel footer -->
                        <footer>
                            <div class="flex flex-col px-6 py-5 border-t border-slate-200">
                                <div class="flex self-end">
                                    <a class="btn self-center bg-slate-500 text-white"
                                       href="{{ route('federation.license-attributed.index') }}"> {{ __('Back') }} </a>
                                    <button type="submit" class="btn btn-action">
                                        {{__('Save request')}}
                                    </button>
                                </div>
                            </div>
                        </footer>

                    </div>
                </div>
            </div>

        </div>

    </form>
</div>
