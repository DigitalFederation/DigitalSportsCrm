<div class="grow">

    <div class="space-y-6 mb-8">

        <div class="flex information-box items-center w-full">
            <x-svg.info class="h-6 w-6 mr-4"/>
            <p class="text-sm">
                {{ __("Define conditional rules for attributes to determine their behavior based on specified criteria.") }}
            </p>
        </div>

        <!-- Assignment -->
        <section>

            <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">{{ __('Rule Settings') }}</h3>

            <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5">

                <div class="sm:w-1/4">
                    <label class="block text-sm font-medium mb-1" for="name"> {{ __('Rule Name') }} <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="name" class="form-input w-full {{ $errors->has('name') ? 'border-rose-300' : '' }}" value="{{old('name',$attributeRule->name)}}" required>

                    @if($errors->has('name'))
                        <div class="text-xs mt-1 text-rose-500 h-2">
                            {{ $errors->first('name') }}
                        </div>
                    @endif
                </div>


                <div class="sm:w-1/4">
                    <label class="block text-sm font-medium mb-1" for="operator">{{ __('Operator') }} <span class="text-rose-500">*</span></label>
                    <select name="operator" id="operator" class="form-input w-full {{ $errors->has('operator') ? 'border-rose-300' : '' }}" required>
                        <option value="" selected disabled> {{ __('-- Select an option --') }} </option>
                        @foreach($attribute_rules_operator as $operator)
                            <option
                                value="{{ $operator->name }}"
                                @if(old('operator',$attributeRule->operator) == $operator->name) selected @endif
                            >{{ $operator->value }}</option>
                        @endforeach
                    </select>

                    @if($errors->has('operator'))
                        <div class="text-xs mt-1 text-rose-500 h-2">
                            {{ $errors->first('operator') }}
                        </div>
                    @endif
                </div>


                <div class="sm:w-1/4">
                    <label class="block text-sm font-medium mb-1" for="default_value"> {{ __('Default value') }} <span class="text-rose-500">*</span></label>
                    <input type="text" name="default_value" id="default_value" class="form-input w-full {{ $errors->has('default_value') ? 'border-rose-300' : '' }}" value="{{old('default_value',$attributeRule->default_value)}}" required>

                    @if($errors->has('default_value'))
                        <div class="text-xs mt-1 text-rose-500 h-2">
                            {{ $errors->first('default_value') }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5">

                <div class="sm:w-1/4">
                    <label class="block text-sm font-medium mb-1" for="comparison_field"> {{ __('Comparison Field') }} </label>
                    <input type="text" name="comparison_field" id="comparison_field" class="form-input w-full {{ $errors->has('comparison_field') ? 'border-rose-300' : '' }}" value="{{old('comparison_field',$attributeRule->comparison_field)}}">

                    @if($errors->has('comparison_field'))
                        <div class="text-xs mt-1 text-rose-500 h-2">
                            {{ $errors->first('comparison_field') }}
                        </div>
                    @endif
                </div>

                <div class="sm:w-1/4">
                    <label class="block text-sm font-medium mb-1" for="comparison_value">{{ __('Comparison Value') }}</label>
                    <input type="text" name="comparison_value" id="comparison_value" class="form-input w-full {{ $errors->has('comparison_value') ? 'border-rose-300' : '' }}" value="{{ old('comparison_value', $attributeRule->comparison_value) }}">

                    @if($errors->has('comparison_value'))
                        <div class="text-xs mt-1 text-rose-500 h-2">
                            {{ $errors->first('comparison_value') }}
                        </div>
                    @endif
                </div>
            </div>


            <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                <div class="sm:w-1/2">
                    <label class="block text-sm font-medium mb-1" for="fillable_global">{{ __('Validation Check') }}</label>
                    <div class="mt-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" id="is_validation" name="is_validation" value="1" {{ old('is_validation', $attributeRule->is_validation) == 1 ? 'checked' : '' }} class="{{ $errors->has('is_validation') ? 'border-rose-300' : '' }}">
                            <span class="ml-2">{{ __('Attribute must validate') }}</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="hidden" name="is_validation" value="0">
                        </label>
                        <p class="text-xs text-gray-400 mt-1">{{ __('Check the box if the attribute needs to validate in the enrollment process.') }}</p>
                    </div>
                    @if($errors->has('is_validation'))
                        <div class="text-xs mt-1 text-rose-500 h-2">
                            {{ $errors->first('is_validation') }}
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    <x-forms.card-form-submit :justBack="true" :buttonText="__('Save record')"/>
</div>
