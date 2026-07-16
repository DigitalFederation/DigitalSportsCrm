<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title">{{ __('Create Membership Plan') }}</h1>
        </div>

        <form action="{{ route('admin.membership-plan.store') }}" method="POST">
            @csrf
            <div class="bg-white shadow-lg rounded-sm mb-8">
                <div class="flex flex-col md:flex-row md:-mr-px">
                    <div class="grow">


                        <section class="p-6 space-y-6">

                            <div class="flex information-box">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-4" width="24" height="24"
                                     viewBox="0 0 24 24" stroke-width="1.5" stroke="#9e9e9e" fill="none"
                                     stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <circle cx="12" cy="12" r="9" />
                                    <line x1="12" y1="8" x2="12.01" y2="8" />
                                    <polyline points="11 12 12 12 12 16 13 16" />
                                </svg>

                                <p class="text-sm"> Choose a type and a name to be used as a membership plan.
                                    <br> In the interval an inteval unit choose a cycle durantion. Ex: 1 Year <br>
                                    It's important to define the licenses that will be available for this plan, so the
                                    proper permissions are applied
                                </p>
                            </div>

                            <div
                                class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                                <div>
                                    <label class="block text-sm font-medium mb-1"
                                           for="committee">{{ __('Type') }}</label>
                                    <select name="committee_id" id="committee"
                                            class="form-select w-full {{ $errors->has('committee_id') ? 'border-rose-300' : '' }}"
                                    >
                                        <option selected value=""> {{ config("branding.international.short_name", "IF") }}</option>
                                        @foreach($committees as $committee)
                                            <option value="{{ $committee->id }}"
                                                    @if(old('committee_id')==$committee->id) selected @endif>
                                                {{ $committee->name }}</option>
                                        @endforeach
                                    </select>

                                    @if($errors->has('committee_id'))
                                        <div class="text-xs mt-1 text-rose-500 h-2">
                                            {{ $errors->first('committee_id') }}
                                        </div>
                                    @endif


                                </div>
                                <div class="w-full">
                                    <label class="block text-sm font-medium mb-1" for="name">{{ __('Plan Name') }} <span
                                            class="text-rose-500">*</span></label>
                                    <input id="name"
                                           class="form-input w-full {{ $errors->has('name') ? 'border-rose-300' : '' }}"
                                           type="text" name="name" value="{{ old('name') }}" required />
                                    @if($errors->has('name'))
                                        <div class="text-xs mt-1 text-rose-500 h-2">
                                            {{ $errors->first('name') }}
                                        </div>
                                    @endif
                                </div>

                            </div>

                            <div
                                class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 mt-5 border-b border-gray-300">
                                <div>
                                    <label class="block text-sm font-medium mb-1"
                                           for="friendly_name">{{ __('Public Registration Name') }}</label>
                                    <input id="friendly_name"
                                           class="form-input w-full {{ $errors->has('friendly_name') ? 'border-rose-300' : '' }}"
                                           type="text" name="friendly_name" value="{{ old('friendly_name') }}"
                                    />
                                    @if($errors->has('friendly_name'))
                                        <div class="text-xs mt-1 text-rose-500 h-2">
                                            {{ $errors->first('friendly_name') }}
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="price">{{ __('Price') }}
                                        ({{ currency_symbol() }})</label>
                                    <input id="price"
                                           pattern="^\\$?(([1-9](\\d*|\\d{0,2}(,\\d{3})*))|0)(\\.\\d{1,2})?$"
                                           class="form-input {{ $errors->has('price') ? 'border-rose-300' : '' }}"
                                           type="text"
                                           min="0"
                                           name="price"
                                           value="{{ old('price') }}" />
                                    <div class="text-xs mt-1"> {{ __('* Price in :currency. Ex: 12,90', ['currency' => currency_code()]) }} </div>

                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        @if($errors->has('price'))
                                            {{ $errors->first('price') }}
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div
                                class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 mt-5 border-b border-gray-300">

                                <div>
                                    <label class="block text-sm font-medium mb-1" for="int">{{ __('Interval') }} <span
                                            class="text-rose-500">*</span></label>
                                    <input id="interval"
                                           class="form-input w-full {{ $errors->has('interval') ? 'border-rose-300' : '' }}"
                                           type="number" min="1" name="interval" value="{{ old('interval') }}" />
                                    <div class="text-xs mt-1"> {{ __('*Numbers only') }} </div>

                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        @if($errors->has('interval'))
                                            {{ $errors->first('interval') }}
                                        @endif
                                    </div>

                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1"
                                           for="interval_unit">{{ __('Interval Unit') }} <span
                                            class="text-rose-500">*</span></label>

                                    <select name="interval_unit" id="interval_unit"
                                            class="form-select w-full {{ $errors->has('interval_unit') ? 'border-rose-300' : '' }}"
                                            required>
                                        <option value="" selected disabled> {{ __('-- Select an option --') }} </option>
                                        @foreach($interval_unit as $key=>$type)
                                            <option value="{{ $key }}"
                                                    @if(old('interval_unit') == $key) selected @endif>{{ $type }}</option>
                                        @endforeach
                                    </select>

                                    @if($errors->has('interval_unit'))
                                        <div class="text-xs mt-1 text-rose-500 h-2">
                                            {{ $errors->first('interval_unit') }}
                                        </div>
                                    @endif

                                </div>

                            </div>


                            <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 mt-5 ">

                                <div>
                                    <label class="block text-sm font-medium mb-1"
                                           for="licenses">{{ __('Licenses') }} </label>

                                    <livewire:input.select-multiple
                                        wire:model="licenses"
                                        :items="$licenses"
                                        inputId="licenses"
                                        inputName="licenses[]" />

                                    <div
                                        class="text-xs mt-1"> {{ __('* Choose one or more licenses to be appendend to this Plan') }} </div>

                                    @if($errors->has('licenses'))
                                        <div class="text-xs mt-1 text-rose-500 h-2">
                                            {{ $errors->first('licenses') }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                        </section>


                        <section>
                            <div class="flex flex-col px-6 py-5 border-t border-slate-200">
                                <div class="flex self-end">
                                    <a class="btn self-center bg-slate-500 text-white"
                                       href="{{ route(Request::segment(1).'.membership-plan.index') }}">{{ __('Back') }}</a>
                                    <button type="submit"
                                            class="btn bg-blue-500 hover:bg-blue-600 text-white ml-3 px-3 py-2 rounded">{{ __('Create Record') }}
                                    </button>
                                </div>
                            </div>
                        </section>

                    </div>
                </div>
            </div>

        </form>

    </div>
</x-layout>
